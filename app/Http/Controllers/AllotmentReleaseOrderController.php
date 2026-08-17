<?php

namespace App\Http\Controllers;

use App\Exports\AllotmentReleaseOrderExport;
use App\Models\AllotmentReleaseOrder;
use App\Models\AllotmentReleaseOrderItem;
use App\Models\Appropriation;
use App\Models\Employee;
use App\Models\Office;
use App\Models\OfficeAllotmentClass;
use App\Models\Supplemental;
use App\Traits\LogsActivity;
use App\Traits\SortsAppropriations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AllotmentReleaseOrderController extends Controller
{
    use LogsActivity, SortsAppropriations;

    /**
     * Office (id 12) that Provincial Budget Officer signatories are sourced from,
     * matching the existing convention used by RAOController.
     */
    private const PBO_OFFICE_ID = '12';

    public function index(Request $request)
    {
        $currentYear = date('Y');
        $selectedYear = $request->input('year1', $currentYear);
        $search = $request->input('search');

        $query = AllotmentReleaseOrder::with([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'items',
        ])->where('year', $selectedYear);

        if ($request->filled('office_filter')) {
            $query->whereHas('officeAllotmentClass', function ($q) use ($request) {
                $q->where('office', $request->office_filter);
            });
        }

        if ($request->filled('allotment_class_filter')) {
            $query->whereHas('officeAllotmentClass', function ($q) use ($request) {
                $q->where('class', $request->allotment_class_filter);
            });
        }

        if ($request->filled('fund_source_filter')) {
            $query->where('fund_source', $request->fund_source_filter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('aro_no', 'like', "%{$search}%")
                    ->orWhere('ppa_code', 'like', "%{$search}%")
                    ->orWhere('supplemental_no', 'like', "%{$search}%")
                    ->orWhereHas('officeAllotmentClass.offices', function ($officeQuery) use ($search) {
                        $officeQuery->where('office_abbreviation', 'like', "%{$search}%")
                            ->orWhere('office_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('account_code', 'like', "%{$search}%")
                            ->orWhere('ppa_description', 'like', "%{$search}%");
                    });
            });
        }

        $allotmentReleaseOrders = $query->orderByDesc('id')->get();

        $availableYears = OfficeAllotmentClass::query()
            ->distinct()
            ->pluck('year')
            ->push($currentYear)
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        $offices = Office::orderBy('id')->get();
        $allotmentClasses = \App\Models\AllotmentClass::orderBy('id')->get();

        $activeFilterChips = [];
        if ($request->filled('office_filter')) {
            $office = $offices->firstWhere('id', $request->office_filter);
            $activeFilterChips[] = [
                'label' => 'Office',
                'value' => $office->office_abbreviation ?? $request->office_filter,
                'param' => 'office_filter',
            ];
        }
        if ($request->filled('allotment_class_filter')) {
            $activeFilterChips[] = [
                'label' => 'Allotment Class',
                'value' => AllotmentReleaseOrder::displayClassLabel($request->allotment_class_filter),
                'param' => 'allotment_class_filter',
            ];
        }
        if ($request->filled('fund_source_filter')) {
            $activeFilterChips[] = [
                'label' => 'Fund Source',
                'value' => $request->fund_source_filter,
                'param' => 'fund_source_filter',
            ];
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Allotment Release Order'],
        ];

        $officeAllotmentClassesForForm = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->orderBy('office')
            ->get();

        return view('allotment_release_orders.index', compact(
            'allotmentReleaseOrders',
            'availableYears',
            'selectedYear',
            'offices',
            'allotmentClasses',
            'activeFilterChips',
            'search',
            'breadcrumb',
            'officeAllotmentClassesForForm'
        ))->with('status', session('status'));
    }

    public function create()
    {
        return redirect()->route('allotment_release_orders.index');
    }

    public function show(AllotmentReleaseOrder $allotmentReleaseOrder)
    {
        return redirect()->route('allotment_release_orders.preview', $allotmentReleaseOrder);
    }

    public function edit(AllotmentReleaseOrder $allotmentReleaseOrder)
    {
        return redirect()->route('allotment_release_orders.index');
    }

    public function store(Request $request)
    {
        $validated = $this->validateAro($request);

        try {
            $allotmentReleaseOrder = DB::transaction(function () use ($validated) {
                $dateOfIssue = Carbon::parse($validated['date_of_issue']);

                $aro = AllotmentReleaseOrder::create([
                    'aro_no' => $this->generateAroNo($validated['office_allotment_classes_id'], $dateOfIssue),
                    'date_of_issue' => $dateOfIssue,
                    'year' => $dateOfIssue->year,
                    'office_allotment_classes_id' => $validated['office_allotment_classes_id'],
                    'fund_source' => $validated['fund_source'],
                    'supplemental_no' => $validated['supplemental_no'] ?? null,
                    'ppa_code' => $validated['ppa_code'] ?? null,
                    'provincial_budget_officer_id' => $validated['provincial_budget_officer_id'],
                    'provincial_budget_officer_title' => $validated['provincial_budget_officer_title'],
                    'provincial_governor_id' => $validated['provincial_governor_title'] === 'Provincial Governor'
                        ? $validated['provincial_governor_id']
                        : null,
                    'provincial_governor_name' => $validated['provincial_governor_title'] === 'Acting Provincial Governor'
                        ? $validated['provincial_governor_name']
                        : null,
                    'provincial_governor_title' => $validated['provincial_governor_title'],
                    'created_by' => auth()->id(),
                ]);

                $this->saveItems($aro, $validated);

                return $aro;
            });

            return redirect()->route('allotment_release_orders.preview', $allotmentReleaseOrder)
                ->with('status', [
                    'type' => 'create',
                    'message' => 'Allotment Release Order: <strong>'.$allotmentReleaseOrder->aro_no.'</strong> has been created successfully.',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('allotment_release_orders.index', $request->only(['year1']))
                ->with('error', 'Failed to create Allotment Release Order: '.$e->getMessage());
        }
    }

    public function update(Request $request, AllotmentReleaseOrder $allotmentReleaseOrder)
    {
        $validated = $this->validateAro($request);

        try {
            DB::transaction(function () use ($validated, $allotmentReleaseOrder) {
                $dateOfIssue = Carbon::parse($validated['date_of_issue']);

                $allotmentReleaseOrder->update([
                    'date_of_issue' => $dateOfIssue,
                    'year' => $dateOfIssue->year,
                    'office_allotment_classes_id' => $validated['office_allotment_classes_id'],
                    'fund_source' => $validated['fund_source'],
                    'supplemental_no' => $validated['supplemental_no'] ?? null,
                    'ppa_code' => $validated['ppa_code'] ?? null,
                    'provincial_budget_officer_id' => $validated['provincial_budget_officer_id'],
                    'provincial_budget_officer_title' => $validated['provincial_budget_officer_title'],
                    'provincial_governor_id' => $validated['provincial_governor_title'] === 'Provincial Governor'
                        ? $validated['provincial_governor_id']
                        : null,
                    'provincial_governor_name' => $validated['provincial_governor_title'] === 'Acting Provincial Governor'
                        ? $validated['provincial_governor_name']
                        : null,
                    'provincial_governor_title' => $validated['provincial_governor_title'],
                ]);

                $allotmentReleaseOrder->items()->delete();
                $this->saveItems($allotmentReleaseOrder, $validated);
            });

            return redirect()->route('allotment_release_orders.preview', $allotmentReleaseOrder)
                ->with('status', [
                    'type' => 'update',
                    'message' => 'Allotment Release Order: <strong>'.$allotmentReleaseOrder->aro_no.'</strong> has been updated successfully.',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('allotment_release_orders.index', $request->only(['year1']))
                ->with('error', 'Failed to update Allotment Release Order: '.$e->getMessage());
        }
    }

    public function destroy(Request $request, AllotmentReleaseOrder $allotmentReleaseOrder)
    {
        $redirectParams = $request->only(['year1']);

        $newerAro = AllotmentReleaseOrder::where('office_allotment_classes_id', $allotmentReleaseOrder->office_allotment_classes_id)
            ->where('id', '!=', $allotmentReleaseOrder->id)
            ->where('date_of_issue', '>', $allotmentReleaseOrder->date_of_issue)
            ->orderBy('date_of_issue')
            ->first();

        if ($newerAro) {
            return redirect()->route('allotment_release_orders.index', $redirectParams)
                ->with('error',
                    "Cannot delete ARO No. <strong>{$allotmentReleaseOrder->aro_no}</strong>. ".
                    "A later ARO (No. <strong>{$newerAro->aro_no}</strong>) for this office/allotment class already accounts for released amounts on top of this one. ".
                    'Delete that ARO first.'
                );
        }

        try {
            $aroNo = $allotmentReleaseOrder->aro_no;
            $allotmentReleaseOrder->delete();

            return redirect()->route('allotment_release_orders.index', $redirectParams)
                ->with('status', [
                    'type' => 'delete',
                    'message' => 'Allotment Release Order: <strong>'.$aroNo.'</strong> has been deleted successfully.',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('allotment_release_orders.index', $redirectParams)
                ->with('error', 'Failed to delete Allotment Release Order: '.$e->getMessage());
        }
    }

    public function preview(AllotmentReleaseOrder $allotmentReleaseOrder)
    {
        $allotmentReleaseOrder->load([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'officeAllotmentClass.fund',
            'items',
            'provincialBudgetOfficer',
            'provincialGovernor',
        ]);

        return view('allotment_release_orders.preview', [
            'aro' => $allotmentReleaseOrder,
        ]);
    }

    public function exportExcel(Request $request, AllotmentReleaseOrder $allotmentReleaseOrder)
    {
        $allotmentReleaseOrder->load([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'officeAllotmentClass.fund',
            'items',
            'provincialBudgetOfficer',
            'provincialGovernor',
        ]);

        $paperSize = $request->query('paper_size') === 'legal' ? 'legal' : 'long';

        $fileName = 'ARO_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $allotmentReleaseOrder->aro_no).'.xlsx';

        $allotmentClass = optional($allotmentReleaseOrder->officeAllotmentClass)->allotmentClass;

        self::logExcelReportGeneration('Allotment Release Order', $fileName, [
            'ARO No.' => $allotmentReleaseOrder->aro_no,
            'Office' => optional($allotmentReleaseOrder->officeAllotmentClass->offices)->office_abbreviation,
            'Allotment Class' => AllotmentReleaseOrder::displayClassLabel(optional($allotmentClass)->class, optional($allotmentClass)->description),
        ]);

        return Excel::download(new AllotmentReleaseOrderExport($allotmentReleaseOrder, $paperSize), $fileName);
    }

    /**
     * AJAX: appropriations (Annual/Reenacted) or supplemental line items
     * (Supplemental Budget) for the selected office/allotment class. For
     * Supplemental Budget, rows come from that class's related supplemental
     * records — narrowed to a specific batch once SB No. is typed, but shown
     * (one row per account, most recent batch) even before then.
     *
     * Each row also carries `already_committed` / `balance` so the same
     * appropriation can't be silently double-released across AROs: balance =
     * that row's authorized amount minus whatever other existing AROs have
     * already committed against it. Pass `aro_id` when editing so that ARO's
     * own (still-persisted) items aren't counted against itself.
     */
    public function getAppropriationsByClass(Request $request)
    {
        $request->validate([
            'office_allotment_classes_id' => 'required|exists:office_allotment_classes,id',
            'fund_source' => 'required|in:Annual Budget,Supplemental Budget,Reenacted Budget',
            'supplemental_no' => 'nullable|string',
            'aro_id' => 'nullable|exists:allotment_release_orders,id',
        ]);

        $officeAllotmentClassId = $request->office_allotment_classes_id;
        $excludeAroId = $request->integer('aro_id') ?: null;
        $fundSource = $request->fund_source;

        $office = OfficeAllotmentClass::with(['offices', 'allotmentClass'])->find($officeAllotmentClassId);
        $ppaCode = optional($office->offices)->ppa_code;

        // A Special Education Fund office consolidates every SEF office sharing the
        // same Allotment Class/year into one ARO, the same way the SAAOB report
        // does — each office's rows are tagged with office_label so the Preview/
        // Export can group them under their own header (see groupedItems()).
        $isSefConsolidated = optional($office->offices)->fund === 'Special Education Fund';
        $targetOffices = $isSefConsolidated
            ? $this->sefOfficeAllotmentClasses($office)
            : collect([$office]);

        if ($fundSource === 'Supplemental Budget') {
            $items = collect();

            foreach ($targetOffices as $targetOffice) {
                $supplementalsQuery = Supplemental::with('appropriation')
                    ->where('office_allotment_classes_id', $targetOffice->id)
                    ->where('type', 'Supplemental');

                if ($request->filled('supplemental_no')) {
                    // "Supplemental No." on the ARO form matches the Supplementals
                    // module's "SB No." field (basis_no — the Sanggunian/Board
                    // Resolution reference number), not the auto-generated
                    // supplemental_no tracking code.
                    $supplementalsQuery->where('basis_no', $request->supplemental_no);
                }

                // Before a specific SB No. is typed, list every account code that has
                // *any* supplemental for this office/class (one row per account, most
                // recent batch), so the table isn't empty while the user is still
                // filling in the number they don't already have memorized.
                $supplementals = $supplementalsQuery->orderByDesc('supplemental_date')->get()
                    ->filter(fn ($s) => $s->appropriation)
                    ->unique('appropriations_id')
                    ->values();

                $officeLabel = $isSefConsolidated ? optional($targetOffice->offices)->office_name : null;

                $items = $items->merge($supplementals->map(function ($supplemental) use ($excludeAroId, $officeLabel) {
                    $authorizedAppropriation = (float) $supplemental->amount;
                    $alreadyCommitted = $this->alreadyCommittedForAppropriation($supplemental->appropriation->id, 'Supplemental Budget', $excludeAroId);

                    return [
                        'appropriation_id' => $supplemental->appropriation->id,
                        'account_code' => $supplemental->appropriation->account_code,
                        'description' => $supplemental->appropriation->description,
                        'programs' => $supplemental->appropriation->programs,
                        'office_label' => $officeLabel,
                        'authorized_appropriation' => $authorizedAppropriation,
                        'quarter1' => (float) $supplemental->quarter1,
                        'quarter2' => (float) $supplemental->quarter2,
                        'quarter3' => (float) $supplemental->quarter3,
                        'quarter4' => (float) $supplemental->quarter4,
                        'already_committed' => $alreadyCommitted,
                        'balance' => max(0, $authorizedAppropriation - $alreadyCommitted),
                    ];
                }));
            }

            return response()->json([
                'items' => $items->values(),
                'ppa_code' => $ppaCode,
                'is_sef_consolidated' => $isSefConsolidated,
            ]);
        }

        $items = collect();

        foreach ($targetOffices as $targetOffice) {
            $appropriations = $this->sortAppropriations(
                Appropriation::where('office_allotment_class_id', $targetOffice->id)->get()
            );

            $officeLabel = $isSefConsolidated ? optional($targetOffice->offices)->office_name : null;

            $items = $items->merge($appropriations->map(function ($appropriation) use ($excludeAroId, $fundSource, $officeLabel) {
                $authorizedAppropriation = (float) $appropriation->appropriation;
                $alreadyCommitted = $this->alreadyCommittedForAppropriation($appropriation->id, $fundSource, $excludeAroId);

                return [
                    'appropriation_id' => $appropriation->id,
                    'account_code' => $appropriation->account_code,
                    'description' => $appropriation->description,
                    'programs' => $appropriation->programs,
                    'office_label' => $officeLabel,
                    'authorized_appropriation' => $authorizedAppropriation,
                    'quarter1' => (float) $appropriation->quarter1,
                    'quarter2' => (float) $appropriation->quarter2,
                    'quarter3' => (float) $appropriation->quarter3,
                    'quarter4' => (float) $appropriation->quarter4,
                    'already_committed' => $alreadyCommitted,
                    'balance' => max(0, $authorizedAppropriation - $alreadyCommitted),
                ];
            }));
        }

        return response()->json([
            'items' => $items->values(),
            'ppa_code' => $ppaCode,
            'is_sef_consolidated' => $isSefConsolidated,
        ]);
    }

    /**
     * Every OfficeAllotmentClass (with the same Allotment Class code and year
     * as $office) whose office belongs to the Special Education Fund —
     * ordered by office name so the consolidated Account Codes list groups
     * consistently. Includes $office itself.
     */
    private function sefOfficeAllotmentClasses(OfficeAllotmentClass $office)
    {
        return OfficeAllotmentClass::with('offices')
            ->where('year', $office->year)
            ->whereHas('allotmentClass', fn ($q) => $q->where('class', optional($office->allotmentClass)->class))
            ->whereHas('offices', fn ($q) => $q->where('fund', 'Special Education Fund'))
            ->get()
            ->sortBy(fn ($oac) => optional($oac->offices)->office_name)
            ->values();
    }

    /**
     * AJAX: read-only preview of the ARO No. that would be generated for the
     * given office/allotment class and date of issue, without persisting.
     */
    public function previewAroNo(Request $request)
    {
        $request->validate([
            'office_allotment_classes_id' => 'required|exists:office_allotment_classes,id',
            'date_of_issue' => 'required|date',
        ]);

        return response()->json([
            'aro_no' => $this->generateAroNo(
                $request->office_allotment_classes_id,
                Carbon::parse($request->date_of_issue),
                lock: false
            ),
        ]);
    }

    /**
     * AJAX: employees eligible as Provincial Budget Officer signatories, plus
     * the Local Chief Executive Signatory auto-resolved from the employee
     * whose designation is "Provincial Governor" (may be empty if that
     * designation isn't seeded yet — the user then has to switch the LCE
     * Designation to "Acting Provincial Governor" and type the name).
     */
    public function getSignatories()
    {
        $pboEmployees = Employee::where('office', self::PBO_OFFICE_ID)->orderBy('name')->get(['id', 'name']);
        $defaultPbo = Employee::where('office', self::PBO_OFFICE_ID)->where('designation', 'Provincial Budget Officer')->first(['id', 'name']);
        $defaultGovernor = Employee::where('designation', 'Provincial Governor')->first(['id', 'name']);

        return response()->json([
            'pbo_employees' => $pboEmployees,
            'default_pbo' => $defaultPbo,
            'default_governor' => $defaultGovernor,
        ]);
    }

    private function validateAro(Request $request): array
    {
        $rules = [
            'date_of_issue' => 'required|date',
            'office_allotment_classes_id' => 'required|exists:office_allotment_classes,id',
            'fund_source' => 'required|in:Annual Budget,Supplemental Budget,Reenacted Budget',
            'supplemental_no' => 'nullable|required_if:fund_source,Supplemental Budget|string',
            'ppa_code' => 'nullable|string',
            'provincial_budget_officer_id' => 'required|exists:employees,id',
            'provincial_budget_officer_title' => 'required|string',
            'provincial_governor_title' => 'required|in:Provincial Governor,Acting Provincial Governor',
            'provincial_governor_id' => 'nullable|required_if:provincial_governor_title,Provincial Governor|exists:employees,id',
            'provincial_governor_name' => 'nullable|required_if:provincial_governor_title,Acting Provincial Governor|string',
            // appropriation_id is the sole "at least one row checked" gate — account_code
            // and ppa_description are intentionally NOT posted per row (see create.blade.php's
            // renderRows()): every item's account_code/description is re-fetched fresh from
            // the Appropriation model in saveItems() below anyway, so submitting them too
            // just doubles the POST field count for no benefit, and offices with large
            // account lists (200+ rows) were silently hitting PHP's max_input_vars limit —
            // PHP truncates the request with no error at all when that happens.
            'appropriation_id' => 'required|array|min:1',
            'appropriation_id.*' => 'required|exists:appropriations,id',
            'authorized_appropriation.*' => 'required|numeric|min:0',
            'row_ppa_code.*' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        // The comma inside "OIC, Provincial Budget Officer" breaks Laravel's pipe-delimited
        // `in:` rule syntax, so the allowed-values check happens here instead.

        if (! in_array($request->provincial_budget_officer_title, [
            'Provincial Budget Officer',
            'Acting Provincial Budget Officer',
            'OIC, Provincial Budget Officer',
        ], true)) {
            abort(422, 'Invalid Provincial Budget Officer title.');
        }
        $validated['provincial_budget_officer_title'] = $request->provincial_budget_officer_title;

        $validated['appropriation_id'] = $request->input('appropriation_id', []);
        $validated['authorized_appropriation'] = $request->input('authorized_appropriation', []);
        $validated['row_ppa_code'] = $request->input('row_ppa_code', []);

        return $validated;
    }

    /**
     * Persist the checked account-code rows for an ARO, computing For Later
     * Release / Previously Released Amount / This Release server-side —
     * never trusting client-submitted computed values.
     *
     * Both store() and update() call this only after the ARO's own prior
     * items (if any) have already been deleted/don't yet exist, so
     * alreadyCommittedForAppropriation() here naturally reflects every
     * *other* ARO's usage without needing to exclude this ARO's id.
     */
    private function saveItems(AllotmentReleaseOrder $aro, array $validated): void
    {
        $dateOfIssue = Carbon::parse($validated['date_of_issue']);
        $quarter = $this->currentQuarter($dateOfIssue);

        foreach ($validated['appropriation_id'] as $index => $appropriationId) {
            $appropriation = Appropriation::with('officeAllotmentClass.offices')->findOrFail($appropriationId);
            $authorizedAppropriation = (float) ($validated['authorized_appropriation'][$index] ?? 0);

            // Use the appropriation's OWN office/allotment class here, not the ARO's
            // anchor office_allotment_classes_id — for a SEF-consolidated ARO, a
            // checked row's appropriation can belong to a *different* SEF office than
            // the one the user picked to open the ARO, and this Supplemental lookup
            // must still resolve against that row's real office to find its amount.
            $sourceRecord = $validated['fund_source'] === 'Supplemental Budget'
                ? Supplemental::where('appropriations_id', $appropriationId)
                    ->where('office_allotment_classes_id', $appropriation->office_allotment_class_id)
                    ->where('basis_no', $validated['supplemental_no'])
                    ->first()
                : $appropriation;

            $officeLabel = optional($appropriation->officeAllotmentClass->offices)->office_name;

            // Guard against two AROs releasing the same money: this row's
            // authorized amount can't exceed what's left after every other
            // existing ARO's usage of this appropriation.
            $sourceAmount = $sourceRecord
                ? (float) ($sourceRecord instanceof Supplemental ? $sourceRecord->amount : $sourceRecord->appropriation)
                : $authorizedAppropriation;
            $alreadyCommitted = $this->alreadyCommittedForAppropriation($appropriationId, $validated['fund_source']);
            $balance = $sourceAmount - $alreadyCommitted;

            if ($authorizedAppropriation > $balance + 0.005) {
                throw new \RuntimeException(
                    "Account Code {$appropriation->account_code} has only ".number_format(max($balance, 0), 2).
                    ' remaining balance (already committed to other ARO(s)), but '.
                    number_format($authorizedAppropriation, 2).' was entered.'
                );
            }

            $forLaterRelease = $sourceRecord ? $this->calculateForLaterRelease($sourceRecord, $quarter) : 0;
            $thisRelease = $authorizedAppropriation - $forLaterRelease;

            $previouslyReleased = (float) AllotmentReleaseOrderItem::where('appropriation_id', $appropriationId)
                ->whereHas('allotmentReleaseOrder', function ($q) use ($aro, $dateOfIssue) {
                    $q->where('id', '!=', $aro->id)
                        ->where('year', $dateOfIssue->year)
                        ->where('date_of_issue', '<', $dateOfIssue);
                })
                ->sum('this_release');

            // A single ARO can roll its checked account codes under multiple PPA
            // Codes (each with its own Subtotal on the printed form) — each row
            // types its own PPA Code, defaulting client-side to the ARO's header
            // PPA Code, but falling back here too in case that's ever blank.
            $rowPpaCode = $validated['row_ppa_code'][$index] ?? null;

            AllotmentReleaseOrderItem::create([
                'allotment_release_order_id' => $aro->id,
                'appropriation_id' => $appropriationId,
                'ppa_code' => $rowPpaCode ?: ($validated['ppa_code'] ?? null),
                'account_code' => $appropriation->account_code,
                'ppa_description' => $appropriation->description,
                'programs' => $appropriation->programs,
                'office_label' => $officeLabel,
                'authorized_appropriation' => $authorizedAppropriation,
                'for_later_release' => $forLaterRelease,
                'previously_released_amount' => $previouslyReleased,
                'this_release' => $thisRelease,
            ]);
        }
    }

    /**
     * Total already committed (sum of this_release) against a given
     * appropriation across every *other* existing ARO — used both to guard
     * against double-releasing the same money and to show a live "Balance"
     * in the create/edit Account Codes table.
     *
     * Annual Budget and Reenacted Budget releases draw from the appropriation's
     * own authorized amount, while Supplemental Budget releases draw from a
     * separate Supplemental amount for the same account code — so a Supplemental
     * release must not count against (or be blocked by) an Annual/Reenacted
     * release on the same appropriation_id, and vice versa.
     */
    private function alreadyCommittedForAppropriation(int $appropriationId, string $fundSource, ?int $excludeAroId = null): float
    {
        $sourceGroup = $fundSource === 'Supplemental Budget'
            ? ['Supplemental Budget']
            : ['Annual Budget', 'Reenacted Budget'];

        return (float) AllotmentReleaseOrderItem::where('appropriation_id', $appropriationId)
            ->when($excludeAroId, fn ($q) => $q->where('allotment_release_order_id', '!=', $excludeAroId))
            ->whereHas('allotmentReleaseOrder', fn ($q) => $q->whereIn('fund_source', $sourceGroup))
            ->sum('this_release');
    }

    /**
     * Fiscal quarter (1-4) for a given date — mirrors DashboardController's
     * private currentQuarter() logic (not reusable directly since it's private there).
     */
    private function currentQuarter(Carbon $date): int
    {
        return match (true) {
            $date->month <= 3 => 1,
            $date->month <= 6 => 2,
            $date->month <= 9 => 3,
            default => 4,
        };
    }

    /**
     * Sum of whichever quarterN columns fall after the given quarter — shared
     * by both Annual (Appropriation) and Supplemental Budget (Supplemental)
     * records since both tables have identically-named quarter1..quarter4 columns.
     */
    private function calculateForLaterRelease($record, int $currentQuarter): float
    {
        $forLater = 0;
        if ($currentQuarter < 2) {
            $forLater += (float) $record->quarter2;
        }
        if ($currentQuarter < 3) {
            $forLater += (float) $record->quarter3;
        }
        if ($currentQuarter < 4) {
            $forLater += (float) $record->quarter4;
        }

        return $forLater;
    }

    /**
     * ARO No. format is {ClassPrefix}-{Year}-{Month}-{Sequence}, but the
     * Sequence itself is one continuous counter per year — shared across
     * every office, allotment class, and month, not scoped to the class
     * prefix in the number. E.g. PS-2026-08-001 can be followed by
     * MOOE-2026-08-002 or CO-2026-09-003; only a new year resets it to 001.
     */
    private function generateAroNo(int $officeAllotmentClassesId, Carbon $dateOfIssue, bool $lock = true): string
    {
        $officeAllotmentClass = OfficeAllotmentClass::with('allotmentClass')->findOrFail($officeAllotmentClassesId);
        $classCode = optional($officeAllotmentClass->allotmentClass)->class;
        // Capital Outlay's ARO No. prefix reads "CapEx", not the raw "CO" class code.
        $classPrefix = $classCode === 'CO' ? 'CapEx' : ($classCode ?? 'ARO');

        $year = (int) $dateOfIssue->format('Y');
        $month = $dateOfIssue->format('m');

        $query = AllotmentReleaseOrder::where('year', $year);
        if ($lock) {
            $query->lockForUpdate();
        }

        $lastSequence = $query->pluck('aro_no')->map(function ($aroNo) {
            $segments = explode('-', $aroNo);

            return (int) end($segments);
        })->max() ?? 0;

        $nextSequence = str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);

        return "{$classPrefix}-{$year}-{$month}-{$nextSequence}";
    }
}
