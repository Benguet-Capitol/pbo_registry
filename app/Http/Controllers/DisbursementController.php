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
