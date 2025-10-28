<?php

namespace App\Http\Controllers;

use App\Models\Appropriation;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use App\Models\ObligationAmount;
use App\Models\Office;
use App\Models\OfficeAllotmentClass;
use App\Models\PurchaseOrder;
use App\Models\Realignment;
use App\Models\Supplemental;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;


class ObligationController extends Controller
{
    public function index(Request $request)
    {
        // --- Filters & Sorting setup ---
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');
        $sortBy = $request->query('sort_by', 'obr_date');
        $sortOrder = $request->query('sort_order', 'desc');
        $currentYear = date('Y');
        $selectedYear = $request->input('year1', $currentYear);

        // --- Base Query for Obligations ---
        $query = Obligation::with([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'purchaseOrders',
            'obligationAmounts.appropriation',
            'obligationAmounts.obligationAdjustments',
            'disbursements',
            'obligationAdjustments'
        ])->whereHas('officeAllotmentClass', function ($q) use ($selectedYear) {
            $q->where('year', $selectedYear);
        });

        // --- Filters ---
        if ($request->filled('office_allotment_class_filter')) {
            $query->where('office_allotment_class_id', $request->office_allotment_class_filter);
        }
        if ($request->filled('obr_type_filter')) {
            $query->where('obr_type', $request->obr_type_filter);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('obr_date', 'like', "%{$search}%")
                    ->orWhere('obr_no', 'like', "%{$search}%")
                    ->orWhere('obr_type', 'like', "%{$search}%")
                    ->orWhere('particulars', 'like', "%{$search}%")
                    ->orWhere('processed_by', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            })
            ->orWhereHas('officeAllotmentClass.offices', fn($q) => 
                $q->where('office_abbreviation', 'like', "%{$search}%"))
            ->orWhereHas('officeAllotmentClass.allotmentClass', fn($q) => 
                $q->where('class', 'like', "%{$search}%"));
        }

        // --- Sorting ---
        if ($sortBy === 'office_allotment_class') {
            $query->join('office_allotment_classes', 'obligations.office_allotment_class_id', '=', 'office_allotment_classes.id')
                ->join('offices', 'offices.id', '=', 'office_allotment_classes.office')
                ->join('allotment_classes', 'allotment_classes.class', '=', 'office_allotment_classes.class')
                ->orderBy(DB::raw("CONCAT(offices.office_abbreviation, ' - ', allotment_classes.class)"), $sortOrder)
                ->select('obligations.*');
    } elseif ($sortBy === 'obr_amount') {
            $query->withSum('obligationAmounts as obr_amount_sum', 'obr_amount')
                ->orderBy('obr_amount_sum', $sortOrder);
        } elseif ($sortBy === 'po_amount') {
            $query->withSum('purchaseOrders as po_amount_sum', 'po_amount')
                ->orderBy('po_amount_sum', $sortOrder);
        } elseif ($sortBy === 'dv_amount') {
            $query->withSum('disbursements as dv_amount_sum', 'disbursement_amount')
                ->orderBy('dv_amount_sum', $sortOrder);
        } elseif ($sortBy === 'balance') {
            $query->withSum('obligationAmounts as obr_amount_sum', 'obr_amount')
                ->withSum('disbursements as dv_amount_sum', 'disbursement_amount')
                ->orderByRaw('(COALESCE(obr_amount_sum, 0) - COALESCE(dv_amount_sum, 0)) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $query->orderBy('obr_date', 'desc');

        // --- Pagination ---
        $obligations = $perPage == 'all'
            ? $query->get()
            : $query->paginate($perPage)->appends([
                'year1' => $selectedYear,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'office_allotment_class_filter' => $request->office_allotment_class_filter,
                'obr_type_filter' => $request->obr_type_filter,
                'per_page' => $perPage,
            ]);

        // --- Preload Appropriations and related data ---
        $appropriations = Appropriation::with([
            'obligationAmounts.obligationAdjustments',
            'realignments',
            'supplementals'
        ])->get();

        $currentMonth = now()->month;
        $currentQuarter = ceil($currentMonth / 3);

        // Precompute balances for appropriations (no per-loop queries)
        $appropriations->each(function ($appropriation) use ($currentQuarter) {
            $totalAppropriation = collect([
                $appropriation->quarter1,
                $appropriation->quarter2,
                $appropriation->quarter3,
                $appropriation->quarter4
            ])->take($currentQuarter)->sum();

            $totalObrAmount = $appropriation->obligationAmounts->sum(function ($oa) {
                return $oa->obr_amount + $oa->obligationAdjustments->sum('adjustment_amount');
            });

            $realignmentTotal = $appropriation->realignments->sum(function ($r) {
                return $r->type === 'Recipient' ? $r->amount : ($r->type === 'Source' ? -$r->amount : 0);
            });

            $supplementalTotal = $appropriation->supplementals->sum(function ($s) {
                return $s->type === 'Supplemental' ? $s->amount : ($s->type === 'Reversion' ? -$s->amount : 0);
            });

            $appropriation->balance = ($totalAppropriation + $realignmentTotal + $supplementalTotal) - $totalObrAmount;
        });

        // Build appropriation map for O(1) lookup instead of O(n) searches
        $appropriationMap = $appropriations->keyBy('id');

        // --- Compute obligation values (single pass, in-memory) ---
        $obligations->each(function ($obligation) use ($appropriationMap) {
            // Calculate total obligation amount with adjustments
            $obrAmount = $obligation->obligationAmounts->sum('obr_amount');
            $adjustmentAmount = $obligation->obligationAdjustments->sum('adjustment_amount');
            $obligation->obr_amount = $obrAmount + $adjustmentAmount;

            // Add these fields directly to the obligation object
            $obligation->office_abbreviation = $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A';
            $obligation->allotment_class = $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A';

            // Transform obligation_amounts in a single pass with precomputed data
            $transformedAmounts = $obligation->obligationAmounts->map(function ($amount) use ($appropriationMap) {
                $relatedAppropriation = $appropriationMap->get($amount->appropriation_id);
                $balance = $relatedAppropriation ? $relatedAppropriation->balance : 0;
                
                // Calculate adjustments for this specific obligation amount
                $adjustmentSum = $amount->obligationAdjustments->sum('adjustment_amount');
                $totalObrAmount = $amount->obr_amount + $adjustmentSum;

                // Return as stdClass for proper JSON serialization
                return (object)[
                    'id' => $amount->id,
                    'appropriation_id' => $amount->appropriation_id,
                    'obligation_id' => $amount->obligation_id,
                    'account_code' => $amount->account_code ?? '',
                    'obr_amount' => $totalObrAmount,
                    'balance_from_allotment' => $balance + $totalObrAmount,
                    'description' => $amount->appropriation->description ?? '',
                    'program' => $amount->appropriation->programs ?? '',
                    'appropriation' => (object)[
                        'id' => $amount->appropriation->id ?? null,
                        'description' => $amount->appropriation->description ?? '',
                        'programs' => $amount->appropriation->programs ?? '',
                    ],
                    'created_at' => $amount->created_at,
                    'updated_at' => $amount->updated_at,
                ];
            });

            // Replace the relationship with computed data
            $obligation->setRelation('obligation_amounts', $transformedAmounts);

            // Prepare data for cancellation modal
            $obligation->obligation_data = json_encode([
                'obr_date' => $obligation->obr_date,
                'office_abbreviation' => $obligation->office_abbreviation,
                'allotment_class' => $obligation->allotment_class,
                'obr_no' => $obligation->obr_no,
                'obr_type' => $obligation->obr_type,
                'particulars' => $obligation->particulars,
                'obr_amount' => $obligation->obr_amount,
            ]);
        });

        // --- Other variables ---
        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->orderBy('office', 'asc')
            ->get();

        $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->where('year', $currentYear)
            ->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Obligations']
        ];

        // --- Return view ---
        return view('obligations.index', compact(
            'obligations',
            'officeAllotmentClasses',
            'appropriations',
            'perPage',
            'search',
            'sortBy',
            'sortOrder',
            'availableYears',
            'selectedYear',
            'office_allotment_classes',
            'breadcrumb'
        ));
    }

    public function show(Obligation $obligation)
    {
        // Eager load related models in a single query
        $obligation->load([
            'obligationAmounts.obligationAdjustments',
            'obligationAmounts.appropriation',
            'obligationAmounts.purchaseOrders',
            'obligationAmounts.disbursements',
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'obligationAdjustments.obligationAmount.appropriation',
        ]);

        // Prepare obligation amounts with summarized data
        $obligationAmounts = $obligation->obligationAmounts->map(function ($amount) {
            $obrAmount = $amount->obr_amount ?? 0;
            $adjustments = $amount->obligationAdjustments->sum('adjustment_amount');
            $poTotal = $amount->purchaseOrders->sum('po_amount');
            $disbursementTotal = $amount->disbursements->sum('disbursement_amount');
            $appropriation = $amount->appropriation;

            return [
                'account_code' => $amount->account_code,
                'description' => $appropriation->description ?? '',
                'programs' => $appropriation->programs ?? '',
                'obr_amount' => $obrAmount,
                'adjustments' => $adjustments,
                'po_total' => $poTotal,
                'disbursement_total' => $disbursementTotal,
                'balance' => $obrAmount - $disbursementTotal,
            ];
        });

        // Prepare obligation adjustment records
        $obligationAdjustments = $obligation->obligationAdjustments->map(function ($adjustment) {
            $appropriation = optional($adjustment->obligationAmount->appropriation);

            return [
                'adjustment_date' => $adjustment->adjustment_date,
                'programs' => $appropriation->programs ?? '',
                'account_code' => $appropriation->account_code ?? '',
                'description' => $appropriation->description ?? '',
                'adjustment_amount' => $adjustment->adjustment_amount,
                'remarks' => $adjustment->adjustment_remarks,
                'adjusted_by' => $adjustment->adjusted_by,
            ];
        })->sortBy([
            ['account_code', 'asc'],
            ['adjustment_date', 'asc'],
        ])->values(); // reset keys after sorting

        // Calculate total obligation amount (OBR + Adjustments)
        $totalObligationAmount = $obligationAmounts->sum(fn($item) => $item['obr_amount'] + $item['adjustments']);
        $totalPOAmount = $obligationAmounts->sum('po_total');

        // Prepare disbursements for the modal
        $disbursements = $obligation->disbursements->map(function ($disb) {
            $appropriation = optional(optional($disb->obligationAmount)->appropriation);
            return [
                'dv_no' => $disb->dv_no,
                'disbursement_date' => $disb->disbursement_date,
                'status' => $disb->status,
                'programs' => $appropriation->programs ?? '-',
                'account_code' => $appropriation->account_code ?? '-',
                'description' => $appropriation->description ?? '-',
                'disbursement_amount' => $disb->disbursement_amount,
            ];
        });
        $totalDisbursementAmount = $disbursements->sum('disbursement_amount');

        return response()->json([
            'obligation' => [
                'obr_date' => $obligation->obr_date,
                'obr_no' => $obligation->obr_no,
                'obr_type' => $obligation->obr_type,
                'office' => optional($obligation->officeAllotmentClass->offices)->office_name ?? '',
                'allotment_class' => optional($obligation->officeAllotmentClass->allotmentClass)->description ?? '',
                'particulars' => $obligation->particulars,
                'remarks' => $obligation->remarks,
                'processed_by' => $obligation->processed_by,
            ],
            'obligation_amounts' => $obligationAmounts,
            'obligation_adjustments' => $obligationAdjustments,
            'total_obligation_amount' => $totalObligationAmount,
            'total_po_amount' => $totalPOAmount,
            'purchase_orders' => $obligation->purchaseOrders->map(function ($po) {
                $appropriation = optional(optional($po->obligationAmount)->appropriation);
                return [
                    'po_number' => $po->po_number,
                    'po_date' => $po->po_date,
                    'supplier' => $po->supplier,
                    'pr_no' => $po->pr_no,
                    'delivery_period' => $po->delivery_period,
                    'po_amount' => $po->po_amount,
                    'programs' => $appropriation->programs ?? '',
                    'account_code' => $appropriation->account_code ?? '',
                    'description' => $appropriation->description ?? '',
                ];
            }),
            'disbursements' => $disbursements,
            'total_disbursement_amount' => $totalDisbursementAmount,
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'office_allotment_class_id' => 'required|integer|exists:office_allotment_classes,id',
                'obr_date' => 'required|date',
                'obr_no' => 'required|string|max:255|unique:obligations,obr_no',
                'obr_type' => 'required|string|max:255',
                'particulars' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:255',
                'account_code' => 'required|array',
                'account_code.*' => 'required|string|exists:appropriations,account_code',
                'amount_of_obligation' => 'required|array',
                'amount_of_obligation.*' => 'required|numeric|min:0.01',
            ]);

            // Start a database transaction
            DB::beginTransaction();

            try {
                // Create the obligation without triggering events
                $obligation = Obligation::withoutEvents(function () use ($validated) {
                    return Obligation::create([
                        'office_allotment_class_id' => $validated['office_allotment_class_id'],
                        'obr_date' => $validated['obr_date'],
                        'obr_no' => $validated['obr_no'],
                        'obr_type' => $validated['obr_type'],
                        'particulars' => $validated['particulars'],
                        'remarks' => $validated['remarks'],
                        'processed_by' => Auth::user()->name ?? 'Unknown User',
                    ]);
                });

                // Save ObligationAmount records
                $totalObrAmount = 0;
                foreach ($validated['account_code'] as $index => $accountCode) {
                    // Fetch the appropriation ID based on the account code and office_allotment_class_id
                    $appropriation = Appropriation::where('account_code', $accountCode)
                        ->where('office_allotment_class_id', $validated['office_allotment_class_id'])
                        ->first();

                    if ($appropriation) {
                        $obrAmount = $validated['amount_of_obligation'][$index];
                        ObligationAmount::create([
                            'appropriation_id' => $appropriation->id,
                            'obligation_id' => $obligation->id,
                            'account_code' => $accountCode,
                            'obr_amount' => $obrAmount,
                        ]);
                        $totalObrAmount += $obrAmount;
                    }
                }

                // Refresh the obligation to include the ObligationAmounts
                $obligation->refresh();
                
                // Now trigger the created event manually
                event('eloquent.created: ' . get_class($obligation), [$obligation]);

                DB::commit();

                // Fetch related office abbreviation and class
                $officeAllotmentClass = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
                    ->find($validated['office_allotment_class_id']);

                $officeAbbreviation = $officeAllotmentClass->offices->office_abbreviation ?? 'N/A';
                $class = $officeAllotmentClass->allotmentClass->class ?? 'N/A';

                return redirect()
                    ->route('obligations.index', $request->only(['search', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter']))
                    ->with('status', "Obligation Request No. <strong>{$validated['obr_no']}</strong> under <strong>{$officeAbbreviation}</strong> - <strong>{$class}</strong> with Total Amount: <strong>" . number_format($totalObrAmount, 2, '.', ',') . "</strong> has been created successfully!");
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Log the error for debugging
                Log::error('Error storing obligation:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Redirect back with input and an error message
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'An error occurred while saving the obligation: ' . $e->getMessage())
                    ->with($request->only(['search', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter']));
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in validation:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect back with input and an error message
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while validating the obligation: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing an obligation
     */
    public function edit($obligation_id)
    {
        $currentYear = date('Y');

        // Eager load all related data for the modal
        $obligation = Obligation::with([
            'obligationAmounts.appropriation',
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass'
        ])->findOrFail($obligation_id);

        // Prepare obligation_amounts for the modal table
        $obligation_amounts = $obligation->obligationAmounts->map(function ($amount) {
            return [
                'account_code' => $amount->account_code,
                'description' => $amount->appropriation->description ?? '',
                'program' => $amount->appropriation->programs ?? '',
                'obr_amount' => $amount->obr_amount,
                'amount' => $amount->obr_amount, // If you have a separate 'amount' field, use it
            ];
        });

        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])->get();
        $office_allotment_classes = DB::table('office_allotment_classes')
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->where('year', $currentYear)
            ->get();

        $appropriations = Appropriation::select(
            'id',
            'office_allotment_class_id',
            'account_code',
            'description',
            'programs',
            'quarter1',
            'quarter2',
            'quarter3',
            'quarter4'
        )->get()->map(function ($appropriation) {
            // Calculate the total appropriation
            $totalAppropriation = $appropriation->quarter1 + $appropriation->quarter2 + $appropriation->quarter3 + $appropriation->quarter4;

            // Get all obligation amounts for this appropriation
            $obligationAmounts = ObligationAmount::where('appropriation_id', $appropriation->id)->get();
            $totalObrAmount = 0;
            foreach ($obligationAmounts as $obr) {
                // Sum adjustments for this obligation amount
                $totalObrAmount += $obr->obr_amount;
            }

            // Calculate the balance
            $appropriation->balance = $totalAppropriation;

            return $appropriation;
        });

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Obligations', 'route' => route('obligations.index')],
            ['label' => 'Edit Obligation']
        ];

        // Pass the prepared $obligation_amounts to the view
        return view('obligations.edit', compact(
            'obligation',
            'obligation_amounts',
            'officeAllotmentClasses',
            'office_allotment_classes',
            'appropriations',
            'breadcrumb'
        ));
    }
    public function update(Request $request, Obligation $obligation): RedirectResponse
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'edit_office_allotment_class_id' => 'required|integer|exists:office_allotment_classes,id',
                'edit_obr_date' => 'required|date',
                'edit_obr_no' => 'required|string|max:255',
                'edit_obr_type' => 'required|string|max:255',
                'edit_particulars' => 'required|string|max:255',
                'edit_remarks' => 'nullable|string|max:255',
                'edit_account_code' => 'required|array',
                'edit_account_code.*' => 'required|string|exists:appropriations,account_code',
                'edit_amount_of_obligation' => 'required|array',
                'edit_amount_of_obligation.*' => 'required|numeric|min:0',
            ]);

            // Start database transaction
            DB::beginTransaction();
            
            try {
                // Update the obligation
                $obligation->fill([
                    'office_allotment_class_id' => $validated['edit_office_allotment_class_id'],
                    'obr_date' => $validated['edit_obr_date'],
                    'obr_no' => $validated['edit_obr_no'],
                    'obr_type' => $validated['edit_obr_type'],
                    'particulars' => $validated['edit_particulars'],
                    'remarks' => $validated['edit_remarks'],
                    'processed_by' => Auth::user()->name ?? 'Unknown User',
                ]);
                
                $obligation->save();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

            // Delete existing ObligationAmount records for this obligation
            ObligationAmount::where('obligation_id', $obligation->id)->delete();

            // Save updated ObligationAmount records
            foreach ($validated['edit_account_code'] as $index => $accountCode) {
                // Fetch the appropriation ID based on the account code and office_allotment_class_id
                $appropriation = Appropriation::where('account_code', $accountCode)
                    ->where('office_allotment_class_id', $validated['edit_office_allotment_class_id'])
                    ->first();

                if ($appropriation) {
                    ObligationAmount::create([
                        'appropriation_id' => $appropriation->id,
                        'obligation_id' => $obligation->id,
                        'account_code' => $accountCode,
                        'obr_amount' => $validated['edit_amount_of_obligation'][$index],
                    ]);
                }
            }

            // Fetch related office abbreviation and class
            $officeAllotmentClass = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
                ->find($validated['edit_office_allotment_class_id']);

            $officeAbbreviation = $officeAllotmentClass->offices->office_abbreviation ?? 'N/A';
            $class = $officeAllotmentClass->allotmentClass->class ?? 'N/A';

            return redirect()->route('obligations.index', $request->only(['search', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter']))
                ->with('status', [
                    'type' => 'update',
                    'message' => "Obligation Request No. <strong>{$validated['edit_obr_no']}</strong> under <strong>{$officeAbbreviation}</strong> - <strong>{$class}</strong> has been updated successfully!"
                ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error updating obligation:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect back with input and an error message
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the obligation: ' . $e->getMessage())
                ->with($request->only(['search', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter']) );
        }
    }

    public function destroy(Request $request, Obligation $obligation): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Eager load related models to minimize queries
            $obligation->load([
                'officeAllotmentClass.offices',
                'officeAllotmentClass.allotmentClass',
                'obligationAmounts',
                'purchaseOrders',
                'disbursements'
            ]);

            $obrNumber = $obligation->obr_no;
            $account_code = $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A';
            $class = $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A';

            // --- Check for related records before deletion ---
            $hasAdjustments = ObligationAdjustment::where('obligation_id', $obligation->id)->exists();
            $hasPurchaseOrders = $obligation->purchaseOrders->isNotEmpty();
            $hasDisbursements = $obligation->disbursements->isNotEmpty();

            if ($hasAdjustments || $hasPurchaseOrders || $hasDisbursements) {
                $errorMessages = [];

                if ($hasAdjustments) {
                    $errorMessages[] = "This obligation has related <strong>Obligation Adjustments</strong>.</br>";
                }
                if ($hasPurchaseOrders) {
                    $errorMessages[] = "This obligation has related <strong>Purchase Orders</strong>.</br>";
                }
                if ($hasDisbursements) {
                    $errorMessages[] = "This obligation has related <strong>Disbursements</strong>.</br>";
                }

                $errorMessages[] = "Please ensure that there are no related records for <strong>Obligation Adjustments, Purchase Orders and Disbursements</strong> before deleting this obligation.";

                DB::rollBack();

                return redirect()->back()->with('status', [
                    'type' => 'delete',
                    'message' => implode(' ', $errorMessages)
                ]);
            }

            // --- Safe to delete ---
            $totalObrAmount = $obligation->obligationAmounts->sum('obr_amount');
            $obligation->delete();

            DB::commit();

            return redirect()
                ->route('obligations.index', $request->only([
                    'search', 'sort_by', 'sort_order', 'per_page', 'year1',
                    'office_allotment_class_filter', 'obr_type_filter'
                ]))
                ->with('status', [
                    'type' => 'delete',
                    'message' => "Obligation Request No. <strong>{$obrNumber}</strong> under <strong>{$account_code}</strong> - <strong>{$class}</strong> with Total Amount: <strong>" . number_format($totalObrAmount, 2, '.', ',') . "</strong> has been deleted successfully!"
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting obligation:', [
                'obligation_id' => $obligation->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('obligations.index', $request->only([
                'search', 'sort_by', 'sort_order', 'per_page', 'year1', 
                'office_allotment_class_filter', 'obr_type_filter'
            ]))->with('error', 'Failed to delete obligation. Please try again.');
        }
    }

    public function cancel(Request $request, Obligation $obligation): RedirectResponse
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'remarks' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            // Eager load relationships to avoid N+1 queries
            $obligation->load([
                'purchaseOrders',
                'disbursements',
                'obligationAmounts',
                'officeAllotmentClass.offices',
                'officeAllotmentClass.allotmentClass'
            ]);

            // Check for existing purchase orders and disbursements with non-zero amounts (single query each)
            $purchaseOrdersWithAmount = $obligation->purchaseOrders->where('po_amount', '>', 0)->count();
            $disbursementsWithAmount = $obligation->disbursements->where('disbursement_amount', '>', 0)->count();

            // Prevent cancellation if there are related records with non-zero amounts
            if ($purchaseOrdersWithAmount > 0 || $disbursementsWithAmount > 0) {
                DB::rollBack();
                
                $errorMessages = [];
                
                if ($purchaseOrdersWithAmount > 0) {
                    $errorMessages[] = "This obligation has <strong>{$purchaseOrdersWithAmount}</strong> purchase order(s) with non-zero amounts.</br>";
                }
                
                if ($disbursementsWithAmount > 0) {
                    $errorMessages[] = "This obligation has <strong>{$disbursementsWithAmount}</strong> disbursement(s) with non-zero amounts.</br>";
                }
                
                $errorMessages[] = "Please ensure all purchase orders have zero PO amounts and all disbursements have zero disbursement amounts before cancelling this obligation.";
                
                return redirect()->back()
                    ->with('status', [
                        'type' => 'delete',
                        'message' => implode(' ', $errorMessages)
                    ]);
            }

            // Prepare cancellation data
            $currentDate = now()->format('Y-m-d');
            $userName = Auth::user()->name ?? 'Unknown User';

            // Bulk fetch existing adjustments (single query instead of N queries)
            $existingAdjustments = ObligationAdjustment::whereIn(
                'obligation_amounts_id',
                $obligation->obligationAmounts->pluck('id')
            )->get()->groupBy('obligation_amounts_id');

            // Prepare bulk insert data
            $adjustmentsToCreate = [];
            
            foreach ($obligation->obligationAmounts as $amount) {
                $existingAdjustmentSum = $existingAdjustments->get($amount->id)?->sum('adjustment_amount') ?? 0;
                $adjustmentAmount = -($amount->obr_amount + $existingAdjustmentSum);

                $adjustmentsToCreate[] = [
                    'obligation_id' => $obligation->id,
                    'obligation_amounts_id' => $amount->id,
                    'adjustment_date' => $currentDate,
                    'adjustment_amount' => $adjustmentAmount,
                    'adjustment_remarks' => $validated['remarks'],
                    'adjusted_by' => $userName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert all adjustments (single query instead of N queries)
            if (!empty($adjustmentsToCreate)) {
                ObligationAdjustment::insert($adjustmentsToCreate);
            }

            DB::commit();

            // Prepare success message data (already loaded from eager loading)
            $obrNumber = $obligation->obr_no;
            $account_code = $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A';
            $class = $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A';

            return redirect()->to(url()->previous())
                ->with('status', [
                    'type' => 'delete',
                    'message' => "Obligation Request No. <strong>{$obrNumber}</strong> under <strong>{$account_code}</strong> - <strong>{$class}</strong> has been cancelled successfully!"
                ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error cancelling obligation:', [
                'obligation_id' => $obligation->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('obligations.index', $request->only([
                'search', 'sort_by', 'sort_order', 'per_page', 'year1', 
                'office_allotment_class_id', 'obr_type_filter'
            ]))->with('error', 'Failed to cancel obligation. Please try again.');
        }
    }

    public function showPurchaseOrderModal(Request $request, Obligation $obligation)
    {
        $obligation->load([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'obligationAmounts.appropriation',
            'purchaseOrders'
        ]);
        $obligationAmounts = $obligation->obligationAmounts;

        // Calculate totals for the modal table footer
        $totalPOAmount = $obligation->purchaseOrders->sum('po_amount');
        $totalAdjustments = $obligation->obligationAmounts->reduce(function($carry, $item) {
            $sum = \App\Models\ObligationAdjustment::where('obligation_amounts_id', $item->id)->sum('adjustment_amount');
            return $carry + ($sum ?: 0);
        }, 0);
        $totalObligationAmount = ($obligation->obligationAmounts->sum('obr_amount') + $totalAdjustments);
        $balanceFromObligations = ($totalObligationAmount - $totalPOAmount) + $totalAdjustments;

        return view('obligations.modal.purchase_order', compact(
            'obligation',
            'obligationAmounts',
            'totalPOAmount',
            'totalObligationAmount',
            'totalAdjustments',
            'balanceFromObligations'
        ));
    }

    /**
     * Store a newly created purchase order for an obligation (used by modal).
     */
    public function storePurchaseOrder(Request $request, Obligation $obligation): RedirectResponse
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

        return redirect()->to(url()->previous())
            ->with('status', [
                'type' => 'default',
                'message' => "Purchase Order No: <strong>{$validated['po_number']}</strong> with Date: <strong>{$validated['po_date']}</strong> under Account Code(s): <strong>{$accountCodesMessage}</strong> has been created successfully!"
            ]);
    }

    public function showObligationAdjustmentModal(Request $request, $obligation_id)
    {
        $obligation = Obligation::with([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'obligationAmounts.appropriation',
            'obligationAmounts.obligationAdjustments',
        ])->findOrFail($obligation_id);

        // Prepare obligationAmounts with adjustedObrAmount
        $obligationAmounts = $obligation->obligationAmounts->map(function ($amount) {
            $adjustedObrAmount = $amount->obr_amount;
            if ($amount->obligationAdjustments && $amount->obligationAdjustments->isNotEmpty()) {
                $adjustedObrAmount += $amount->obligationAdjustments->sum('adjustment_amount');
            }
            $amount->adjustedObrAmount = $adjustedObrAmount;
            return $amount;
        });

        return view('obligations.modal.obligation_adjustment', compact(
            'obligation',
            'obligationAmounts'
        ));
    }

    public function storeObligationAdjustment(Request $request, $obligation): RedirectResponse
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

        // Get the OBR number for the success message
        $obr = Obligation::find($validated['obligation_id']);
        $obrNo = $obr ? $obr->obr_no : '';
        // Redirect back to the index page with a success message
        return redirect()->to(url()->previous())
            ->with('status', [
                'type' => 'default',
                'message' => "<strong>$adjustmentsSaved Obligation Adjustments</strong> for OBR No.: <strong>{$obrNo}</strong> has been created successfully. <strong>Details: Adjustment Date:</strong> {$validated['adjustment_date']} with <strong>Account Code(s):</strong> {$accountCodesMessage}"
            ]);
    }

    public function showDisbursementModal(Request $request, Obligation $obligation)
    {
        $obligation->load([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass',
            'obligationAmounts.appropriation',
            'purchaseOrders'
        ]);
        $obligationAmounts = $obligation->obligationAmounts;

        // Calculate totals for the modal table footer
        $totalDisbursementAmount = $obligation->disbursements->sum('disbursement_amount');
        $totalAdjustments = $obligation->obligationAmounts->reduce(function($carry, $item) {
            $sum = \App\Models\ObligationAdjustment::where('obligation_amounts_id', $item->id)->sum('adjustment_amount');
            return $carry + ($sum ?: 0);
        }, 0);
        $totalObligationAmount = ($obligation->obligationAmounts->sum('obr_amount') + $totalAdjustments);
        $balanceFromObligations = ($totalObligationAmount - $totalDisbursementAmount) + $totalAdjustments;

        return view('obligations.modal.disbursement', compact(
            'obligation',
            'obligationAmounts',
            'totalDisbursementAmount',
            'totalObligationAmount',
            'totalAdjustments',
            'balanceFromObligations'
        ));
    }

    public function storeDisbursement(Request $request, Obligation $obligation) : RedirectResponse
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

        return redirect()->to(url()->previous())
            ->with('status', [
                'type' => 'default',
                'message' => "DV / Check No: <strong>{$validated['dv_no']}</strong> with Date: <strong>{$validated['disbursement_date']}</strong> under Account Code(s): <strong>{$accountCodesMessage}</strong> has been created successfully!"
            ]);
        }

}
