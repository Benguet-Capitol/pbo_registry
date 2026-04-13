<?php

namespace App\Http\Controllers;

use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use App\Models\OfficeAllotmentClass;
use App\Models\ObligationAmount;
use App\Models\Appropriation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ObligationAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $obligationId = $request->query('obligation_id');
        $obligation = Obligation::findOrFail($obligationId);

        $officeAllotmentClass = OfficeAllotmentClass::findOrFail($obligation->office_allotment_class_id);

        // Fetch ObligationAdjustments and related Appropriations
        $adjustments = ObligationAdjustment::where('obligation_id', $obligationId)
            ->get()
            ->map(function ($adjustment) {
                // Get the ObligationAmount and Appropriation
                $obligationAmount = ObligationAmount::find($adjustment->obligation_amounts_id);
                $appropriation = $obligationAmount ? Appropriation::find($obligationAmount->appropriation_id) : null;

                // Sum of Obligation Amounts
                $obrAmount = $obligationAmount->obr_amount ?? 0;

                // Sum of PO Amounts
                $po_amount = $obligationAmount->purchaseOrders->sum('po_amount') ?? 0;

                // Sum of earlier adjustments
                $earlierAdjustmentsSum = ObligationAdjustment::where('obligation_id', $adjustment->obligation_id)
                    ->where('obligation_amounts_id', $adjustment->obligation_amounts_id)
                    ->where('id', '<', $adjustment->id) // Only earlier adjustments
                    ->sum('adjustment_amount');

                $adjustmentSum = ObligationAdjustment::where('obligation_id', $adjustment->obligation_id)
                    ->where('obligation_amounts_id', $adjustment->obligation_amounts_id)
                    ->sum('adjustment_amount');

                // Current adjustment
                $adjustmentAmount = $adjustment->adjustment_amount ?? 0;

                // Adjusted OBR = obr + all earlier adjustments + current adjustment
                $adjustedObrAmount = $obrAmount + $adjustmentSum;

                // --- Compute Allotment (up to current quarter only) ---
                $totalAppropriation = 0;
                if ($appropriation) {
                    $currentMonth = now()->month; 
                    if ($currentMonth >= 1 && $currentMonth <= 3) {
                        $currentQuarter = 1;
                    } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
                        $currentQuarter = 2;
                    } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
                        $currentQuarter = 3;
                    } else {
                        $currentQuarter = 4;
                    }

                    if ($currentQuarter >= 1) $totalAppropriation += $appropriation->quarter1 ?? 0;
                    if ($currentQuarter >= 2) $totalAppropriation += $appropriation->quarter2 ?? 0;
                    if ($currentQuarter >= 3) $totalAppropriation += $appropriation->quarter3 ?? 0;
                    if ($currentQuarter >= 4) $totalAppropriation += $appropriation->quarter4 ?? 0;

                    // Realignments
                    $realignmentTotal = 0;
                    foreach ($appropriation->realignments ?? [] as $realignment) {
                        if ($realignment->type === 'Source') {
                            $realignmentTotal -= $realignment->amount;
                        } elseif ($realignment->type === 'Recipient') {
                            $realignmentTotal += $realignment->amount;
                        }
                    }

                    // Supplementals
                    $supplementalTotal = 0;
                    foreach ($appropriation->supplementals ?? [] as $supplemental) {
                        if ($supplemental->type === 'Reversion') {
                            $supplementalTotal -= $supplemental->amount;
                        } elseif ($supplemental->type === 'Supplemental') {
                            $supplementalTotal += $supplemental->amount;
                        }
                    }

                    $allotment = $totalAppropriation + $realignmentTotal + $supplementalTotal;
                } else {
                    $allotment = 0;
                }

                // Balance from Allotment
                $balanceFromAllotment = $allotment - $obrAmount - $earlierAdjustmentsSum;

                return [
                    'id' => $adjustment->id,
                    'obligation_amounts_id' => $adjustment->obligation_amounts_id,
                    'adjustment_date' => $adjustment->adjustment_date,
                    'adjustment_type' => $adjustment->adjustment_type ?? 'N/A',
                    'adjustment_amount' => $adjustmentAmount,
                    'earlier_adjustments' => $earlierAdjustmentsSum,
                    'adjusted_amount' => $adjustedObrAmount,
                    'obr_amount' => $obrAmount + $earlierAdjustmentsSum,
                    'adjustment_remarks' => $adjustment->adjustment_remarks,
                    'adjusted_by' => $adjustment->adjusted_by,
                    'account_code' => $appropriation->account_code ?? '',
                    'description' => $appropriation->description ?? '',
                    'program' => $appropriation->programs ?? '',
                    'allotment' => $allotment,
                    'balance_from_allotment' => $balanceFromAllotment,
                    'po_amount' => $po_amount,
                    'obr_type' => $obligationAmount->obligation->obr_type ?? '',
                ];
            })
            ->values(); // Reset keys

        // Fetch ObligationAmounts and related Appropriations
        $obligationAmounts = ObligationAmount::where('obligation_id', $obligationId)->get();

        $appropriations = $obligationAmounts->map(function ($obligationAmount) {
            $appropriation = Appropriation::find($obligationAmount->appropriation_id);
            // Sum of adjustments for this obligation_amount
            $adjustmentSum = ObligationAdjustment::where('obligation_amounts_id', $obligationAmount->id)->sum('adjustment_amount');

            return [
                'account_code' => $appropriation->account_code ?? '',
                'description' => $appropriation->description ?? '',
                'program' => $appropriation->programs ?? '',
                'obr_amount' => $obligationAmount->obr_amount ?? 0,
                'adjusted_amount' => ($obligationAmount->obr_amount ?? 0) + $adjustmentSum,
            ];
        });

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Obligations', 'route' => route('obligations.index')],
            ['label' => 'Obligation Adjustments']
        ];

        return view('obligation_adjustments.index', [
            'obligation' => $obligation,
            'officeAllotmentClass' => $officeAllotmentClass,
            'adjustments' => $adjustments,
            'obligationAmounts' => $obligationAmounts,
            'appropriations' => $appropriations,
            'breadcrumb' => $breadcrumb
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('obligation_adjustments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'obligation_id' => 'required|exists:obligations,id',
            'adjustment_date' => 'required|date',
            'adjustment_remarks' => 'nullable|string',
            'adjusted_amount' => 'required|array',
            'adjusted_amount.*' => 'nullable|numeric|min:0',
        ]);
        // Validate the adjusted_amount array to ensure it contains numeric values
        $adjustmentsSaved = 0; // Counter for saved adjustments
        $accountCodes = [];

        try {
            foreach ($validated['adjusted_amount'] as $obligationAmountId => $adjustedAmount) {
                if (is_null($adjustedAmount) || $adjustedAmount === '') {
                    continue; // Skip rows with null or empty adjusted amounts
                }
                // Check if the obligation amount ID is valid
                $obligationAmount = ObligationAmount::find($obligationAmountId);
                if (!$obligationAmount) {
                    continue; // Skip if ObligationAmount is not found
                }
                $obrAmount = $obligationAmount->obr_amount;
                // Calculate the adjustment amount
                $existingAdjustment = ObligationAdjustment::where('obligation_id', $validated['obligation_id'])
                    ->where('obligation_amounts_id', $obligationAmountId)
                    ->sum('adjustment_amount');
                $adjustmentAmount = $adjustedAmount - $obrAmount - $existingAdjustment; // Exclude previously stored adjustments
                // Check if the adjustment amount is valid (not zero)
                if ($adjustmentAmount != 0) {
                    ObligationAdjustment::create([
                        'obligation_id' => $validated['obligation_id'],
                        'obligation_amounts_id' => $obligationAmountId,
                        'adjustment_date' => $validated['adjustment_date'],
                        'adjustment_remarks' => $validated['adjustment_remarks'],
                        'adjustment_amount' => $adjustmentAmount,
                        'adjusted_by' => Auth::user()->name ?? 'Unknown User',
                    ]);
                    $adjustmentsSaved++;

                    // Collect account codes for the success message
                    $appropriation = $obligationAmount->appropriation;
                    if ($appropriation && !in_array($appropriation->account_code, $accountCodes)) {
                        $accountCodes[] = $appropriation->account_code;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error saving obligation adjustment:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Handle the error gracefully and provide feedback to the user
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the obligation adjustment: ' . $e->getMessage());
        }
        // Check if any adjustments were saved
        if ($adjustmentsSaved === 0) {
            return redirect()->back()->with('error', '<strong>No valid adjustments were saved.</strong> Please ensure that the <strong>Adjusted Amounts</strong> are different from the <strong>Amount of Obligation</strong>.');
        }

        // Prepare account codes for the success message
        $accountCodesMessage = count($accountCodes) > 1 ? implode(', ', $accountCodes) : ($accountCodes[0] ?? 'N/A');

        // Redirect back to the index page with a success message
        return redirect()->route('obligation_adjustments.index', ['obligation_id' => $validated['obligation_id']])
            ->with('status', [
                'type' => 'default',
                'message' => "<strong>$adjustmentsSaved Obligation Adjustments</strong> created successfully. <strong>Details: Adjustment Date:</strong> {$validated['adjustment_date']} with <strong>Account Code(s):</strong> {$accountCodesMessage}"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ObligationAdjustment $obligationAdjustment)
    {
        return view('obligation_adjustments.show', compact('obligationAdjustment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'edit_adjustment_date' => 'required|date',
                'edit_adjustment_remarks' => 'nullable|string',
                'edit_adjusted_amount' => 'required|numeric|min:0',
            ]);

            // Log incoming request data for debugging
            Log::info('Update Request Data:', $request->all());

            // Find the obligation adjustment or fail
            $obligationAdjustment = ObligationAdjustment::findOrFail($id);

            // Compute the adjustment amount
            $obligationAmount = ObligationAmount::findOrFail($obligationAdjustment->obligation_amounts_id);
            $obrAmount = $obligationAmount->obr_amount;
            $earlierAdjustments = ObligationAdjustment::where('obligation_id', $obligationAdjustment->obligation_id)
                ->where('obligation_amounts_id', $obligationAdjustment->obligation_amounts_id)
                ->where('id', '<', $id) // Only earlier adjustments
                ->sum('adjustment_amount');

            $adjustmentAmount = $validated['edit_adjusted_amount'] - $obrAmount - $earlierAdjustments;

            // Update the obligation adjustment
            $obligationAdjustment->update([
                'adjustment_date' => $validated['edit_adjustment_date'],
                'adjustment_remarks' => $validated['edit_adjustment_remarks'],
                'adjustment_amount' => $adjustmentAmount,
            ]);

            // Redirect to the index route with a success message
            return redirect()->route('obligation_adjustments.index', ['obligation_id' => $obligationAdjustment->obligation_id])
                ->with('status', [
                    'type' => 'update',
                    'message' => "Obligation Adjustment with <strong>Adjustment Date:</strong> {$obligationAdjustment->adjustment_date} and <strong>Account Code:</strong> " . ($obligationAmount->appropriation->account_code ?? 'N/A') . " updated successfully with <strong>Adjustment Amount:</strong> " . number_format($adjustmentAmount, 2)
                ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating Obligation Adjustment:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect back with an error message
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the Obligation Adjustment.' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObligationAdjustment $obligationAdjustment)
    {
        $obligationAmount = $obligationAdjustment->obligationAmount;
        $appropriation = $obligationAmount ? $obligationAmount->appropriation : null;

        $accountCode = $appropriation ? $appropriation->account_code : 'N/A';
        $adjustmentAmount = number_format($obligationAdjustment->adjustment_amount, 2);

        // Delete the obligation adjustment
        $obligationAdjustment->delete();

        return redirect()->route('obligation_adjustments.index', ['obligation_id' => $obligationAdjustment->obligation_id])
            ->with('status', [
                'type' => 'delete',
                'message' => "Obligation Adjustment deleted successfully. <strong>Details: Adjustment Date:</strong> {$obligationAdjustment->adjustment_date}, <strong>Account Code:</strong> {$accountCode}, <strong>Adjustment Amount:</strong> {$adjustmentAmount}"
            ]);
    }
}
