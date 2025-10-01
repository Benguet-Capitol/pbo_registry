<?php

namespace App\Http\Controllers;

use App\Models\Appropriation;
use App\Models\Disbursement;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use App\Models\ObligationAmount;
use App\Models\OfficeAllotmentClass;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DisbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate the request to ensure 'obligation_id' is present
        $obligationId = $request->query('obligation_id');
        $obligation = Obligation::findOrFail($obligationId);
        // Fetches the related OfficeAllotmentClass using the foreign key from the Obligation
        $officeAllotmentClass = OfficeAllotmentClass::findOrFail($obligation->office_allotment_class_id);
        // Fetch ObligationAdjustments and related Appropriations
        $disbursements = Disbursement::with('obligationAmount.appropriation')
            ->where('obligation_id', $obligationId)
            ->get();

        // Sort disbursements by dv_number and mark duplicates for view
        $disbursements = $disbursements->sortBy('dv_number')->values();
        $displayedDvNumbers = [];
        foreach ($disbursements as $disbursement) {
            $dvNumber = $disbursement['dv_number'];
            $isDuplicateDv = in_array($dvNumber, $displayedDvNumbers);
            $disbursement->is_duplicate_dv = $isDuplicateDv;
            if (!$isDuplicateDv) {
                $displayedDvNumbers[] = $dvNumber;
            }
        }

        // Fetch ObligationAmounts and related Appropriations
        $obligationAmounts = ObligationAmount::with('appropriation')
            ->where('obligation_id', $obligationId)
            ->get();
        // Map the ObligationAmounts to include Appropriation details and Disbursement amount
        $appropriations = $obligationAmounts->map(function ($obligationAmount) {
            $appropriation = $obligationAmount->appropriation;
            $poAmount = PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)->sum('po_amount');
            $disbursementAmount = Disbursement::where('obligation_amounts_id', $obligationAmount->id)->sum('disbursement_amount');
            $adjustmentSum = ObligationAdjustment::where('obligation_amounts_id', $obligationAmount->id)->sum('adjustment_amount');
            return [
                'program' => $appropriation->programs ?? '',
                'account_code' => $appropriation->account_code ?? '',
                'description' => $appropriation->description ?? '',
                'obr_amount' => ($obligationAmount->obr_amount ?? 0) + $adjustmentSum,
                'po_amount' => $poAmount,
                'disbursement_amount' => $disbursementAmount,
            ];
        });

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Obligations', 'route' => route('obligations.index')],
            ['label' => 'Disbursements']
        ];

        return view('disbursements.index', [
            'obligation' => $obligation,
            'officeAllotmentClass' => $officeAllotmentClass,
            'disbursements' => $disbursements,
            'obligationAmounts' => $obligationAmounts,
            'appropriations' => $appropriations,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Display all purchase orders in a single view with edit capability.
     */
     public function all(Request $request)
    {
        // Get the per page value from the request
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');
        // Get the sort by and sort order values from the request
        $sortBy = $request->query('sort_by', 'obr_date');
        $sortOrder = $request->query('sort_order', 'desc');

        // Get the selected year or default to the current year
        $currentYear = date('Y');
        $selectedYear = $request->input('year1', $currentYear);

        $query = Disbursement::with([
                'obligation.obligationAmounts.appropriation',
                'obligation.officeAllotmentClass.offices',
                'obligation.officeAllotmentClass.allotmentClass',
                ])
            ->whereHas('obligation.officeAllotmentClass', function ($q) use ($selectedYear) {
                $q->where('year', $selectedYear);
            });

        // Apply filter for office_allotment_class_filter
        if ($request->filled('office_allotment_class_filter')) {
            $query->whereHas('obligation.officeAllotmentClass', function ($q) use ($request) {
                $q->where('id', $request->input('office_allotment_class_filter'));
            });
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('disbursement_date', 'like', '%' . $search . '%')
                  ->orWhere('dv_no', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%')
                    ->orWhere('disbursement_amount', 'like', '%' . $search . '%')
                  ->orWhereHas('obligation.obligationAmounts.appropriation', function ($q2) use ($search) {
                      $q2->where('account_code', 'like', '%' . $search . '%')
                         ->orWhere('description', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('obligation.officeAllotmentClass.offices', function ($q3) use ($search) {
                      $q3->where('office_abbreviation', 'like', '%' . $search . '%');
                  })->orWhereHas('obligation.officeAllotmentClass.allotmentClass', function ($q4) use ($search) {
                      $q4->where('class', 'like', '%' . $search . '%');
                  });
            });
        }

        // Apply sorting and pagination
        if ($sortBy && in_array($sortBy, ['disbursement_date', 'dv_no', 'status', 'remarks', 'disbursement_amount'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            // Default sorting by obr_date descending
            $query->orderBy('disbursement_date', 'desc');
        }

        if ($perPage === 'all') {
            $disbursements = $query->get();
        } else {
            $perPage = is_numeric($perPage) ? (int)$perPage : 10; // Default to 10 if invalid
            $disbursements = $query->paginate($perPage)->appends($request->query());
        }

        // Fetch distinct years from the database
        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        // Get the list of office allotment classes filtered by the selected year
        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->orderBy('office', 'asc')
            ->get();
        // Get the list of office allotment classes filtered by the selected year
        $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->where('year', $currentYear) // Filter by the current year 
            ->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Disbursements']
        ];

            
        return view('disbursements.index_all', compact('disbursements', 'breadcrumb', 'availableYears', 'selectedYear', 'perPage', 'search', 'sortBy', 'sortOrder', 'officeAllotmentClasses', 'office_allotment_classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'obligation_id' => 'required|exists:obligations,id',
            'disbursement_date' => 'required|date',
            'dv_no' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'disbursement_amount' => 'required|array',
            'disbursement_amount.*' => 'nullable|numeric|min:0',
        ]);

        $savedDVs = 0;
        $accountCodes = [];

        try {
            foreach ($validated['disbursement_amount'] as $obligationAmountId => $dvAmount) {
                if (is_null($dvAmount) || $dvAmount === '') {
                    continue; // Skip empty inputs
                }

                $obligationAmount = ObligationAmount::find($obligationAmountId);
                if (!$obligationAmount) {
                    continue; // Invalid reference
                }

                $existingDV = Disbursement::where('obligation_amounts_id', $obligationAmountId)->sum('disbursement_amount');

                if ($dvAmount <= 0) {
                    continue; // Skip zero or negative amounts
                }

                Disbursement::create([
                    'obligation_id' => $validated['obligation_id'],
                    'obligation_amounts_id' => $obligationAmountId,
                    'dv_no' => $validated['dv_no'],
                    'disbursement_date' => $validated['disbursement_date'],
                    'remarks' => $validated['remarks'],
                    'status' => $validated['status'],
                    'disbursement_amount' => $dvAmount,
                ]);

                $savedDVs++;

                $appropriation = $obligationAmount->appropriation;
                if ($appropriation && !in_array($appropriation->account_code, $accountCodes)) {
                    $accountCodes[] = $appropriation->account_code;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error saving disbursement:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the disbursement: ' . $e->getMessage());
        }

        if ($savedDVs === 0) {
            return redirect()->back()->with('error', 'No valid disbursement were saved.');
        }

        $accountCodesMessage = count($accountCodes) > 1 ? implode(', ', $accountCodes) : ($accountCodes[0] ?? 'N/A');

        return redirect()->route('disbursements.index', ['obligation_id' => $validated['obligation_id']])
            ->with('status', [
                'type' => 'default',
                'message' => "DV / Check No: <strong>{$validated['dv_no']}</strong> with Date: <strong>{$validated['disbursement_date']}</strong> under Account Code(s): <strong>{$accountCodesMessage}</strong> has been created successfully!"
            ]);
        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Disbursement $disbursement)
    {
        try {
            $validated = $request->validate([
                'disbursement_id' => 'required|exists:disbursements,id',
                'edit_disbursement_date' => 'required|date',
                'edit_dv_no' => 'required|string|max:255',
                'edit_status' => 'required|string|max:255',
                'edit_remarks' => 'nullable|string|max:1000',
                'edit_disbursement_amount' => 'required|array',
                'edit_edit_disbursement_amount_amount.*' => 'nullable|numeric|min:0',
            ]);

            $disbursement = Disbursement::findOrFail($validated['disbursement_id']);
            $obligationId = $disbursement->obligation_amounts_id;

            // Get DV amount specifically for this obligation
            $dvAmount = $validated['edit_disbursement_amount'][$obligationId] ?? 0;

            $disbursement->update([
                'disbursement_date' => $validated['edit_disbursement_date'],
                'dv_no' => $validated['edit_dv_no'],
                'status' => $validated['edit_status'],
                'remarks' => $validated['edit_remarks'],
                'disbursement_amount' => $dvAmount,
            ]);

            $accountCodesMessage = optional($disbursement->obligationAmount->appropriation)->account_code ?? 'N/A';

            return redirect()->route('disbursements.index', [
                'obligation_id' => $disbursement->obligation_id,
            ])->with('status', [
                'type' => 'update',
                'message' => "DV / Check No: <strong>{$disbursement->dv_no}</strong> has been <strong>updated</strong> under <strong>Account Code:</strong> {$accountCodesMessage}."
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating disbursement:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the disbursement: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disbursement $disbursement)
    {
        $disbursement->delete();

        $accountCode = optional(optional($disbursement->obligationAmount)->appropriation)->account_code ?? 'N/A';

        return redirect()->route('disbursements.index', [
            'obligation_id' => $disbursement->obligation_id,
        ])->with('status', [
            'type' => 'delete',
            'message' => "DV / Check No: <strong>{$disbursement->dv_no}</strong> dated <strong>{$disbursement->disbursement_date}</strong> under Account Code: <strong>{$accountCode}</strong> has been <strong>deleted</strong>!"
        ]);
    }
}
