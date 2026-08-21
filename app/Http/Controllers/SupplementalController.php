<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AllotmentReleaseOrderItem;
use App\Models\Supplemental;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\ObligationAdjustment;
use App\Models\Realignment;
use App\Services\AllotmentReleaseOrderService;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class SupplementalController extends Controller
{
    private AllotmentReleaseOrderService $aroService;

    public function __construct(AllotmentReleaseOrderService $aroService)
    {
        $this->aroService = $aroService;
    }

    public function index(Request $request): View
{
    $perPage = $request->input('per_page', 'all');
    $search = $request->input('search');

    $sortBy = $request->query('sort_by', 'id');
    $sortOrder = $request->query('sort_order', 'desc');

    $currentYear = date('Y');
    $selectedYear = $request->input('year1', $currentYear);

    // Preload supplementals with office allotment class and appropriation
    $query = Supplemental::with(['officeAllotmentClass', 'appropriation'])
        ->whereHas('officeAllotmentClass', fn($q) => $q->where('year', $selectedYear));

    if ($request->filled('office_allotment_class_id')) {
        $query->where('office_allotment_classes_id', $request->office_allotment_class_id);
    }

    if ($request->filled('supplemental_type_filter')) {
        $query->where('type', $request->supplemental_type_filter);
    }

    if ($search) {
        $query->where(fn($q) => $q->where('supplemental_no', 'like', "%{$search}%")
            ->orWhere('supplemental_date', 'like', "%{$search}%")
            ->orWhere('basis_no', 'like', "%{$search}%")
            ->orWhere('basis', 'like', "%{$search}%")
            ->orWhere('type', 'like', "%{$search}%")
            ->orWhere('amount', 'like', "%{$search}%")
            ->orWhere('remarks', 'like', "%{$search}%"));
    }

    $query->orderBy($sortBy, $sortOrder);

    $supplementals = $perPage === 'all'
        ? $query->get()
        : $query->paginate($perPage)->appends([
            'year1' => $selectedYear,
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'office_allotment_classes_id' => $request->office_allotment_class_id,
            'type' => $request->supplemental_type_filter,
            'per_page' => $perPage,
        ]);

    $availableYears = OfficeAllotmentClass::distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
        ->where('year', $selectedYear)
        ->orderBy('office', 'asc')
        ->get();

    $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
        ->select('id', 'office_abbreviation', 'class', 'fund')
        ->where('year', $currentYear)
        ->get();

    // Optimize appropriations calculation with eager loading to avoid N+1
    $appropriations = Appropriation::with([
        'obligationAmounts.obligationAdjustments',
        'realignments',
        'supplementals'
    ])->select(
        'id', 'office_allotment_class_id', 'account_code', 'description', 'programs',
        'quarter1', 'quarter2', 'quarter3', 'quarter4'
    )->get()->map(function ($appropriation) {
        // Determine current quarter
        $month = now()->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        // Sum appropriation up to current quarter
        $totalAppropriation = array_sum(array_slice(
            [$appropriation->quarter1, $appropriation->quarter2, $appropriation->quarter3, $appropriation->quarter4],
            0, $currentQuarter
        ));

        // Total obligation including adjustments
        $totalObrAmount = $appropriation->obligationAmounts->sum(fn($obr) =>
            $obr->obr_amount + $obr->obligationAdjustments->sum('adjustment_amount')
        );

        // Realignments
        $realignmentTotal = $appropriation->realignments->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

        // Supplemental appropriations
        $supplementalTotal = $appropriation->supplementals->sum(fn($s) => $s->type === 'Reversion' ? -$s->amount : $s->amount);

        // Calculate balances
        $appropriation->balance = ($totalAppropriation + $realignmentTotal + $supplementalTotal) - $totalObrAmount;
        $appropriation->balance_from_allotment = ($totalAppropriation + $realignmentTotal) - $totalObrAmount;

        return $appropriation;
    });

    $breadcrumb = [
        ['label' => 'Dashboard', 'route' => route('dashboard')],
        ['label' => 'Supplemental Appropriations | Reversions']
    ];

    $supplementalsBulkDelete = Supplemental::with('appropriation')
        ->when($request->year1, function ($query) use ($request) {
            $query->whereHas('officeAllotmentClass', function ($q) use ($request) {
                $q->where('year', $request->year1);
            });
        })
        ->get();

    // Calculate total records (unique supplemental_no values)
    $totalRecordsQuery = Supplemental::with(['officeAllotmentClass', 'appropriation'])
        ->whereHas('officeAllotmentClass', fn($q) => $q->where('year', $selectedYear));

    if ($request->filled('office_allotment_class_id')) {
        $totalRecordsQuery->where('office_allotment_classes_id', $request->office_allotment_class_id);
    }

    if ($request->filled('supplemental_type_filter')) {
        $totalRecordsQuery->where('type', $request->supplemental_type_filter);
    }

    if ($search) {
        $totalRecordsQuery->where(fn($q) => $q->where('supplemental_no', 'like', "%{$search}%")
            ->orWhere('supplemental_date', 'like', "%{$search}%")
            ->orWhere('basis_no', 'like', "%{$search}%")
            ->orWhere('basis', 'like', "%{$search}%")
            ->orWhere('type', 'like', "%{$search}%")
            ->orWhere('amount', 'like', "%{$search}%")
            ->orWhere('remarks', 'like', "%{$search}%"));
    }

    $totalRecords = $totalRecordsQuery->distinct('supplemental_no')->count('supplemental_no');

    [$existingAroByRow, $existingAroByBatch, $staleAroByRow, $staleAroByBatch] = $this->buildExistingAroLookups($supplementals);

    return view('supplementals.index', compact(
        'supplementals', 'perPage', 'search', 'sortBy', 'sortOrder',
        'availableYears', 'selectedYear', 'officeAllotmentClasses',
        'office_allotment_classes', 'appropriations', 'breadcrumb', 'supplementalsBulkDelete', 'totalRecords',
        'existingAroByRow', 'existingAroByBatch', 'staleAroByRow', 'staleAroByBatch'
    ));
}

    /**
     * Looks up, in one batched query, which of the currently-listed
     * "Supplemental" rows already have an ARO releasing them — lets the index
     * view show a "Preview" button (list view: per row/account code; card
     * view: per SB No. batch) instead of the user opening each one to find
     * out. "Reversion" rows are skipped — no release ever applies to them.
     *
     * Row-level matching deliberately does NOT require the ARO's own
     * office_allotment_classes_id to equal the row's — a Special Education
     * Fund ARO consolidates account codes across every SEF office sharing the
     * same Allotment Class/year (see getAppropriationsByClass()'s
     * sefOfficeAllotmentClasses()), anchored at whichever one of those offices
     * was picked when it was created, so the anchor can legitimately differ
     * from this row's own office. appropriation_id already narrows to one
     * specific account code, and requiring it to actually be an item on the
     * ARO is definitive proof the money was released through it.
     *
     * A row/batch can have more than one matching ARO — e.g. two separate
     * partial releases against the same appropriation under the same SB No.
     * over time — so each entry is a list (most recent first), not a single
     * ARO; the index view offers a picker via <x-aro-preview-picker> whenever
     * a list has more than one.
     *
     * Also flags rows/batches whose current Supplemental amount no longer
     * matches what their ARO(s) actually authorized — e.g. the Supplemental
     * was edited and the linked ARO wasn't updated to match (the user picked
     * "Not Now" on that prompt) — so the index can surface a "stale" warning
     * instead of silently leaving the two out of sync with no visible sign.
     *
     * @return array{0: array<string, list<object{id:int, aro_no:string, date_of_issue:?\Carbon\Carbon}>>, 1: array<string, list<object{id:int, aro_no:string, date_of_issue:?\Carbon\Carbon}>>, 2: array<string, bool>, 3: array<string, bool>}
     *         [0] byRow keyed by "appropriation_id|basis_no" (list view)
     *         [1] byBatch keyed by the batch's own supplemental_no tracking code (card view)
     *         [2] staleByRow — same keys as [0], true where the amounts don't match
     *         [3] staleByBatch — same keys as [1], true if any row in that batch is stale
     */
    private function buildExistingAroLookups($supplementals): array
    {
        $rows = collect($supplementals)->filter(
            fn ($s) => trim($s->type ?? '') === 'Supplemental' && $s->appropriations_id && $s->basis_no
        );

        if ($rows->isEmpty()) {
            return [[], [], [], []];
        }

        $appropriationIds = $rows->pluck('appropriations_id')->unique()->values();

        $matches = AllotmentReleaseOrderItem::query()
            ->join('allotment_release_orders', 'allotment_release_orders.id', '=', 'allotment_release_order_items.allotment_release_order_id')
            ->where('allotment_release_orders.fund_source', 'Supplemental Budget')
            ->whereIn('allotment_release_order_items.appropriation_id', $appropriationIds)
            ->orderByDesc('allotment_release_orders.date_of_issue')
            ->orderByDesc('allotment_release_orders.id')
            ->select([
                'allotment_release_order_items.appropriation_id',
                'allotment_release_order_items.authorized_appropriation',
                'allotment_release_orders.id as aro_id',
                'allotment_release_orders.aro_no',
                'allotment_release_orders.date_of_issue',
                'allotment_release_orders.supplemental_no',
            ])
            ->get();

        $byRow = [];
        $totalAuthorizedByRow = [];
        foreach ($matches as $match) {
            if ((string) $match->supplemental_no !== '') {
                $key = "{$match->appropriation_id}|{$match->supplemental_no}";
                $byRow[$key][] = (object) [
                    'id' => $match->aro_id,
                    'aro_no' => $match->aro_no,
                    'date_of_issue' => $match->date_of_issue ? \Carbon\Carbon::parse($match->date_of_issue) : null,
                ];
                $totalAuthorizedByRow[$key] = ($totalAuthorizedByRow[$key] ?? 0) + (float) $match->authorized_appropriation;
            }
        }

        // Batch-level (card view): union of every row's matches in the batch
        // (a batch is every row sharing one supplemental_no tracking code,
        // which — since update() keeps office_allotment_classes_id/basis_no in
        // sync across all of a batch's rows — already correctly disambiguates
        // two unrelated offices that happen to reuse the same literal SB No.
        // text), deduped by ARO id since more than one row can point at the
        // same ARO.
        $byBatch = [];
        $staleByRow = [];
        $staleByBatch = [];
        foreach ($rows as $row) {
            $key = "{$row->appropriations_id}|{$row->basis_no}";
            $rowMatches = $byRow[$key] ?? [];
            if (! $rowMatches) {
                continue;
            }

            $existing = $byBatch[$row->supplemental_no] ?? [];
            $byBatch[$row->supplemental_no] = collect(array_merge($existing, $rowMatches))
                ->unique('id')
                ->sortByDesc(fn ($aro) => $aro->date_of_issue)
                ->values()
                ->all();

            $rowAmount = (float) str_replace(',', '', (string) $row->amount);
            $isStale = abs($rowAmount - ($totalAuthorizedByRow[$key] ?? 0)) > 0.005;
            $staleByRow[$key] = $isStale;
            if ($isStale) {
                $staleByBatch[$row->supplemental_no] = true;
            }
        }

        return [$byRow, $byBatch, $staleByRow, $staleByBatch];
    }

    public function create(): View
    {
        // Fetch all OfficeAllotmentClass records (customize filter as needed)
        $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass'])->get();

        // Fetch appropriations with only the fields needed for the modal, and append calculated balance
        $appropriations = Appropriation::select('id', 'account_code', 'programs', 'description', 'office_allotment_class_id')
            ->with(['officeAllotmentClass', 'obligationAmounts'])
            ->get()
            ->map(function ($item) {
                $item->balance = $item->calculateBalance(); // Assumes you have a calculateBalance() accessor/method
                return $item;
            });

        return view('supplementals.create', compact('office_allotment_classes', 'appropriations'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            // Validate the main supplemental fields
            $validated = $request->validate([
                'office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
                'supplemental_no' => 'required|string|max:255',
                'supplemental_date' => 'required|date',
                'type' => 'required|string',
                'basis_no' => 'nullable|string|max:255',
                'basis' => 'required|string',
            ]);

            // Get all program table fields
            $programs = $request->input('programs', []);
            $account_codes = $request->input('account_code', []);
            $descriptions = $request->input('description', []);
            $amounts = $request->input('amount_of_obligation', []);
            $quarters_1 = $request->input('quarter_1', []);
            $quarters_2 = $request->input('quarter_2', []);
            $quarters_3 = $request->input('quarter_3', []);
            $quarters_4 = $request->input('quarter_4', []);

            // For each row in the programs table, create a Supplemental record
            foreach ($account_codes as $i => $account_code) {
                // Find the appropriation by account_code and office_allotment_class_id
                $appropriation = Appropriation::where('account_code', $account_code)
                    ->where('office_allotment_class_id', $validated['office_allotment_class_id'])
                    ->first();

                $dataToInsert = [
                    'office_allotment_classes_id' => $validated['office_allotment_class_id'],
                    'appropriations_id' => $appropriation ? $appropriation->id : null,
                    'supplemental_no' => $validated['supplemental_no'],
                    'supplemental_date' => $validated['supplemental_date'],
                    'type' => $validated['type'],
                    'basis_no' => $validated['basis_no'] ?? null,
                    'basis' => $validated['basis'],
                    'account_code' => $account_code,
                    'description' => $descriptions[$i] ?? null,
                    'program' => $programs[$i] ?? null,
                    'amount' => $amounts[$i] ?? null,
                    'quarter1' => $quarters_1[$i] ?? '0.00',
                    'quarter2' => $quarters_2[$i] ?? '0.00',
                    'quarter3' => $quarters_3[$i] ?? '0.00',
                    'quarter4' => $quarters_4[$i] ?? '0.00',
                ];
                Log::info('Attempting to insert supplemental row', $dataToInsert);
                Supplemental::create($dataToInsert);
            }

            $redirectParams = $request->only(['year1', 'office_allotment_class_filter', 'supplemental_type_filter', 'per_page', 'search']);

            // A brand-new "Supplemental" batch (adds funds) can immediately be
            // released via an ARO — deep-link into the (embedded) ARO create modal
            // on this same index, pre-scoped/pre-filled from this batch, instead of
            // making the user look everything up again. "Reversion" reduces funds
            // instead, so no release applies there.
            if ($validated['type'] === 'Supplemental') {
                $redirectParams['open_aro_create'] = 1;
                $redirectParams['aro_office_allotment_classes_id'] = $validated['office_allotment_class_id'];
                $redirectParams['aro_supplemental_no'] = $validated['basis_no'] ?? '';
                $redirectParams['aro_date_of_issue'] = $validated['supplemental_date'];
            }

            return redirect()->route('supplementals.index', $redirectParams)
                ->with('status', "<strong>{$validated['type']}</strong> No. <strong>{$validated['supplemental_no']}</strong> has been created successfully!");
        } catch (\Exception $e) {
            Log::error('Error saving supplemental: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->withInput()->with('status', 'Error saving supplemental: ' . $e->getMessage());
        }
    }

    public function edit(Supplemental $supplemental): View
    {
        return view('supplementals.edit', compact('supplementals'));
    }

    public function update(Request $request, Supplemental $supplemental): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'edit_office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
                'edit_supplemental_no' => 'required|string|max:255',
                'edit_supplemental_date' => 'required|date',
                'edit_type' => 'required|string',
                'edit_basis_no' => 'nullable|string|max:255',
                'edit_basis' => 'required|string',
                'edit_account_code' => 'required|string',
                'edit_description' => 'nullable|string|max:255',
                'edit_amount_of_obligation' => 'required|string',
                'edit_quarter_1' => 'nullable|string',
                'edit_quarter_2' => 'nullable|string',
                'edit_quarter_3' => 'nullable|string',
                'edit_quarter_4' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Store the original supplemental_no to find all related rows
            $originalSupplementalNo = $supplemental->supplemental_no;

            // Find the appropriation by account_code and office_allotment_class_id
            $appropriation = Appropriation::where('account_code', $validated['edit_account_code'])
                ->where('office_allotment_class_id', $validated['edit_office_allotment_class_id'])
                ->first();

            // Update the current row with all fields
            $supplemental->update([
                'office_allotment_classes_id' => $validated['edit_office_allotment_class_id'],
                'appropriations_id' => $appropriation ? $appropriation->id : null,
                'supplemental_no' => $validated['edit_supplemental_no'],
                'supplemental_date' => $validated['edit_supplemental_date'],
                'type' => $validated['edit_type'],
                'basis_no' => $validated['edit_basis_no'] ?? null,
                'basis' => $validated['edit_basis'],
                'amount' => $validated['edit_amount_of_obligation'],
                'quarter1' => $validated['edit_quarter_1'] ?? '0.00',
                'quarter2' => $validated['edit_quarter_2'] ?? '0.00',
                'quarter3' => $validated['edit_quarter_3'] ?? '0.00',
                'quarter4' => $validated['edit_quarter_4'] ?? '0.00',
            ]);

            // Update shared fields for all other rows with the same original supplemental_no
            // Exclude the current row being edited
            Supplemental::where('supplemental_no', $originalSupplementalNo)
                ->where('id', '!=', $supplemental->id)
                ->update([
                    'office_allotment_classes_id' => $validated['edit_office_allotment_class_id'],
                    'supplemental_no' => $validated['edit_supplemental_no'],
                    'supplemental_date' => $validated['edit_supplemental_date'],
                    'type' => $validated['edit_type'],
                    'basis_no' => $validated['edit_basis_no'] ?? null,
                    'basis' => $validated['edit_basis'],
                ]);

            DB::commit();

            $redirectParams = $request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search']);

            // Editing a "Supplemental" (not "Reversion" — no release applies there)
            // may affect an ARO already tied to it, or mean one still doesn't exist
            // yet — either way, prompt on the index instead of silently leaving the
            // ARO out of sync with what was just edited.
            if ($validated['edit_type'] === 'Supplemental' && $appropriation) {
                $existingAro = $this->aroService->findAroForSupplemental(
                    $appropriation->id,
                    $validated['edit_basis_no'] ?? null,
                );

                if ($existingAro) {
                    $redirectParams['existing_aro_id'] = $existingAro->id;
                } else {
                    $redirectParams['open_aro_create_direct'] = 1;
                    $redirectParams['aro_office_allotment_classes_id'] = $validated['edit_office_allotment_class_id'];
                    $redirectParams['aro_supplemental_no'] = $validated['edit_basis_no'] ?? '';
                    $redirectParams['aro_date_of_issue'] = $validated['edit_supplemental_date'];
                }
            }

            return redirect()->route('supplementals.index', $redirectParams)
                ->with('status', "<strong>{$validated['edit_type']}</strong> No. <strong>{$validated['edit_supplemental_no']}</strong> with Account Code: <strong>{$validated['edit_account_code']} - {$validated['edit_description']}</strong> has been updated successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating supplemental: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating supplemental: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Supplemental $supplemental): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store details before deletion
            $supplementalNo = $supplemental->supplemental_no;
            $type = $supplemental->type;
            $amount = $supplemental->amount;
            $appropriation = Appropriation::find($supplemental->appropriations_id);
            $accountCode = $appropriation ? $appropriation->account_code : 'N/A';
            $description = $appropriation ? $appropriation->description : 'N/A';

            // Check if this is a bulk delete request
            $isBulkDelete = $request->input('bulk_delete') === '1';

            if ($isBulkDelete) {
                // Delete all supplementals with the same supplemental_no
                $relatedSupplementals = Supplemental::where('supplemental_no', $supplementalNo)->get();
                $deletedCount = $relatedSupplementals->count();
                
                // Check if any related supplemental has obligations created after the supplemental
                foreach ($relatedSupplementals as $related) {
                    if ($related->type === 'Supplemental') {
                        $appropriationCheck = Appropriation::find($related->appropriations_id);
                        if ($appropriationCheck) {
                            // Get obligations and check their dates
                            $obligationAmounts = ObligationAmount::where('appropriation_id', $appropriationCheck->id)
                                ->with('obligation')
                                ->get();
                            
                            // Check if any obligation was created after (or on same date as) the supplemental
                            $supplementalDate = \Carbon\Carbon::parse($related->supplemental_date);
                            $blockedObligations = 0;
                            
                            foreach ($obligationAmounts as $oa) {
                                if ($oa->obligation) {
                                    $obligationDate = \Carbon\Carbon::parse($oa->obligation->obr_date);
                                    if (!$obligationDate->lt($supplementalDate)) {
                                        $blockedObligations++;
                                    }
                                }
                            }
                            
                            if ($blockedObligations > 0) {
                                DB::rollBack();
                                return redirect()->route('supplementals.index', array_filter($request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search'])))
                                    ->with('error', 
                                        "Cannot delete Supplemental/Reversion No: <strong>{$supplementalNo}</strong>. " .
                                        "One or more supplemental accounts in this transaction have <strong>{$blockedObligations}</strong> obligation(s) created after the supplemental date. " .
                                        "Please delete the related obligations first before removing this supplemental/reversion."
                                    );
                            }
                        }
                    }
                }

                // Delete all related supplementals
                Supplemental::where('supplemental_no', $supplementalNo)->delete();

                DB::commit();

                return redirect()->route('supplementals.index', array_filter($request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search'])))
                    ->with('status',
                        "All <strong>{$deletedCount}</strong> supplemental/reversion(s) with No: <strong>{$supplementalNo}</strong> have been deleted successfully!"
                    );
            } else {
                // Single delete - check for obligations created after the supplemental
                if ($type === 'Supplemental' && $appropriation) {
                    $obligationAmounts = ObligationAmount::where('appropriation_id', $appropriation->id)
                        ->with('obligation')
                        ->get();
                    
                    // Check if any obligation was created after (or on same date as) the supplemental
                    $supplementalDate = \Carbon\Carbon::parse($supplemental->supplemental_date);
                    $blockedObligations = 0;
                    
                    foreach ($obligationAmounts as $oa) {
                        if ($oa->obligation) {
                            $obligationDate = \Carbon\Carbon::parse($oa->obligation->obr_date);
                            if (!$obligationDate->lt($supplementalDate)) {
                                $blockedObligations++;
                            }
                        }
                    }
                    
                    if ($blockedObligations > 0) {
                        DB::rollBack();
                        return redirect()->route('supplementals.index', array_filter($request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search'])))
                            ->with('error', 
                                "Cannot delete <strong>{$type}</strong> No. <strong>{$supplementalNo}</strong> for Account Code: <strong>{$accountCode}</strong>. " .
                                "This supplemental has <strong>{$blockedObligations}</strong> obligation(s) created after the supplemental date. " .
                                "Please delete the related obligations first before removing this supplemental entry."
                            );
                    }
                }

                // Count related records BEFORE deletion
                $relatedCount = Supplemental::where('supplemental_no', $supplementalNo)
                    ->where('id', '!=', $supplemental->id)
                    ->count();

                // Delete the single supplemental
                $supplemental->delete();

                DB::commit();

                $warningMessage = '';
                if ($relatedCount > 0) {
                    $warningMessage = " <strong>Note:</strong> There are still <strong>{$relatedCount}</strong> related supplemental/reversion(s) with the same No.";
                }

                return redirect()->route('supplementals.index', array_filter($request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search'])))
                    ->with('status',
                        '<strong>' . $type . '</strong> No. <strong>' . $supplementalNo . '</strong> with Account Code: <strong>' . $accountCode . '</strong> - <strong>' . $description . '</strong> and Amount: <strong>' . number_format($amount, 2) . '</strong> has been deleted successfully!' . $warningMessage
                    );
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Supplemental | Reversion Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'supplemental_id' => $supplemental->id ?? null,
                'is_bulk_delete' => $isBulkDelete ?? false,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->route('supplementals.index', array_filter($request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search'])))
                ->with('error', 'An error occurred while deleting the supplemental/reversion: ' . $e->getMessage());
        }
    }

    /**
     * Check if supplemental can be deleted based on obligation date
     */
    public function checkSupplementalDeletionDate(Request $request)
    {
        $validated = $request->validate([
            'supplemental_id' => 'required|integer',
        ]);

        $supplemental = Supplemental::find($validated['supplemental_id']);
        if (!$supplemental) {
            return response()->json([
                'canDelete' => false,
                'message' => 'Supplemental not found'
            ], 404);
        }

        // Get the appropriation and related obligations
        $appropriation = Appropriation::find($supplemental->appropriations_id);
        if (!$appropriation) {
            return response()->json([
                'canDelete' => false,
                'message' => 'Appropriation not found'
            ], 404);
        }

        // Get the earliest obligation date for this appropriation
        $earliestObligation = ObligationAmount::where('appropriation_id', $appropriation->id)
            ->with('obligation')
            ->get()
            ->map(function ($oam) {
                return $oam->obligation;
            })
            ->filter()
            ->sortBy('obr_date')
            ->first();

        // If no obligations exist, allow deletion
        if (!$earliestObligation) {
            return response()->json([
                'canDelete' => true,
                'message' => 'No obligations associated with this supplemental'
            ]);
        }

        // Convert supplemental_date to comparable format
        $supplementalDate = \Carbon\Carbon::parse($supplemental->supplemental_date);
        $obligationDate = \Carbon\Carbon::parse($earliestObligation->obr_date);

        // Supplemental can be deleted if obligation was created BEFORE supplemental
        $canDelete = $obligationDate->lt($supplementalDate);

        return response()->json([
            'canDelete' => $canDelete,
            'message' => $canDelete 
                ? 'Supplemental can be deleted' 
                : 'Cannot delete: Obligation was created after or on the same date as this supplemental',
            'supplemental_date' => $supplementalDate->format('Y-m-d'),
            'earliest_obligation_date' => $obligationDate->format('Y-m-d')
        ]);
    }
}
