<?php

namespace App\Http\Controllers;

use App\Models\Appropriation;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use App\Models\ObligationAmount;
use App\Models\OfficeAllotmentClass;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
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
        $purchase_orders = PurchaseOrder::with('obligationAmount.appropriation')
            ->where('obligation_id', $obligationId)
            ->get();

        // Sort purchase orders by po_number and mark duplicates for view
        $purchase_orders = $purchase_orders->sortBy('po_number')->values();
        $displayedPoNumbers = [];
        foreach ($purchase_orders as $purchase_order) {
            $poNumber = $purchase_order['po_number'];
            $isDuplicatePo = in_array($poNumber, $displayedPoNumbers);
            $purchase_order->is_duplicate_po = $isDuplicatePo;
            if (!$isDuplicatePo) {
                $displayedPoNumbers[] = $poNumber;
            }
        }

        // Fetch ObligationAmounts and related Appropriations
        $obligationAmounts = ObligationAmount::with('appropriation')
            ->where('obligation_id', $obligationId)
            ->get();
        // Map the ObligationAmounts to include Appropriation details and PO amount
        $appropriations = $obligationAmounts->map(function ($obligationAmount) {
            $appropriation = $obligationAmount->appropriation;
            $poAmount = PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)->sum('po_amount');
            $adjustmentSum = ObligationAdjustment::where('obligation_amounts_id', $obligationAmount->id)->sum('adjustment_amount');
            return [
                'program' => $appropriation->programs ?? '',
                'account_code' => $appropriation->account_code ?? '',
                'description' => $appropriation->description ?? '',
                'obr_amount' => ($obligationAmount->obr_amount ?? 0) + $adjustmentSum,
                'po_amount' => $poAmount,
            ];
        });

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Obligations', 'route' => route('obligations.index')],
            ['label' => 'Purchase Orders']
        ];

        return view('purchase_orders.index', [
            'obligation' => $obligation,
            'officeAllotmentClass' => $officeAllotmentClass,
            'purchase_orders' => $purchase_orders,
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
        return view('purchase_orders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    // Validate the request data
    $validated = $request->validate([
        'obligation_id' => 'required|exists:obligations,id',
        'po_date' => 'required|date',
        'po_number' => 'required|string|max:255',
        'pr_no' => 'required|string|max:255',
        'po_remarks' => 'nullable|string|max:255',
        'supplier' => 'required|string|max:255',
        'delivery_period' => 'nullable|string|max:255',
        'po_amount' => 'required|array',
        'po_amount.*' => 'nullable|numeric|min:0',
    ]);

    $savedPOs = 0;
    $accountCodes = [];

    try {
        foreach ($validated['po_amount'] as $obligationAmountId => $poAmount) {
            if (is_null($poAmount) || $poAmount === '') {
                continue; // Skip empty inputs
            }

            $obligationAmount = ObligationAmount::find($obligationAmountId);
            if (!$obligationAmount) {
                continue; // Invalid reference
            }

            $existingPO = PurchaseOrder::where('obligation_amounts_id', $obligationAmountId)->sum('po_amount');

            if ($poAmount <= 0) {
                continue; // Skip zero or negative amounts
            }

            PurchaseOrder::create([
                'obligation_id' => $validated['obligation_id'],
                'obligation_amounts_id' => $obligationAmountId,
                'po_number' => $validated['po_number'],
                'pr_no' => $validated['pr_no'],
                'po_date' => $validated['po_date'],
                'po_remarks' => $validated['po_remarks'],
                'supplier' => $validated['supplier'],
                'delivery_period' => $validated['delivery_period'],
                'po_amount' => $poAmount,
            ]);

            $savedPOs++;

            $appropriation = $obligationAmount->appropriation;
            if ($appropriation && !in_array($appropriation->account_code, $accountCodes)) {
                $accountCodes[] = $appropriation->account_code;
            }
        }
    } catch (\Exception $e) {
        Log::error('Error saving purchase order:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()->back()
            ->withInput()
            ->with('error', 'An error occurred while saving the purchase order: ' . $e->getMessage());
    }

    if ($savedPOs === 0) {
        return redirect()->back()->with('error', 'No valid purchase orders were saved.');
    }

    $accountCodesMessage = count($accountCodes) > 1 ? implode(', ', $accountCodes) : ($accountCodes[0] ?? 'N/A');

    return redirect()->route('purchase_orders.index', ['obligation_id' => $validated['obligation_id']])
        ->with('status', [
            'type' => 'default',
            'message' => "Purchase Order No: <strong>{$validated['po_number']}</strong> with Date: <strong>{$validated['po_date']}</strong> under Account Code(s): <strong>{$accountCodesMessage}</strong> has been created successfully!"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
        {
            $validated = $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'edit_po_date' => 'required|date',
                'edit_po_number' => 'required|string|max:255',
                'edit_pr_no' => 'required|string|max:255',
                'edit_delivery_period' => 'required|string|max:255',
                'edit_supplier' => 'required|string|max:255',
                'edit_po_remarks' => 'nullable|string|max:1000',
                'edit_po_amount' => 'required|array',
                'edit_po_amount.*' => 'nullable|numeric|min:0',
            ]);

            $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);
            $obligationId = $purchaseOrder->obligation_amounts_id;

            // Get PO amount specifically for this obligation
            $poAmount = $validated['edit_po_amount'][$obligationId] ?? 0;

            $purchaseOrder->update([
                'po_date' => $validated['edit_po_date'],
                'po_number' => $validated['edit_po_number'],
                'pr_no' => $validated['edit_pr_no'],
                'delivery_period' => $validated['edit_delivery_period'],
                'supplier' => $validated['edit_supplier'],
                'po_remarks' => $validated['edit_po_remarks'],
                'po_amount' => $poAmount,
            ]);

            $accountCodesMessage = optional($purchaseOrder->obligationAmount->appropriation)->account_code ?? 'N/A';

            return redirect()->route('purchase_orders.index', [
                'obligation_id' => $purchaseOrder->obligation_id,
            ])->with('status', [
                'type' => 'update',
                'message' => "Purchase Order No: <strong>{$purchaseOrder->po_number}</strong> has been <strong>updated</strong> under <strong>Account Code:</strong> {$accountCodesMessage}."
            ]);
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        $accountCode = optional(optional($purchaseOrder->obligationAmount)->appropriation)->account_code ?? 'N/A';

        return redirect()->route('purchase_orders.index', [
            'obligation_id' => $purchaseOrder->obligation_id,
        ])->with('status', [
            'type' => 'delete',
            'message' => "Purchase Order No: <strong>{$purchaseOrder->po_number}</strong> dated <strong>{$purchaseOrder->po_date}</strong> under Account Code: <strong>{$accountCode}</strong> has been <strong>deleted</strong>!"
        ]);
    }
}