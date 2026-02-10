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
use App\Models\ActivityLog;
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
        $searchColumn = $request->input('search_column', '');
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
        if ($request->filled('fund_filter')) {
            $query->whereHas('officeAllotmentClass', function ($q) use ($request) {
                $q->where('fund', $request->fund_filter);
            });
        }
        if ($request->filled('obr_type_filter')) {
            $query->where('obr_type', $request->obr_type_filter);
        }
        if ($search) {
            if ($searchColumn) {
                // Search in specific column
                switch ($searchColumn) {
                    case 'obr_date':
                        $query->where('obr_date', 'like', "%{$search}%");
                        break;
                    case 'obr_no':
                        $query->where('obr_no', 'like', "%{$search}%");
                        break;
                    case 'obr_type':
                        $query->where('obr_type', 'like', "%{$search}%");
                        break;
                    case 'particulars':
                        $query->where('particulars', 'like', "%{$search}%");
                        break;
                    case 'office_abbreviation':
                        $query->whereHas('officeAllotmentClass.offices', fn($q) => 
                            $q->where('office_abbreviation', 'like', "%{$search}%"));
                        break;
                    case 'allotment_class':
                        $query->whereHas('officeAllotmentClass.allotmentClass', fn($q) => 
                            $q->where('class', 'like', "%{$search}%"));
                        break;
                    case 'remarks':
                        $query->where('remarks', 'like', "%{$search}%");
                        break;
                    case 'processed_by':
                        $query->where('processed_by', 'like', "%{$search}%");
                        break;
                    default:
                        // General search if column not recognized
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
            } else {
                // General search across all columns
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
                'search_column' => $searchColumn,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'office_allotment_class_filter' => $request->office_allotment_class_filter,
                'fund_filter' => $request->fund_filter,
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

        // Get total count of obligations based on filters
        $totalRecords = Obligation::with([
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass'
        ])->whereHas('officeAllotmentClass', function ($q) use ($selectedYear) {
            $q->where('year', $selectedYear);
        })
        ->when($request->filled('office_allotment_class_filter'), function ($q) use ($request) {
            return $q->where('office_allotment_class_id', $request->office_allotment_class_filter);
        })
        ->when($request->filled('fund_filter'), function ($q) use ($request) {
            return $q->whereHas('officeAllotmentClass', function ($subQ) use ($request) {
                $subQ->where('fund', $request->fund_filter);
            });
        })
        ->when($request->filled('obr_type_filter'), function ($q) use ($request) {
            return $q->where('obr_type', $request->obr_type_filter);
        })
        ->when($search, function ($q) use ($search, $searchColumn) {
            if ($searchColumn) {
                switch ($searchColumn) {
                    case 'obr_date':
                        return $q->where('obr_date', 'like', "%{$search}%");
                    case 'obr_no':
                        return $q->where('obr_no', 'like', "%{$search}%");
                    case 'obr_type':
                        return $q->where('obr_type', 'like', "%{$search}%");
                    case 'particulars':
                        return $q->where('particulars', 'like', "%{$search}%");
                    case 'office_abbreviation':
                        return $q->whereHas('officeAllotmentClass.offices', fn($subQ) => 
                            $subQ->where('office_abbreviation', 'like', "%{$search}%"));
                    case 'allotment_class':
                        return $q->whereHas('officeAllotmentClass.allotmentClass', fn($subQ) => 
                            $subQ->where('class', 'like', "%{$search}%"));
                    case 'remarks':
                        return $q->where('remarks', 'like', "%{$search}%");
                    case 'processed_by':
                        return $q->where('processed_by', 'like', "%{$search}%");
                    default:
                        return $q->where(function ($subQ) use ($search) {
                            $subQ->where('obr_date', 'like', "%{$search}%")
                                ->orWhere('obr_no', 'like', "%{$search}%")
                                ->orWhere('obr_type', 'like', "%{$search}%")
                                ->orWhere('particulars', 'like', "%{$search}%")
                                ->orWhere('processed_by', 'like', "%{$search}%")
                                ->orWhere('remarks', 'like', "%{$search}%");
                        })
                        ->orWhereHas('officeAllotmentClass.offices', fn($subQ) => 
                            $subQ->where('office_abbreviation', 'like', "%{$search}%"))
                        ->orWhereHas('officeAllotmentClass.allotmentClass', fn($subQ) => 
                            $subQ->where('class', 'like', "%{$search}%"));
                }
            } else {
                return $q->where(function ($subQ) use ($search) {
                    $subQ->where('obr_date', 'like', "%{$search}%")
                        ->orWhere('obr_no', 'like', "%{$search}%")
                        ->orWhere('obr_type', 'like', "%{$search}%")
                        ->orWhere('particulars', 'like', "%{$search}%")
                        ->orWhere('processed_by', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                })
                ->orWhereHas('officeAllotmentClass.offices', fn($subQ) => 
                    $subQ->where('office_abbreviation', 'like', "%{$search}%"))
                ->orWhereHas('officeAllotmentClass.allotmentClass', fn($subQ) => 
                    $subQ->where('class', 'like', "%{$search}%"));
            }
        })
        ->count();

        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->orderBy('office', 'asc')
            ->get();

        $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->where('year', $selectedYear)
            ->get();

        // Get all unique funds that have obligations in the selected year
        $funds = OfficeAllotmentClass::join('obligations', 'office_allotment_classes.id', '=', 'obligations.office_allotment_class_id')
            ->where('office_allotment_classes.year', $selectedYear)
            ->distinct()
            ->pluck('office_allotment_classes.fund')
            ->filter()
            ->unique()
            ->values();

        $obligations_check = Obligation::select('obr_no')
        ->whereHas('officeAllotmentClass', function($q) use ($selectedYear) {
            $q->where('year', $selectedYear);
        })
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
            'obligations_check',
            'breadcrumb',
            'funds',
            'totalRecords'
        ));
    }

    public function show(Obligation $obligation)
    {
        // Eager load related models in a single query
        $obligation->load([
            'obligationAmounts.obligationAdjustments',
            'obligationAmounts.appropriation.realignments',
            'obligationAmounts.appropriation.supplementals',
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
            
            // Calculate balance using the same formula as the index method
            $totalAppropriation = collect([
                $appropriation->quarter1 ?? 0,
                $appropriation->quarter2 ?? 0,
                $appropriation->quarter3 ?? 0,
                $appropriation->quarter4 ?? 0,
            ])->sum();
            
            // Get realignments and supplementals
            $realignmentTotal = ($appropriation->realignments ?? collect())->sum(function ($r) {
                return $r->type === 'Recipient' ? $r->amount : ($r->type === 'Source' ? -$r->amount : 0);
            });
            
            $supplementalTotal = ($appropriation->supplementals ?? collect())->sum(function ($s) {
                return $s->type === 'Supplemental' ? $s->amount : ($s->type === 'Reversion' ? -$s->amount : 0);
            });
            
            // Get total obligation amount for this appropriation
            $totalObrAmount = $appropriation->obligationAmounts->sum(function ($oa) {
                return $oa->obr_amount + $oa->obligationAdjustments->sum('adjustment_amount');
            });
            
            // Calculate current balance
            $balance = ($totalAppropriation + $realignmentTotal + $supplementalTotal) - $totalObrAmount;
            
            // Balance from allotment shows what it was before this obligation
            $balanceFromAllotment = $balance + $obrAmount;

            return [
                'account_code' => $amount->account_code,
                'description' => $appropriation->description ?? '',
                'programs' => $appropriation->programs ?? '',
                'obr_amount' => $obrAmount,
                'adjustments' => $adjustments,
                'po_total' => $poTotal,
                'disbursement_total' => $disbursementTotal,
                'balance' => $obrAmount - $disbursementTotal,
                'balance_from_allotment' => $balanceFromAllotment,
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
                'office_allotment_class_id' => $obligation->office_allotment_class_id,
                'obr_date' => $obligation->obr_date,
                'obr_no' => $obligation->obr_no,
                'obr_type' => $obligation->obr_type,
                'office' => optional($obligation->officeAllotmentClass->offices)->office_abbreviation ?? '',
                'allotment_class' => optional($obligation->officeAllotmentClass->allotmentClass)->class ?? '',
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
                'preselected_class' => 'nullable|boolean',
                'preselected_appropriation_id' => 'nullable|integer|exists:appropriations,id',
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
                $preselectedAccountCode = null;

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

                        // Store the first account code for redirection
                        if ($index === 0 && isset($validated['preselected_appropriation_id']) && 
                            $appropriation->id == $validated['preselected_appropriation_id']) {
                            $preselectedAccountCode = $accountCode;
                        }
                    }
                } // <-- This closing brace was missing!

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

                $statusMessage = [
                    'type' => 'create',
                    'message' => "Obligation Request No. <strong>{$validated['obr_no']}</strong> under <strong>{$officeAbbreviation}</strong> - <strong>{$class}</strong> with Total Amount: <strong>" . number_format($totalObrAmount, 2, '.', ',') . "</strong> has been created successfully!"
                ];

                // Check if this was a preselected class from dashboard or accounts
                if ($request->has('preselected_class') && $request->input('preselected_class') == '1') {
                    // Check if coming from accounts page (has preselected appropriation)
                    $fromAccountsPage = $request->has('preselected_appropriation_id') && !empty($request->input('preselected_appropriation_id'));
                    
                    if ($fromAccountsPage) {
                        // Redirect back to accounts page with modal reopen
                        return redirect()
                            ->route('dashboard.accounts', $validated['office_allotment_class_id'])
                            ->with('status', $statusMessage['message'])
                            ->with('reopen_modal', true)
                            ->with('preselected_class_id', $validated['office_allotment_class_id'])
                            ->with('preselected_appropriation_id', $request->input('preselected_appropriation_id'))
                            ->with('preselected_account_code', $preselectedAccountCode)
                            ->with($request->only(['search']));
                    } else {
                        // Redirect back to dashboard with modal reopen
                        return redirect()
                            ->route('dashboard', $request->only(['year1', 'office_filter', 'allotment_class_filter', 'group_filter', 'fund_type_filter', 'fund_filter', 'search', 'sort_by', 'sort_order']))
                            ->with('status', $statusMessage)
                            ->with('reopen_modal', true)
                            ->with('preselected_class_id', $validated['office_allotment_class_id']);
                    }
                } else {
                    // If not preselected, redirect to obligations index
                    return redirect()
                        ->route('obligations.index', $request->only(['search', 'search_column', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter', 'fund_filter']))
                        ->with('status', $statusMessage);
                }
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
            // Debug: Log incoming request data
            Log::info('Update obligation request:', [
                'from_dashboard' => $request->input('from_dashboard'),
                'obligation_id' => $obligation->id
            ]);
            
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

            // Check if this edit came from the dashboard modal
            // This check MUST happen after the successful update
            if ($request->input('from_dashboard') == 1) {
                Log::info('Redirecting to dashboard after update', [
                    'from_dashboard' => $request->input('from_dashboard'),
                    'obligation_id' => $obligation->id
                ]);
                
                return redirect()->route('dashboard', $request->only([
                    'search', 'sort_by', 'sort_order', 'per_page', 'year1', 
                    'group_filter', 'fund_type_filter', 'fund_filter', 
                    'office_filter', 'allotment_class_filter'
                ]))->with('status', [
                    'type' => 'update',
                    'message' => "Obligation Request No. <strong>{$validated['edit_obr_no']}</strong> under <strong>{$officeAbbreviation}</strong> - <strong>{$class}</strong> has been updated successfully!"
                ]);
            }

            // Check if this edit came from the accounts modal
            if ($request->input('from_accounts') == 1) {
                Log::info('Redirecting to accounts after update', [
                    'from_accounts' => $request->input('from_accounts'),
                    'obligation_id' => $obligation->id
                ]);
                
                $accountsClassId = $request->input('accounts_class_id');
                return redirect()->route('dashboard.accounts', $accountsClassId)
                    ->with('status', [
                        'type' => 'update',
                        'message' => "Obligation Request No. <strong>{$validated['edit_obr_no']}</strong> under <strong>{$officeAbbreviation}</strong> - <strong>{$class}</strong> has been updated successfully!"
                    ])
                    ->with($request->only(['search']));
            }

            return redirect()->route('obligations.index', $request->only(['search', 'search_column', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter', 'fund_filter']))
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
                ->with($request->only(['search', 'sort_by', 'sort_order', 'per_page', 'year1', 'office_allotment_class_filter', 'obr_type_filter', 'fund_filter']) );
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
                    'search', 'search_column', 'sort_by', 'sort_order', 'per_page', 'year1',
                    'office_allotment_class_filter', 'obr_type_filter', 'fund_filter'
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
                'search', 'search_column', 'sort_by', 'sort_order', 'per_page', 'year1', 
                'office_allotment_class_filter', 'obr_type_filter', 'fund_filter'
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

            // Log the cancellation action
            Obligation::logObligationCancellation($obligation, $validated['remarks']);

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
                'search', 'search_column', 'sort_by', 'sort_order', 'per_page', 'year1', 
                'office_allotment_class_filter', 'obr_type_filter', 'fund_filter'
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
     * Get activity history for a specific obligation
     */
    public function activityHistory($obligationId): JsonResponse
    {
        try {
            // Find the obligation first
            $obligation = Obligation::findOrFail($obligationId);
            
            // Get all purchase order numbers related to this obligation
            $purchaseOrders = PurchaseOrder::where('obligation_id', $obligationId)->get();
            $poNumbers = $purchaseOrders->pluck('po_number')->toArray();
            
            // Escape special regex characters in OBR number
            $escapedObrNo = preg_quote($obligation->obr_no, '/');
            
            // Fetch activity logs related to this obligation and its purchase orders
            // We'll search for:
            // 1. Activities where details contains the obligation_id
            // 2. Activities where description mentions the exact obligation ID or OBR number
            // 3. Activities related to purchase orders for this obligation
            
            $activities = ActivityLog::with('user')
                ->where(function($query) use ($obligationId, $obligation, $escapedObrNo, $poNumbers) {
                    // Obligation-related activities
                    $query->where(function($obligationQuery) use ($obligationId, $obligation, $escapedObrNo) {
                        // Check if details JSON contains obligation_id
                        $obligationQuery->whereRaw("JSON_EXTRACT(details, '$.obligation_id') = ?", [$obligationId])
                            // Or description mentions "Obligation #ID" (exact ID match)
                            ->orWhere('description', 'like', "%Obligation #{$obligationId} %")
                            ->orWhere('description', 'like', "%Obligation #{$obligationId}:%")
                            ->orWhere('description', 'like', "%Obligation #{$obligationId}-%")
                            // Or description mentions the exact OBR number with word boundaries
                            // Match "OBR# {number}" or "OBR: {number}" or just the number surrounded by spaces/punctuation
                            ->orWhere('description', 'REGEXP', "OBR[#:]?[[:space:]]*{$escapedObrNo}([[:space:]]|\$|,|\\.|;)")
                            // Or match the OBR number at the start of the description
                            ->orWhere('description', 'like', "{$obligation->obr_no} %")
                            ->orWhere('description', 'like', "{$obligation->obr_no}:%")
                            ->orWhere('description', 'like', "{$obligation->obr_no}-%")
                            // Or description mentions obligation-related actions with exact ID in details
                            ->orWhere(function($q) use ($obligationId) {
                                $q->where('description', 'like', "%obligation%")
                                  ->whereRaw("JSON_EXTRACT(details, '$.id') = ?", [$obligationId]);
                            });
                    });
                    
                    // Add purchase order related activities
                    if (!empty($poNumbers)) {
                        $query->orWhere(function($poQuery) use ($poNumbers, $obligationId) {
                            foreach ($poNumbers as $poNumber) {
                                $poQuery->orWhere(function($q) use ($poNumber, $obligationId) {
                                    // Match PO number in description AND obligation_id in JSON details
                                    $q->where(function($subQ) use ($poNumber, $obligationId) {
                                        $subQ->where('description', 'like', "%{$poNumber}%")
                                          ->whereRaw("JSON_EXTRACT(details, '$.obligation_id') = ?", [$obligationId]);
                                    })
                                      // Or check details JSON for both po_number and obligation_id
                                      ->orWhere(function($subQ) use ($poNumber, $obligationId) {
                                          $subQ->whereRaw("JSON_EXTRACT(details, '$.po_number') = ?", [$poNumber])
                                            ->whereRaw("JSON_EXTRACT(details, '$.obligation_id') = ?", [$obligationId]);
                                      })
                                      // Or check for PO# format with obligation_id
                                      ->orWhere(function($subQ) use ($poNumber, $obligationId) {
                                          $subQ->where('description', 'like', "%PO#{$poNumber}%")
                                            ->whereRaw("JSON_EXTRACT(details, '$.obligation_id') = ?", [$obligationId]);
                                      });
                                });
                            }
                        });
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching obligation activity history', [
                'obligation_id' => $obligationId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created purchase order for an obligation (used by modal).
     */
    public function storePurchaseOrder(Request $request, Obligation $obligation)
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

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the purchase order: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the purchase order: ' . $e->getMessage());
        }

        if ($savedPOs === 0) {
            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid purchase orders were saved.'
                ], 400);
            }

            return redirect()->back()->with('error', 'No valid purchase orders were saved.');
        }

        $accountCodesMessage = count($accountCodes) > 1 ? implode(', ', $accountCodes) : ($accountCodes[0] ?? 'N/A');
        $successMessage = "Purchase Order No: {$validated['po_number']} with Date: {$validated['po_date']} under Account Code(s): {$accountCodesMessage} has been created successfully!";

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => [
                    'po_number' => $validated['po_number'],
                    'po_date' => $validated['po_date'],
                    'account_codes' => $accountCodesMessage,
                    'saved_count' => $savedPOs
                ]
            ]);
        }

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
        
        // Get the query parameters
        $from = $request->query('from', 'obligation');
        $purchaseOrderId = $request->query('purchase_order_id');
        
        // Fetch the specific purchase order if provided
        $purchaseOrder = null;
        if ($from === 'purchase_order' && $purchaseOrderId) {
            $purchaseOrder = \App\Models\PurchaseOrder::find($purchaseOrderId);
        }
        
        // Filter obligation amounts based on context
        if ($from === 'purchase_order' && $purchaseOrderId && $purchaseOrder) {
            // Get all purchase orders with the same po_number
            $poNumbersToInclude = \App\Models\PurchaseOrder::where('po_number', $purchaseOrder->po_number)
                ->pluck('obligation_amounts_id')
                ->toArray();
            
            // Show obligation amounts related to all purchase orders with the same po_number
            $obligationAmounts = $obligation->obligationAmounts()
                ->whereIn('id', $poNumbersToInclude)
                ->get();
        } else {
            // Show all obligation amounts
            $obligationAmounts = $obligation->obligationAmounts;
        }

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
            'balanceFromObligations',
            'from',
            'purchaseOrder'
        ));
    }

    public function storeDisbursement(Request $request, Obligation $obligation) : RedirectResponse
    {
        // Validate the request data
        $validated = $request->validate([
            'obligation_id' => 'required|exists:obligations,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'disbursement_date' => 'required|date',
            'dv_no' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'disbursement_amount' => 'required|array',
            'disbursement_amount.*' => 'nullable|numeric|min:0',
        ]);

        $savedDVs = 0;
        $accountCodes = [];
        $totalDisbursementAmount = 0;
        $dvNumbers = [];

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

                // Generate unique DV number for each account (transaction)
                $baseDVNo = $validated['dv_no'];
                $suffix = 1;
                $uniqueDVNo = $baseDVNo;
                
                // Check if this DV number already exists for this obligation
                while (Disbursement::where('obligation_id', $validated['obligation_id'])
                    ->where('dv_no', $uniqueDVNo)
                    ->where('obligation_amounts_id', $obligationAmountId)
                    ->exists()) {
                    $uniqueDVNo = $baseDVNo . '-' . $suffix;
                    $suffix++;
                }

                Disbursement::create([
                    'obligation_id' => $validated['obligation_id'],
                    'obligation_amounts_id' => $obligationAmountId,
                    'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                    'dv_no' => $uniqueDVNo,
                    'disbursement_date' => $validated['disbursement_date'],
                    'remarks' => $validated['remarks'],
                    'status' => $validated['status'],
                    'disbursement_amount' => $dvAmount,
                ]);

                $savedDVs++;
                $totalDisbursementAmount += $dvAmount;
                if (!in_array($uniqueDVNo, $dvNumbers)) {
                    $dvNumbers[] = $uniqueDVNo;
                }

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
        $dvNumbersMessage = implode(', ', $dvNumbers);
        $formattedAmount = number_format($totalDisbursementAmount, 2);
        $obrNo = $obligation->obr_no ?? 'N/A';

        return redirect()->to(url()->previous())
            ->with('status', [
                'type' => 'default',
                'message' => "DV / Check No(s): <strong>{$dvNumbersMessage}</strong> for OBR No. <strong>{$obrNo}</strong> with DV / Check Date: <strong>{$validated['disbursement_date']}</strong> under Account Code(s): <strong>{$accountCodesMessage}</strong> with Total Amount: <strong>₱{$formattedAmount}</strong> has been created successfully!"
            ]);
        }

    public function updatePaymentRemarks(Request $request, Obligation $obligation)
    {
        $request->validate([
            'payment_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $obligation->update([
                'payment_remarks' => $request->payment_remarks,
            ]);

            return redirect()->route('obligations.index', $request->only(['year1', 'office_allotment_class_filter', 'obr_type_filter', 'per_page', 'search', 'search_column', 'sort_by', 'sort_order', 'fund_filter']))
                ->with('status', [
                    'type' => 'update',
                    'message' => '<strong>Payment remarks</strong> for OBR No. <strong>' . $obligation->obr_no . '</strong> has been updated successfully!'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update payment remarks: ' . $e->getMessage());
        }
    }

    /**
     * Get obligations by office allotment class ID (API endpoint)
     */
    public function getByOfficeAllotmentClass($classId): JsonResponse
    {
        try {
            // Fetch office allotment class with relationships
            $officeAllotmentClass = OfficeAllotmentClass::with([
                'offices',
                'allotmentClass'
            ])->findOrFail($classId);

            $obligations = Obligation::with([
                'officeAllotmentClass.offices',
                'officeAllotmentClass.allotmentClass',
                'obligationAmounts.appropriation',
                'obligationAmounts.obligationAdjustments',
                'obligationAmounts.purchaseOrders',
                'obligationAdjustments',
                'purchaseOrders',
                'disbursements'
            ])->where('office_allotment_class_id', $classId)
                ->orderBy('obr_date', 'asc')
                ->get();

            // Transform obligations data
            $obligationsData = $obligations->map(function ($obligation) {
                // Calculate total amount from obligation_amounts
                $obligationAmountsTotal = $obligation->obligationAmounts->sum('obr_amount') ?? 0;
                
                // Calculate total adjustments from obligation_adjustments
                $adjustmentsTotal = $obligation->obligationAdjustments->sum('adjustment_amount') ?? 0;
                
                // Total amount is obligation amounts + adjustments
                $totalAmount = $obligationAmountsTotal + $adjustmentsTotal;
                
                // Get total Disbursement amount
                $disbursementAmount = $obligation->disbursements->sum('disbursement_amount') ?? 0;
                
                // Get total PO amount if obr_type is "Purchase Request"
                $poAmount = '-';
                if ($obligation->obr_type === 'Purchase Request' && $obligation->purchaseOrders->count() > 0) {
                    $totalPoAmount = $obligation->purchaseOrders->sum('po_amount');
                    $poAmount = number_format($totalPoAmount, 2);
                }
                
                // Get appropriations from obligation_amounts with related adjustments and purchase orders
                $appropriations = $obligation->obligationAmounts->map(function ($obrAmount) {
                    // Get adjustment amount for this obligation_amount
                    $adjustmentAmount = $obrAmount->obligationAdjustments->sum('adjustment_amount') ?? 0;
                    $originalAmount = $obrAmount->obr_amount ?? 0;
                    $adjustedAmount = $originalAmount + $adjustmentAmount;
                    
                    // Get PO amount for this obligation_amount
                    $poAmount = $obrAmount->purchaseOrders->sum('po_amount') ?? 0;
                    
                    // Get disbursement amount for this obligation_amount
                    $disbursementAmountPerOA = \App\Models\Disbursement::where('obligation_amounts_id', $obrAmount->id)->sum('disbursement_amount') ?? 0;
                    
                    return [
                        'id' => $obrAmount->appropriation->id ?? null,
                        'programs' => $obrAmount->appropriation->programs ?? '-',
                        'code' => $obrAmount->appropriation->account_code ?? '-',
                        'description' => $obrAmount->appropriation->description ?? '-',
                        'amount' => number_format($originalAmount, 2) ?? '0.00',
                        'adjustment_amount' => number_format($adjustmentAmount, 2) ?? '0.00',
                        'adjusted_amount' => number_format($adjustedAmount, 2) ?? '0.00',
                        'purchase_order_amount' => $poAmount > 0 ? number_format($poAmount, 2) : '-',
                        'disbursement_amount' => $disbursementAmountPerOA > 0 ? number_format($disbursementAmountPerOA, 2) : '-',
                    ];
                })->toArray();
                
                return [
                    'id' => $obligation->id,
                    'obr_no' => $obligation->obr_no,
                    'obr_date' => $obligation->obr_date ? \Carbon\Carbon::parse($obligation->obr_date)->format('M d, Y') : '-',
                    'obr_type' => $obligation->obr_type ?? '-',
                    'payee' => $obligation->particulars ?? '-',
                    'remarks' => $obligation->remarks ?? '-',
                    'amount' => number_format($totalAmount, 2) ?? '0.00',
                    'purchase_order' => $poAmount,
                    'disbursement' => $disbursementAmount > 0 ? number_format($disbursementAmount, 2) : '-',
                    'appropriations' => $appropriations,
                    'office' => $obligation->officeAllotmentClass->offices->office_abbreviation ?? '-',
                    'class' => $obligation->officeAllotmentClass->allotmentClass->class ?? '-',
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $obligationsData,
                'count' => count($obligationsData),
                'office' => $officeAllotmentClass->offices->office_abbreviation ?? '-',
                'allotmentClass' => $officeAllotmentClass->allotmentClass->class ?? '-',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'count' => 0,
                'message' => 'Error fetching obligations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get obligations by appropriation ID (API endpoint)
     */
    public function getByAppropriation($appropriationId): JsonResponse
    {
        try {
            $appropriation = Appropriation::findOrFail($appropriationId);

            $obligations = Obligation::whereHas('obligationAmounts', function ($query) use ($appropriationId) {
                $query->where('appropriation_id', $appropriationId);
            })->with([
                'obligationAmounts.appropriation',
                'obligationAmounts.obligationAdjustments',
                'obligationAmounts.purchaseOrders',
                'obligationAdjustments',
                'purchaseOrders',
                'disbursements'
            ])->orderBy('obr_date', 'asc')->get();

            // Transform obligations data
            $obligationsData = $obligations->map(function ($obligation) {
                // Calculate total amount from obligation_amounts
                $obligationAmountsTotal = $obligation->obligationAmounts->sum('obr_amount') ?? 0;
                
                // Calculate total adjustments from obligation_adjustments
                $adjustmentsTotal = $obligation->obligationAdjustments->sum('adjustment_amount') ?? 0;
                
                // Total amount is obligation amounts + adjustments
                $totalAmount = $obligationAmountsTotal + $adjustmentsTotal;
                
                // Get total PO amount if obr_type is "Purchase Request"
                $poAmount = '-';
                if ($obligation->obr_type === 'Purchase Request' && $obligation->purchaseOrders->count() > 0) {
                    $totalPoAmount = $obligation->purchaseOrders->sum('po_amount');
                    $poAmount = number_format($totalPoAmount, 2);
                }
                
                // Get total disbursement amount
                $disbursementAmount = $obligation->disbursements->sum('disbursement_amount') ?? 0;
                $disbursement = $disbursementAmount > 0 ? number_format($disbursementAmount, 2) : '-';
                
                // Get appropriations from obligation_amounts with related adjustments and purchase orders
                $appropriations = $obligation->obligationAmounts->map(function ($obrAmount) {
                    // Get adjustment amount for this obligation_amount
                    $adjustmentAmount = $obrAmount->obligationAdjustments->sum('adjustment_amount') ?? 0;
                    $originalAmount = $obrAmount->obr_amount ?? 0;
                    $adjustedAmount = $originalAmount + $adjustmentAmount;
                    
                    // Get PO amount for this obligation_amount
                    $poAmount = $obrAmount->purchaseOrders->sum('po_amount') ?? 0;
                    
                    // Get disbursement amount for this obligation_amount
                    $disbursementAmount = $obrAmount->disbursements->sum('disbursement_amount') ?? 0;
                    
                    return [
                        'id' => $obrAmount->appropriation->id ?? null,
                        'programs' => $obrAmount->appropriation->programs ?? '-',
                        'code' => $obrAmount->appropriation->account_code ?? '-',
                        'description' => $obrAmount->appropriation->description ?? '-',
                        'amount' => number_format($originalAmount, 2) ?? '0.00',
                        'adjustment_amount' => number_format($adjustmentAmount, 2) ?? '0.00',
                        'adjusted_amount' => number_format($adjustedAmount, 2) ?? '0.00',
                        'purchase_order_amount' => $poAmount > 0 ? number_format($poAmount, 2) : '-',
                        'disbursement_amount' => $disbursementAmount > 0 ? number_format($disbursementAmount, 2) : '-',
                    ];
                })->toArray();
                
                return [
                    'id' => $obligation->id,
                    'obr_no' => $obligation->obr_no,
                    'obr_date' => $obligation->obr_date ? \Carbon\Carbon::parse($obligation->obr_date)->format('M d, Y') : '-',
                    'obr_type' => $obligation->obr_type ?? '-',
                    'payee' => $obligation->particulars ?? '-',
                    'remarks' => $obligation->remarks ?? '-',
                    'amount' => number_format($totalAmount, 2) ?? '0.00',
                    'purchase_order' => $poAmount,
                    'disbursement' => $disbursement,
                    'appropriations' => $appropriations,
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $obligationsData,
                'count' => count($obligationsData),
                'appropriation' => $appropriation->description ?? '-',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'count' => 0,
                'message' => 'Error fetching obligations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get obligation details with amounts, adjustments, PO, and disbursements for the details panel
     */
    public function getObligationDetails($obligationId): JsonResponse
    {
        try {
            $obligation = Obligation::with([
                'obligationAmounts.appropriation',
                'obligationAmounts.obligationAdjustments',
                'obligationAmounts.purchaseOrders',
                'obligationAmounts.disbursements',
                'obligationAdjustments',
                'purchaseOrders',
                'disbursements'
            ])->findOrFail($obligationId);

            // Map obligation amounts with related data
            $amounts = $obligation->obligationAmounts->map(function ($obrAmount) {
                $originalObligation = $obrAmount->obr_amount ?? 0;
                $adjustment = $obrAmount->obligationAdjustments->sum('adjustment_amount') ?? 0;
                $adjustedObligation = $originalObligation + $adjustment;
                $poAmount = $obrAmount->purchaseOrders->sum('po_amount') ?? 0;
                $disbursementAmount = $obrAmount->disbursements->sum('disbursement_amount') ?? 0;

                return [
                    'id' => $obrAmount->id,
                    'account_code' => $obrAmount->appropriation->account_code ?? '-',
                    'description' => $obrAmount->appropriation->description ?? '-',
                    'program' => $obrAmount->appropriation->programs ?? '-',
                    'amount' => $originalObligation,
                    'adjustment' => $adjustment,
                    'adjusted_obligation' => $adjustedObligation,
                    'po_amount' => $poAmount,
                    'disbursement_amount' => $disbursementAmount,
                ];
            })->toArray();

            return response()->json([
                'id' => $obligation->id,
                'obr_no' => $obligation->obr_no,
                'particulars' => $obligation->particulars,
                'obligation_amounts' => $amounts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch obligation details',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getObligationAmounts($obligationId): JsonResponse
    {
        try {
            $obligationAmounts = ObligationAmount::where('obligation_id', $obligationId)
                ->with('appropriation', 'obligationAdjustments')
                ->get();

            $amounts = $obligationAmounts->map(function ($obrAmount) {
                $adjustment = $obrAmount->obligationAdjustments->sum('adjustment_amount') ?? 0;

                return [
                    'id' => $obrAmount->id,
                    'obr_amount' => $obrAmount->obr_amount,
                    'adjustment_amount' => $adjustment,
                    'account_code' => $obrAmount->account_code ?? '-',
                    'appropriation' => [
                        'programs' => $obrAmount->appropriation->programs ?? '-',
                        'account_code' => $obrAmount->appropriation->account_code ?? '-',
                        'description' => $obrAmount->appropriation->description ?? '-',
                    ]
                ];
            })->toArray();

            return response()->json($amounts);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch obligation amounts',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the year of the obligation's office allotment class
     */
    public function getObligationYear($obligationId)
    {
        try {
            $obligation = Obligation::findOrFail($obligationId);
            $year = $obligation->officeAllotmentClass->year;
            
            return response()->json(['year' => $year]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch obligation year',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}

