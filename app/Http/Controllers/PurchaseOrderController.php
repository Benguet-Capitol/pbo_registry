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
     * Display all purchase orders in a single view with edit capability.
     */
    public function all(Request $request)
    {
        // Get the per page value from the request
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $searchColumn = $request->input('search_column', '');
        // Get the sort by and sort order values from the request
        $sortBy = $request->query('sort_by', 'obr_date');
        $sortOrder = $request->query('sort_order', 'desc');

        // Get the selected year or default to the current year
        $currentYear = date('Y');
        $selectedYear = $request->input('year1', $currentYear);

        $query = PurchaseOrder::with([
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
            if ($searchColumn) {
                // Search in specific column
                switch ($searchColumn) {
                    case 'po_number':
                        $query->where('po_number', 'like', '%' . $search . '%');
                        break;
                    case 'po_date':
                        $query->where('po_date', 'like', '%' . $search . '%');
                        break;
                    case 'pr_no':
                        $query->where('pr_no', 'like', '%' . $search . '%');
                        break;
                    case 'supplier':
                        $query->where('supplier', 'like', '%' . $search . '%');
                        break;
                    case 'delivery_period':
                        $query->where('delivery_period', 'like', '%' . $search . '%');
                        break;
                    case 'po_remarks':
                        $query->where('po_remarks', 'like', '%' . $search . '%');
                        break;
                    default:
                        // General search if column not recognized
                        $query->where(function ($q) use ($search) {
                            $q->where('po_number', 'like', '%' . $search . '%')
                              ->orWhere('pr_no', 'like', '%' . $search . '%')
                              ->orWhere('supplier', 'like', '%' . $search . '%')
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
            } else {
                // General search across all columns
                $query->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', '%' . $search . '%')
                      ->orWhere('pr_no', 'like', '%' . $search . '%')
                      ->orWhere('supplier', 'like', '%' . $search . '%')
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
        }

        // Apply sorting and pagination
        if ($sortBy && in_array($sortBy, ['po_date', 'po_number', 'pr_no', 'supplier', 'po_amount'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            // Default sorting by obr_date descending
            $query->orderBy('po_date', 'desc');
        }

        if ($perPage === 'all') {
            $purchaseOrders = $query->get();
            // Calculate disbursement amounts for each purchase order
            $purchaseOrders = $purchaseOrders->map(function ($purchaseOrder) {
                // Get all PO IDs with the same po_number
                $relatedPoIds = PurchaseOrder::where('po_number', $purchaseOrder->po_number)->pluck('id')->toArray();
                
                // Sum disbursements for this obligation_amounts_id from any related PO
                $disbursementAmount = Disbursement::where('obligation_amounts_id', $purchaseOrder->obligation_amounts_id)
                    ->whereIn('purchase_order_id', $relatedPoIds)
                    ->sum('disbursement_amount');
                $purchaseOrder->disbursement_amount = $disbursementAmount;
                return $purchaseOrder;
            });
        } else {
            $perPage = is_numeric($perPage) ? (int)$perPage : 10; // Default to 10 if invalid
            $purchaseOrders = $query->paginate($perPage)->appends($request->query());
            
            // Calculate disbursement amounts for paginated results
            $purchaseOrders->getCollection()->transform(function ($purchaseOrder) {
                // Get all PO IDs with the same po_number
                $relatedPoIds = PurchaseOrder::where('po_number', $purchaseOrder->po_number)->pluck('id')->toArray();
                
                // Sum disbursements for this obligation_amounts_id from any related PO
                $disbursementAmount = Disbursement::where('obligation_amounts_id', $purchaseOrder->obligation_amounts_id)
                    ->whereIn('purchase_order_id', $relatedPoIds)
                    ->sum('disbursement_amount');
                $purchaseOrder->disbursement_amount = $disbursementAmount;
                return $purchaseOrder;
            });
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
            ['label' => 'Purchase Orders']
        ];

        // Calculate total records (unique po_number values)
        $totalRecordsQuery = PurchaseOrder::with([
                'obligation.obligationAmounts.appropriation',
                'obligation.officeAllotmentClass.offices',
                'obligation.officeAllotmentClass.allotmentClass',
                ])
            ->whereHas('obligation.officeAllotmentClass', function ($q) use ($selectedYear) {
                $q->where('year', $selectedYear);
            });

        if ($request->filled('office_allotment_class_filter')) {
            $totalRecordsQuery->whereHas('obligation.officeAllotmentClass', function ($q) use ($request) {
                $q->where('id', $request->input('office_allotment_class_filter'));
            });
        }

        if ($search) {
            if ($searchColumn) {
                switch ($searchColumn) {
                    case 'po_number':
                        $totalRecordsQuery->where('po_number', 'like', '%' . $search . '%');
                        break;
                    case 'po_date':
                        $totalRecordsQuery->where('po_date', 'like', '%' . $search . '%');
                        break;
                    case 'pr_no':
                        $totalRecordsQuery->where('pr_no', 'like', '%' . $search . '%');
                        break;
                    case 'supplier':
                        $totalRecordsQuery->where('supplier', 'like', '%' . $search . '%');
                        break;
                    case 'delivery_period':
                        $totalRecordsQuery->where('delivery_period', 'like', '%' . $search . '%');
                        break;
                    case 'po_remarks':
                        $totalRecordsQuery->where('po_remarks', 'like', '%' . $search . '%');
                        break;
                    default:
                        $totalRecordsQuery->where(function ($q) use ($search) {
                            $q->where('po_number', 'like', '%' . $search . '%')
                              ->orWhere('pr_no', 'like', '%' . $search . '%')
                              ->orWhere('supplier', 'like', '%' . $search . '%')
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
            } else {
                $totalRecordsQuery->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', '%' . $search . '%')
                      ->orWhere('pr_no', 'like', '%' . $search . '%')
                      ->orWhere('supplier', 'like', '%' . $search . '%')
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
        }

        $totalRecords = $totalRecordsQuery->distinct('po_number')->count('po_number');

        return view('purchase_orders.index_all', compact('purchaseOrders', 'breadcrumb', 'availableYears', 'selectedYear', 'perPage', 'search', 'sortBy', 'sortOrder', 'officeAllotmentClasses', 'office_allotment_classes', 'totalRecords'));
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
                'redirect' => 'nullable|string',
            ]);

            $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);
            $obligationId = $purchaseOrder->obligation_amounts_id;
            $originalPoNumber = $purchaseOrder->po_number;

            // Get PO amount specifically for this obligation
            $poAmount = $validated['edit_po_amount'][$obligationId] ?? 0;

            // Prepare common data that applies to all related POs
            $commonData = [
                'po_date' => $validated['edit_po_date'],
                'po_number' => $validated['edit_po_number'],
                'pr_no' => $validated['edit_pr_no'],
                'delivery_period' => $validated['edit_delivery_period'],
                'supplier' => $validated['edit_supplier'],
                'po_remarks' => $validated['edit_po_remarks'],
            ];

            // Update the current purchase order with all fields including po_amount
            $purchaseOrder->update(array_merge($commonData, ['po_amount' => $poAmount]));

            // Update all related purchase orders with the same ORIGINAL po_number (except the current one)
            // This ensures that if po_number is changed, all related POs get the new po_number too
            PurchaseOrder::where('po_number', $originalPoNumber)
                ->where('id', '!=', $purchaseOrder->id)
                ->update($commonData);

            $accountCodesMessage = optional($purchaseOrder->obligationAmount->appropriation)->account_code ?? 'N/A';

            // Check if redirect to all purchase orders is requested
            if (($validated['redirect'] ?? null) === 'all') {
                return redirect()->route('purchase_orders.all')->with('status', [
                    'type' => 'update',
                    'message' => "Purchase Order No: <strong>{$purchaseOrder->po_number}</strong> has been <strong>updated</strong> under <strong>Account Code:</strong> {$accountCodesMessage}."
                ]);
            }

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
        try {
            // Get the obligation_amounts_id from the purchase order
            $obligationAmountId = $purchaseOrder->obligation_amounts_id;

            // Check for existing disbursements linked to this obligation amount
            $disbursementsCount = Disbursement::where('obligation_id', $purchaseOrder->obligation_id)
                ->where('obligation_amounts_id', $obligationAmountId)
                ->count();

            // Prevent deletion if there are related disbursements
            if ($disbursementsCount > 0) {
                return redirect()->route('purchase_orders.index', [
                    'obligation_id' => $purchaseOrder->obligation_id,
                ])->with('status', [
                    'type' => 'delete',
                    'message' => "Cannot delete Purchase Order No: <strong>{$purchaseOrder->po_number}</strong>. This purchase order has <strong>{$disbursementsCount}</strong> disbursement(s) associated with the same obligation and account code. Please delete the related disbursements first."
                ]);
            }

            /* // Check for existing disbursements with non-zero amounts
            $disbursementsWithAmount = Disbursement::where('obligation_id', $purchaseOrder->obligation_id)
                ->where('obligation_amounts_id', $obligationAmountId)
                ->where('disbursement_amount', '>', 0)
                ->count();

            if ($disbursementsWithAmount > 0) {
                return redirect()->route('purchase_orders.index', [
                    'obligation_id' => $purchaseOrder->obligation_id,
                ])->with('status', [
                    'type' => 'error',
                    'message' => "Cannot delete Purchase Order No: <strong>{$purchaseOrder->po_number}</strong>. This purchase order has <strong>{$disbursementsWithAmount}</strong> disbursement(s) with non-zero amounts linked to the same account code. Please set all disbursement amounts to zero or delete the related disbursements first."
                ]);
            } */

            // Store details before deletion
            $poNumber = $purchaseOrder->po_number;
            $poDate = $purchaseOrder->po_date;
            $obligationId = $purchaseOrder->obligation_id;
            $accountCode = optional(optional($purchaseOrder->obligationAmount)->appropriation)->account_code ?? 'N/A';

            // Proceed with deletion
            $purchaseOrder->delete();

            return redirect()->route('purchase_orders.index', [
                'obligation_id' => $obligationId,
            ])->with('status', [
                'type' => 'delete',
                'message' => "Purchase Order No: <strong>{$poNumber}</strong> dated <strong>{$poDate}</strong> under Account Code: <strong>{$accountCode}</strong> has been <strong>deleted</strong>!"
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting purchase order:', [
                'purchase_order_id' => $purchaseOrder->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('purchase_orders.index', [
                'obligation_id' => $purchaseOrder->obligation_id,
            ])->with('error', 'Failed to delete purchase order. Please try again.');
        }
    }

    /**
     * Get all purchase orders by po_number
     */
    public function getByPoNumber($poNumber)
    {
        $purchaseOrders = PurchaseOrder::where('po_number', $poNumber)->get();
        return response()->json($purchaseOrders);
    }

    /**
     * Check if PO number is unique per year of officeAllotmentClass
     */
    public function checkPoNumber(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:2999',
        ]);

        $exists = PurchaseOrder::whereHas('obligation.officeAllotmentClass', function ($query) use ($validated) {
            $query->where('year', $validated['year']);
        })
        ->where('po_number', $validated['po_number'])
        ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? "PO Number '<strong>{$validated['po_number']}</strong>' is already used in the year <strong>{$validated['year']}</strong>." : ''
        ]);
    }
}