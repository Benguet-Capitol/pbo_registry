<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplemental;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\ObligationAdjustment;
use App\Models\Realignment;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class SupplementalController extends Controller
{
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

    return view('supplementals.index', compact(
        'supplementals', 'perPage', 'search', 'sortBy', 'sortOrder',
        'availableYears', 'selectedYear', 'officeAllotmentClasses',
        'office_allotment_classes', 'appropriations', 'breadcrumb'
    ));
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
            $balances = $request->input('balance_from_allotment', []);
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

            return redirect()->route('supplementals.index', $request->only(['year1', 'office_allotment_class_filter', 'supplemental_type_filter', 'per_page', 'search']))
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

            // Find the appropriation by account_code and office_allotment_class_id
            $appropriation = Appropriation::where('account_code', $validated['edit_account_code'])
                ->where('office_allotment_class_id', $validated['edit_office_allotment_class_id'])
                ->first();

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

            return redirect()->route('supplementals.index', $request->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search']))
            ->with('status', "<strong>{$validated['edit_type']}</strong> No. <strong>{$validated['edit_supplemental_no']}</strong> with Account Code: <strong>{$validated['edit_account_code']} - {$validated['edit_description']}</strong>  has been updated successfully!");
        } catch (\Exception $e) {
            Log::error('Error updating supplemental: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->withInput()->with('status', 'Error updating supplemental: ' . $e->getMessage());
        }
    }

    public function destroy(Supplemental $supplemental): RedirectResponse
    {
        try {
            // Get the related appropriation
            $appropriation = Appropriation::find($supplemental->appropriations_id);
            $accountCode = $appropriation ? $appropriation->account_code : '';
            $description = $appropriation ? $appropriation->description : '';

            $supplemental->delete();

            return redirect()->route('supplementals.index', request()->only(['year1', 'office_allotment_class_id', 'supplemental_type_filter', 'per_page', 'search']))
            ->with('status',
                '<strong>' . $supplemental->type . '</strong> No.: <strong>' . $supplemental->supplemental_no . '</strong> with Account Code: <strong>' . $accountCode . '</strong> - <strong>' . $description . '</strong> has been deleted successfully!'
            );
        } catch (\Throwable $e) {
            Log::error('Supplemental | Reversion Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'supplemental_id' => $supplemental->id
            ]);
            return redirect()->back()->with('status', 'An error occurred while deleting the supplemental / reversion: ' . $e->getMessage());
        }
    }
}
