<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\Office;
use App\Models\FundSource;
use App\Models\Fund;
use App\Models\AllotmentClass;
use App\Models\ObligationAdjustment;
use App\Models\Supplemental;
use App\Models\Realignment;
use App\Models\Disbursement;
use App\Models\Obligation;
use App\Models\PurchaseOrder;
use App\Traits\SortsAppropriations;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use SortsAppropriations;

    /**
     * Display the dashboard with all office allotment classes.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');
        $sortBy = $request->query('sort_by', 'office'); // Default to 'id'
        $sortOrder = $request->query('sort_order', 'asc'); // Default to 'desc'
        $currentYear = $request->input('year1', date('Y'));
        $groupFilter = $request->input('group_filter');
        $fundTypeFilter = $request->input('fund_type_filter');
        $fundFilter = $request->input('fund_filter');
        $officeFilter = $request->input('office_filter');
        $allotmentClassFilter = $request->input('allotment_class_filter');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Check if user is a Guest and automatically filter by their office
        $isGuest = auth()->user()->hasRole('Guest');
        $userOfficeId = auth()->user()->office;

        if ($isGuest && $userOfficeId) {
            // Force office filter for guests
            $officeFilter = $userOfficeId;
        }

        // Fetch all offices for group_filter (branch) dropdown
        $offices = Office::all();

        // For guests, only show their office in the office filter dropdown
        if ($isGuest && $userOfficeId) {
            $offices = Office::where('id', $userOfficeId)->get();
        }

        $branches = $offices->pluck('branch')->unique()->filter()->sort()->values();
        $fundTypes = FundSource::select('category')->orderBy('category')->pluck('category')->unique()->values();
        $funds = Fund::select('fund_type')->distinct()->pluck('fund_type')->filter()->unique()->sort()->values();
        $allotmentClasses = OfficeAllotmentClass::with('allotmentClass')
            ->select('class')
            ->distinct()
            ->get()
            ->unique('class')
            ->sortBy(function ($item) {
                return optional($item->allotmentClass)->id;
            })
            ->values();

        // Filter by selected year and other filters
        $officeAllotmentClassesQuery = OfficeAllotmentClass::with([
            'appropriations',
            'obligationAmounts' => function ($query) use ($fromDate, $toDate) {
                // Filter obligation amounts by obligation date
                if ($fromDate) {
                    $query->whereHas('obligation', function ($q) use ($fromDate) {
                        $q->where('obr_date', '>=', $fromDate);
                    });
                }
                if ($toDate) {
                    $query->whereHas('obligation', function ($q) use ($toDate) {
                        $q->where('obr_date', '<=', $toDate);
                    });
                }
            },
            'fundSourceRelation',
            'allotmentClass',
            'offices',
            'realignments' => function ($query) use ($fromDate, $toDate) {
                // Filter realignments by realignment_date
                if ($fromDate) {
                    $query->where('realignment_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $query->where('realignment_date', '<=', $toDate);
                }
            }
        ])->where('year', $currentYear);

        // Apply office filter for guests automatically
        if ($isGuest && $userOfficeId) {
            $officeAllotmentClassesQuery->where('office_allotment_classes.office', $userOfficeId);
        }
        if ($groupFilter) {
            $officeIds = $offices->where('branch', $groupFilter)->pluck('id');
            $officeAllotmentClassesQuery->whereIn('office_allotment_classes.office', $officeIds);
        }
        if ($fundTypeFilter) {
            $fundSourceCategory = FundSource::where('category', $fundTypeFilter)->pluck('source');
            $officeAllotmentClassesQuery->whereIn('office_allotment_classes.fund_source', $fundSourceCategory);
        }
        if ($fundFilter) {
            $fundType = Fund::where('fund_type', $fundFilter)->pluck('fund');
            $officeAllotmentClassesQuery->whereIn('office_allotment_classes.fund', $fundType);
        }
        
        // Check if selected office is a SEF office, if so get all SEF offices
        $isSEFConsolidated = false;
        if ($officeFilter && !$isGuest) {
            $selectedOfficeRecord = Office::find($officeFilter);
            if ($selectedOfficeRecord && $selectedOfficeRecord->fund === 'Special Education Fund') {
                // Get all SEF offices
                $officeIds = Office::where('fund', 'Special Education Fund')->pluck('id');
                $officeAllotmentClassesQuery->whereIn('office_allotment_classes.office', $officeIds);
                $isSEFConsolidated = true;
            } else {
                $officeAllotmentClassesQuery->where('office_allotment_classes.office', $officeFilter);
            }
        }
        
        if ($allotmentClassFilter) {
            $officeAllotmentClassesQuery->where('office_allotment_classes.class', $allotmentClassFilter);
        }
        if ($search) {
            $officeAllotmentClassesQuery->where(function ($q) use ($search) {
                $q->where('office_allotment_classes.fpp_code', 'like', "%$search%")
                    ->orWhere('office_allotment_classes.responsibility_code', 'like', "%$search%")
                    ->orWhere('office_allotment_classes.class', 'like', "%$search%")
                    ->orWhere('office_allotment_classes.fund', 'like', "%$search%")
                    ->orWhere('office_allotment_classes.fund_source', 'like', "%$search%")
                    ->orWhereHas('offices', function ($query) use ($search) {
                        $query->where('office_abbreviation', 'like', "%$search%")
                              ->orWhere('office_name', 'like', "%$search%");
                    });
            });
        }

        // Use pagination for the main table
        $query = $officeAllotmentClassesQuery;
        $selectedYear = $currentYear; // for appends
        $officeAllotmentClasses = $perPage == 'all'
            ? $query->orderBy('office_allotment_classes.office')->join('allotment_classes', 'office_allotment_classes.class', '=', 'allotment_classes.class')->orderBy('allotment_classes.id')->select('office_allotment_classes.*')->get()
            : $query->orderBy('office_allotment_classes.office')->join('allotment_classes', 'office_allotment_classes.class', '=', 'allotment_classes.class')->orderBy('allotment_classes.id')->select('office_allotment_classes.*')->paginate($perPage)->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'year1' => $selectedYear,
            ]);

        // Determine current quarter based on server date
        $month = Carbon::now()->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };


        // Calculate the sum of appropriations, allotments, obligations, supplementals, and reversions for each office allotment class
        foreach ($officeAllotmentClasses as $class) {
            // Approved Appropriations
            $class->appropriations_sum = $class->appropriations->sum('appropriation');
            // Supplementals
            $supplementalQuery = Supplemental::where('type', 'Supplemental')
                ->where('office_allotment_classes_id', $class->id);
            if ($fromDate) {
                $supplementalQuery->where('supplemental_date', '>=', $fromDate);
            }
            if ($toDate) {
                $supplementalQuery->where('supplemental_date', '<=', $toDate);
            }
            $class->supplemental_sum = $supplementalQuery->sum('amount');
            
            // Reversions
            $reversionQuery = Supplemental::where('type', 'Reversion')
                ->where('office_allotment_classes_id', $class->id);
            if ($fromDate) {
                $reversionQuery->where('supplemental_date', '>=', $fromDate);
            }
            if ($toDate) {
                $reversionQuery->where('supplemental_date', '<=', $toDate);
            }
            $class->reversion_sum = $reversionQuery->sum('amount');
            $class->realignments_sum = $class->realignments->sum(
                fn($realignment) => $realignment->type === 'Source'
                    ? -$realignment->amount
                    : $realignment->amount
            );
            // Allotments (add supplementals and subtract reversions)
            $class->allotments_sum = $class->appropriations->sum(function ($item) {
                return ($item->quarter1 ?? 0) + ($item->quarter2 ?? 0) + ($item->quarter3 ?? 0) + ($item->quarter4 ?? 0);
            });
            $class->allotments_sum += $class->supplemental_sum;
            $class->allotments_sum -= $class->reversion_sum;
            $class->allotments_sum += $class->realignments_sum;
            // Calculate obligations sum including adjustments
            $obligationAmountIds = $class->obligationAmounts->pluck('id');
            // Get the sum of obligation amounts
            $obrSum = $class->obligationAmounts->sum('obr_amount') ?? 0;
            // Get the sum of adjustments for the obligation amounts
            $adjustmentQuery = ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds);
            if ($fromDate) {
                $adjustmentQuery->where('adjustment_date', '>=', $fromDate);
            }
            if ($toDate) {
                $adjustmentQuery->where('adjustment_date', '<=', $toDate);
            }
            $adjustmentSum = $obligationAmountIds->isNotEmpty()
                ? $adjustmentQuery->sum('adjustment_amount')
                : 0;
            // Obligations
            $class->obligations_sum = $obrSum + $adjustmentSum;
            // Authorized Appropriations
            $class->authorized_appropriations = ($class->appropriations_sum + $class->supplemental_sum + $class->realignments_sum) - $class->reversion_sum;
            // Appropriation Accomplishment
            $class->appropriation_accomplishment = $class->authorized_appropriations > 0
                ? ($class->obligations_sum / $class->authorized_appropriations) * 100
                : 0;
            // For Later Release (per class)
            $forLater = 0;
            foreach ($class->appropriations as $app) {
                if ($currentQuarter < 2) $forLater += $app->quarter2 ?? 0;
                if ($currentQuarter < 3) $forLater += $app->quarter3 ?? 0;
                if ($currentQuarter < 4) $forLater += $app->quarter4 ?? 0;
            }
            $class->for_later_release = $forLater;

            $class->allotments_sum -= $class->for_later_release;

            // Balance Allotments
            $class->balance_allotments = $class->allotments_sum - $class->obligations_sum;
            // Allotment Accomplishment
            $class->allotment_accomplishment = $class->allotments_sum > 0
                ? ($class->obligations_sum / $class->allotments_sum) * 100
                : 0;
            // Balance Appropriations
            $class->balance_appropriations = $class->authorized_appropriations - $class->obligations_sum;

            // Disbursements
            $disbursementQuery = Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds);
            if ($fromDate) {
                $disbursementQuery->where('disbursement_date', '>=', $fromDate);
            }
            if ($toDate) {
                $disbursementQuery->where('disbursement_date', '<=', $toDate);
            }
            $class->disbursements_sum = $obligationAmountIds->isNotEmpty()
                ? $disbursementQuery->sum('disbursement_amount') : 0;

            // Disbursements / Obligation
            $class->disbursements_to_obligations = $class->obligations_sum > 0
                ? ($class->disbursements_sum / $class->obligations_sum) * 100
                : 0;

            // Disbursements / Authorized Appropriations
            $class->disbursements_to_appropriations = $class->authorized_appropriations > 0
                ? ($class->disbursements_sum / $class->authorized_appropriations) * 100
                : 0;

            // Disbursement Balance
            $class->disbursement_balance = $class->obligations_sum - $class->disbursements_sum;
        }

        // Only include appropriations for the filtered office allotment classes
        $appropriationIds = $officeAllotmentClasses->flatMap(function ($class) {
            return $class->appropriations->pluck('id');
        });

        // Calculate total appropriations for the card (filtered)
        $totalAppropriations = Appropriation::whereIn('id', $appropriationIds)->sum('appropriation');

        // Calculate total Supplementals and Reversions for the card (filtered)
        $totalSupplementalsQuery = Supplemental::where('type', 'Supplemental')
            ->whereIn('office_allotment_classes_id', $officeAllotmentClasses->pluck('id'));
        if ($fromDate) {
            $totalSupplementalsQuery->where('supplemental_date', '>=', $fromDate);
        }
        if ($toDate) {
            $totalSupplementalsQuery->where('supplemental_date', '<=', $toDate);
        }
        $totalSupplementals = $totalSupplementalsQuery->sum('amount');
        
        $totalReversionsQuery = Supplemental::where('type', 'Reversion')
            ->whereIn('office_allotment_classes_id', $officeAllotmentClasses->pluck('id'));
        if ($fromDate) {
            $totalReversionsQuery->where('supplemental_date', '>=', $fromDate);
        }
        if ($toDate) {
            $totalReversionsQuery->where('supplemental_date', '<=', $toDate);
        }
        $totalReversions = $totalReversionsQuery->sum('amount');

        $totalRealignmentsQuery = Realignment::whereIn('appropriations_id', $appropriationIds);
        if ($fromDate) {
            $totalRealignmentsQuery->where('realignment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $totalRealignmentsQuery->where('realignment_date', '<=', $toDate);
        }
        $totalRealignments = $totalRealignmentsQuery->get()
            ->sum(function ($realignment) {
                return $realignment->type === 'Source' ? -$realignment->amount : $realignment->amount;
            });

        // Calculate total allotments for the card (filtered)
        $totalAllotments = Appropriation::whereIn('id', $appropriationIds)->sum('quarter1') +
            Appropriation::whereIn('id', $appropriationIds)->sum('quarter2') +
            Appropriation::whereIn('id', $appropriationIds)->sum('quarter3') +
            Appropriation::whereIn('id', $appropriationIds)->sum('quarter4');
        $totalAllotments += $totalSupplementals;
        $totalAllotments += $totalRealignments;
        $totalAllotments -= $totalReversions;

        // Get all obligation amounts for appropriations in the filtered set, including adjustments
        $obligationAmountsIds = ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id');
        
        $totalObligationsQuery = ObligationAmount::whereIn('appropriation_id', $appropriationIds);
        if ($fromDate) {
            $totalObligationsQuery->whereHas('obligation', function ($q) use ($fromDate) {
                $q->where('obr_date', '>=', $fromDate);
            });
        }
        if ($toDate) {
            $totalObligationsQuery->whereHas('obligation', function ($q) use ($toDate) {
                $q->where('obr_date', '<=', $toDate);
            });
        }
        $totalObligations = $totalObligationsQuery->sum('obr_amount');
        
        $totalAdjustmentsQuery = ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountsIds);
        if ($fromDate) {
            $totalAdjustmentsQuery->where('adjustment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $totalAdjustmentsQuery->where('adjustment_date', '<=', $toDate);
        }
        $totalAdjustments = $totalAdjustmentsQuery->sum('adjustment_amount');
        $totalObligations += $totalAdjustments;

        // Total For Later Release (all classes)
        $totalForLaterRelease = 0;
        $appropriations = Appropriation::with([
            'obligationAmounts.obligationAdjustments',
            'realignments',
            'supplementals'
        ])->whereIn('id', $appropriationIds)->get();

        // Calculate balance for each appropriation
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

        foreach ($appropriations as $app) {
            if ($currentQuarter < 2) $totalForLaterRelease += $app->quarter2 ?? 0;
            if ($currentQuarter < 3) $totalForLaterRelease += $app->quarter3 ?? 0;
            if ($currentQuarter < 4) $totalForLaterRelease += $app->quarter4 ?? 0;
        }

        // Subtract For Later Release from Total Allotments
        $totalAllotments -= $totalForLaterRelease;

        // Calculate Allotment Balance (filtered)
        $allotmentBalance = $totalAllotments - $totalObligations;

        // Calculate Allotment Accomplishment percentage (filtered)
        $allotmentAccomplishment = $totalAllotments > 0 ? ($totalObligations / $totalAllotments) * 100 : 0;

        // Fetch distinct years from the database
        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // Calculate Authorized Appropriations
        $totalAuthorizedAppropriations = ($totalAppropriations + $totalSupplementals + $totalRealignments) - $totalReversions;

        // Calculate Authorized Appropriations Balance
        $totalAuthorizedAppropriationsBalance = $totalAuthorizedAppropriations - $totalObligations;

        // Calculate Authorized Appropriations Accomplishment
        $totalAuthorizedAppropriationsAccomplishment = $totalAuthorizedAppropriations > 0
            ? ($totalObligations / $totalAuthorizedAppropriations) * 100
            : 0;

        // Calculate Disbursement
        $totalDisbursementsQuery = Disbursement::whereIn(
            'obligation_amounts_id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        );
        if ($fromDate) {
            $totalDisbursementsQuery->where('disbursement_date', '>=', $fromDate);
        }
        if ($toDate) {
            $totalDisbursementsQuery->where('disbursement_date', '<=', $toDate);
        }
        $totalDisbursements = $totalDisbursementsQuery->sum('disbursement_amount');

        // Calculate Disbursements / Obligations
        $totalDisbursementsToObligations = $totalObligations > 0 ? ($totalDisbursements / $totalObligations) * 100 : 0;

        // Calculate Disbursements / Authorized Appropriations
        $totalDisbursementsToAppropriations = $totalAuthorizedAppropriations > 0 ? ($totalDisbursements / $totalAuthorizedAppropriations) * 100 : 0;

        // Calculate Disbursement Balance
        $disbursementBalance = $totalObligations - $totalDisbursements;

        // Prepare the selected year for the view
        if ($request->has('year1')) {
            $selectedYear = $request->input('year1');
        } else {
            $selectedYear = date('Y'); // Default to current year if not provided
        }
        // Prepare selected office and allotment class names for the view
        $selectedOfficeName = null;
        if ($officeFilter) {
            $selectedOffice = Office::find($officeFilter);
            if ($isSEFConsolidated) {
                $selectedOfficeName = 'Special Education Fund';
            } else {
                $selectedOfficeName = $selectedOffice ? $selectedOffice->office_name : null;
            }
        }

        $selectedAllotmentClassDesc = null;
        if ($allotmentClassFilter) {
            $selectedClass = AllotmentClass::where('class', $allotmentClassFilter)->first();
            $selectedAllotmentClassDesc = $selectedClass ? $selectedClass->description : null;
        }

        $selectedGroup = $request->input('group_filter');
        if ($selectedGroup) {
            $selectedGroup = Office::where('branch', $selectedGroup)->pluck('branch')->first();
        }

        $selectedFundType = $request->input('fund_type_filter');
        if ($selectedFundType) {
            $selectedFundType = FundSource::where('category', $selectedFundType)->pluck('category')->first();
        }

        $selectedFund = $request->input('fund_filter');
        if ($selectedFund) {
            $selectedFund = Fund::where('fund_type', $selectedFund)->pluck('fund_type')->first();
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')]
        ];

        $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass', 'fundSourceRelation', 'fund'])->where('year', $currentYear)->get();

        // Volume Metrics Calculations
        // Total Number of Obligations Created (unique obr_no from appropriations)
        $obligationQuery = Obligation::whereIn('id', 
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        );
        if ($fromDate) {
            $obligationQuery->where('obr_date', '>=', $fromDate);
        }
        if ($toDate) {
            $obligationQuery->where('obr_date', '<=', $toDate);
        }
        $totalObligationCount = $obligationQuery->distinct('obr_no')->count('obr_no');
        
        // Total Number of unique Purchase Orders (by po_number)
        $poQuery = \App\Models\PurchaseOrder::whereIn('obligation_amounts_id', 
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        );
        if ($fromDate) {
            $poQuery->where('po_date', '>=', $fromDate);
        }
        if ($toDate) {
            $poQuery->where('po_date', '<=', $toDate);
        }
        $totalPurchaseOrderCount = $poQuery->distinct('po_number')->count('po_number');

        // Total Number of unique Disbursements (by dv_no)
        $disburseQueryMetric = \App\Models\Disbursement::whereIn('obligation_amounts_id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        );
        if ($fromDate) {
            $disburseQueryMetric->where('disbursement_date', '>=', $fromDate);
        }
        if ($toDate) {
            $disburseQueryMetric->where('disbursement_date', '<=', $toDate);
        }
        $totalDisbursementCount = $disburseQueryMetric->distinct('dv_no')->count('dv_no');

        // Calculate days elapsed based on date range
        if ($fromDate && $toDate) {
            $startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $toDate);
            $daysElapsed = $startDate->diffInDays($endDate) + 1; // +1 to include the start date
        } else {
            // Use the current year as fallback
            $startOfYear = Carbon::createFromDate($currentYear, 1, 1);
            $daysElapsed = $startOfYear->diffInDays(now()) + 1; // +1 to include the current day
        }
        
        // Calculate Average Obligation Count per Day
        $averageObligationCountPerDay = $daysElapsed > 0 ? round($totalObligationCount / $daysElapsed, 2) : 0;
        
        // Calculate Average Disbursement Count per Day
        $averageDisbursementCountPerDay = $daysElapsed > 0 ? round($totalDisbursementCount / $daysElapsed, 2) : 0;
        
        // Get all obligations with unique obr_no and their amounts
        $obligationsMetricQuery = Obligation::whereIn('id', 
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        );
        if ($fromDate) {
            $obligationsMetricQuery->where('obr_date', '>=', $fromDate);
        }
        if ($toDate) {
            $obligationsMetricQuery->where('obr_date', '<=', $toDate);
        }
        $obligations = $obligationsMetricQuery->get();
        
        $obligationAmounts = [];
        foreach ($obligations as $obligation) {
            $amount = $obligation->obligationAmounts->sum('obr_amount');
            if ($amount > 0) {
                $obligationAmounts[] = $amount;
            }
        }
        
        // Obligation Distribution by Amount Range (histogram)
        $obligationRanges = [
            ['label' => '< 10,000', 'min' => 0, 'max' => 10000, 'count' => 0],
            ['label' => '10,000 - 50,000', 'min' => 10000, 'max' => 50000, 'count' => 0],
            ['label' => '50,000 - 100,000', 'min' => 50000, 'max' => 100000, 'count' => 0],
            ['label' => '100,000 - 500,000', 'min' => 100000, 'max' => 500000, 'count' => 0],
            ['label' => '500,000 - 1,000,000', 'min' => 500000, 'max' => 1000000, 'count' => 0],
            ['label' => '> 1,000,000', 'min' => 1000000, 'max' => PHP_INT_MAX, 'count' => 0],
        ];
        
        foreach ($obligationAmounts as $amount) {
            foreach ($obligationRanges as &$range) {
                if ($amount >= $range['min'] && $amount < $range['max']) {
                    $range['count']++;
                    break;
                }
            }
        }
        
        // Obligations by Quarter (unique obr_no) - based on when obligations were created
        $obligationsByQuarter = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarterStart = Carbon::createFromDate($currentYear, ($q - 1) * 3 + 1, 1);
            $quarterEnd = Carbon::createFromDate($currentYear, ($q - 1) * 3 + 3, 1)->endOfMonth();
            
            $quarterQuery = Obligation::whereIn('id',
                ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
            )->whereBetween('created_at', [$quarterStart, $quarterEnd]);
            
            if ($fromDate) {
                $quarterQuery->where('obr_date', '>=', $fromDate);
            }
            if ($toDate) {
                $quarterQuery->where('obr_date', '<=', $toDate);
            }
            
            $count = $quarterQuery->distinct('obr_no')->count('obr_no');
            
            $obligationsByQuarter[] = [
                'quarter' => 'Q' . $q,
                'count' => $count
            ];
        }

        return view('dashboard', compact(
            'officeAllotmentClasses',
            'totalAppropriations',
            'totalAllotments',
            'totalObligations',
            'allotmentBalance',
            'allotmentAccomplishment',
            'availableYears',
            'offices',
            'branches',
            'fundTypes',
            'funds',
            'allotmentClasses',
            'perPage',
            'breadcrumb',
            'totalSupplementals',
            'totalReversions',
            'totalRealignments',
            'totalAuthorizedAppropriations',
            'totalForLaterRelease',
            'totalAuthorizedAppropriationsBalance',
            'totalAuthorizedAppropriationsAccomplishment',
            'totalDisbursements',
            'totalDisbursementsToObligations',
            'totalDisbursementsToAppropriations',
            'disbursementBalance',
            'selectedYear',
            'selectedOfficeName',
            'selectedAllotmentClassDesc',
            'selectedGroup',
            'selectedFundType',
            'selectedFund',
            'isGuest',
            'office_allotment_classes',
            'appropriations',
            'isSEFConsolidated',
            'totalObligationCount',
            'totalPurchaseOrderCount',
            'totalDisbursementCount',
            'averageObligationCountPerDay',
            'averageDisbursementCountPerDay',
            'obligationRanges',
            'obligationsByQuarter'
        ));
    }

    public function accounts($id, Request $request)
    {
        $search = $request->input('search');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        $officeAllotmentClasses = OfficeAllotmentClass::with([
            'offices',
            'allotmentClass',
            'appropriations' => function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('programs', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('account_code', 'like', "%{$search}%")
                            ->orWhere('fpp_code', 'like', "%{$search}%");
                    });
                }
            },
            'supplementals' => function ($query) use ($fromDate, $toDate) {
                // Filter supplementals by supplemental_date
                if ($fromDate) {
                    $query->where('supplemental_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $query->where('supplemental_date', '<=', $toDate);
                }
            },
            'realignments' => function ($query) use ($fromDate, $toDate) {
                // Filter realignments by realignment_date
                if ($fromDate) {
                    $query->where('realignment_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $query->where('realignment_date', '<=', $toDate);
                }
            },
            'obligationAmounts' => function ($query) use ($fromDate, $toDate) {
                // Filter obligation amounts by obligation date
                if ($fromDate) {
                    $query->whereHas('obligation', function ($q) use ($fromDate) {
                        $q->where('obr_date', '>=', $fromDate);
                    });
                }
                if ($toDate) {
                    $query->whereHas('obligation', function ($q) use ($toDate) {
                        $q->where('obr_date', '<=', $toDate);
                    });
                }
            },
        ])->findOrFail($id);

        // Custom sorting: Accounts without program first, then by program
        // All accounts sort by account code LEFT to RIGHT
        $officeAllotmentClasses->appropriations = $this->sortAppropriations($officeAllotmentClasses->appropriations);

        // Check if user is a Guest and verify they have access to this office
        $isGuest = auth()->user()->hasRole('Guest');
        $userOfficeId = auth()->user()->office;

        if ($isGuest && $userOfficeId && $officeAllotmentClasses->office != $userOfficeId) {
            abort(403, 'Unauthorized access to this office data.');
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Balances | Accounts']
        ];

        // Determine current quarter based on server date
        $month = Carbon::now()->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        // Approved Appropriations
        $officeAllotmentClasses->appropriations_sum = $officeAllotmentClasses->appropriations->sum('appropriation');

        // Supplementals
        $supplementalQuery = Supplemental::where('type', 'Supplemental')
            ->where('office_allotment_classes_id', $officeAllotmentClasses->id);
        if ($fromDate) {
            $supplementalQuery->where('supplemental_date', '>=', $fromDate);
        }
        if ($toDate) {
            $supplementalQuery->where('supplemental_date', '<=', $toDate);
        }
        $officeAllotmentClasses->supplemental_sum = $supplementalQuery->sum('amount');

        // Reversions
        $reversionQuery = Supplemental::where('type', 'Reversion')
            ->where('office_allotment_classes_id', $officeAllotmentClasses->id);
        if ($fromDate) {
            $reversionQuery->where('supplemental_date', '>=', $fromDate);
        }
        if ($toDate) {
            $reversionQuery->where('supplemental_date', '<=', $toDate);
        }
        $officeAllotmentClasses->reversion_sum = $reversionQuery->sum('amount');

        // Realignments (positive for Recipient, negative for Source)
        $realignmentsQuery = Realignment::where('office_allotment_classes_id', $officeAllotmentClasses->id);
        if ($fromDate) {
            $realignmentsQuery->where('realignment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $realignmentsQuery->where('realignment_date', '<=', $toDate);
        }
        $officeAllotmentClasses->realignments_sum = $realignmentsQuery->get()
            ->sum(function ($realignment) {
                return $realignment->type === 'Source' ? -$realignment->amount : $realignment->amount;
            });

        // Calculate Authorized Appropriations
        $officeAllotmentClasses->authorized_appropriations = ($officeAllotmentClasses->appropriations_sum + $officeAllotmentClasses->supplemental_sum + $officeAllotmentClasses->realignments_sum) - $officeAllotmentClasses->reversion_sum;
        // Allotments
        $officeAllotmentClasses->allotments_sum = $officeAllotmentClasses->appropriations->sum(function ($item) {
            return ($item->quarter1 ?? 0) + ($item->quarter2 ?? 0) + ($item->quarter3 ?? 0) + ($item->quarter4 ?? 0);
        });

        // For Later Release (appropriations + supplementals)
        $officeAllotmentClasses->for_later_release =
            $officeAllotmentClasses->appropriations->sum(function ($app) use ($currentQuarter) {
                $forLater = 0;
                if ($currentQuarter < 2) $forLater += $app->quarter2 ?? 0;
                if ($currentQuarter < 3) $forLater += $app->quarter3 ?? 0;
                if ($currentQuarter < 4) $forLater += $app->quarter4 ?? 0;
                return $forLater;
            }) +
            $officeAllotmentClasses->supplementals
            ->where('type', 'Supplemental')
            ->sum(function ($supp) use ($currentQuarter) {
                $forLater = 0;
                if ($currentQuarter < 2) $forLater += $supp->quarter2 ?? 0;
                if ($currentQuarter < 3) $forLater += $supp->quarter3 ?? 0;
                if ($currentQuarter < 4) $forLater += $supp->quarter4 ?? 0;
                return $forLater;
            });

        $officeAllotmentClasses->allotments_sum -= $officeAllotmentClasses->for_later_release;

        $officeAllotmentClasses->allotments_sum += $officeAllotmentClasses->supplemental_sum;
        $officeAllotmentClasses->allotments_sum += $officeAllotmentClasses->realignments_sum;
        $officeAllotmentClasses->allotments_sum -= $officeAllotmentClasses->reversion_sum;

        // Calculate the obligation sum with adjustments
        $obligationAmountIds = $officeAllotmentClasses->obligationAmounts->pluck('id');
        // Get the sum of obligation amounts
        $obrSum = $officeAllotmentClasses->obligationAmounts->sum('obr_amount') ?? 0;
        // Get the sum of adjustments for the obligation amounts
        $adjustmentQuery = ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds);
        if ($fromDate) {
            $adjustmentQuery->where('adjustment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $adjustmentQuery->where('adjustment_date', '<=', $toDate);
        }
        $adjustmentSum = $obligationAmountIds->isNotEmpty()
            ? $adjustmentQuery->sum('adjustment_amount')
            : 0;
        // Obligations
        $obrSum += $adjustmentSum;
        $officeAllotmentClasses->obligations_sum = $obrSum;

        // Calculate Authorized Appropriations Balance
        $officeAllotmentClasses->balance_appropriations = $officeAllotmentClasses->authorized_appropriations - $obrSum;
        // Calculate Authorized Appropriations Accomplishment
        $officeAllotmentClasses->appropriation_accomplishment = $officeAllotmentClasses->authorized_appropriations > 0 ? ($obrSum / $officeAllotmentClasses->authorized_appropriations) * 100 : 0;

        // Calculate Allotment Balance
        $officeAllotmentClasses->balance_allotments = $officeAllotmentClasses->allotments_sum - $obrSum;
        // Calculate Allotment Accomplishment
        $officeAllotmentClasses->allotment_accomplishment =$officeAllotmentClasses->allotments_sum > 0 ? ($obrSum / $officeAllotmentClasses->allotments_sum) * 100 : 0;
        // Calculate Disbursements
        $disbursementsQuery = Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds);
        if ($fromDate) {
            $disbursementsQuery->where('disbursement_date', '>=', $fromDate);
        }
        if ($toDate) {
            $disbursementsQuery->where('disbursement_date', '<=', $toDate);
        }
        $officeAllotmentClasses->disbursements_sum = $obligationAmountIds->isNotEmpty()
            ? $disbursementsQuery->sum('disbursement_amount')
            : 0;
        // Calculate Disbursements / Obligations
        $officeAllotmentClasses->disbursements_to_obligations = $officeAllotmentClasses->obligations_sum > 0 ? ($officeAllotmentClasses->disbursements_sum / $officeAllotmentClasses->obligations_sum) * 100 : 0;
        // Calculate Disbursements / Authorized Appropriations
        $officeAllotmentClasses->disbursements_to_appropriations = $officeAllotmentClasses->authorized_appropriations > 0 ? ($officeAllotmentClasses->disbursements_sum / $officeAllotmentClasses->authorized_appropriations) * 100 : 0;
        // Calculate Disbursement Balance
        $officeAllotmentClasses->disbursement_balance = ($officeAllotmentClasses->obligations_sum - $officeAllotmentClasses->disbursements_sum);

        
        // Calculate the sum of appropriations, allotments, obligations, supplementals, and reversions for each appropriation
        foreach ($officeAllotmentClasses->appropriations as $appropriation) {
            // Approved Appropriations
            $appropriation->appropriation_sum = $appropriation->appropriation;

            // Supplementals
            $supplementalQueryApp = Supplemental::where('type', 'Supplemental')
                ->where('appropriations_id', $appropriation->id);
            if ($fromDate) {
                $supplementalQueryApp->where('supplemental_date', '>=', $fromDate);
            }
            if ($toDate) {
                $supplementalQueryApp->where('supplemental_date', '<=', $toDate);
            }
            $appropriation->supplemental_sum = $supplementalQueryApp->sum('amount');

            // Reversions
            $reversionQueryApp = Supplemental::where('type', 'Reversion')
                ->where('appropriations_id', $appropriation->id);
            if ($fromDate) {
                $reversionQueryApp->where('supplemental_date', '>=', $fromDate);
            }
            if ($toDate) {
                $reversionQueryApp->where('supplemental_date', '<=', $toDate);
            }
            $appropriation->reversion_sum = $reversionQueryApp->sum('amount');

            // Realignments (positive for Recipient, negative for Source)
            $realignmentsQueryApp = Realignment::where('appropriations_id', $appropriation->id);
            if ($fromDate) {
                $realignmentsQueryApp->where('realignment_date', '>=', $fromDate);
            }
            if ($toDate) {
                $realignmentsQueryApp->where('realignment_date', '<=', $toDate);
            }
            $appropriation->realignments_sum = $realignmentsQueryApp->get()
                ->sum(function ($realignment) {
                    return $realignment->type === 'Source' ? -$realignment->amount : $realignment->amount;
                });

            // Allotments
            $appropriation->allotments_sum = ($appropriation->quarter1 ?? 0)
                + ($appropriation->quarter2 ?? 0)
                + ($appropriation->quarter3 ?? 0)
                + ($appropriation->quarter4 ?? 0)
                + $appropriation->supplemental_sum
                - $appropriation->reversion_sum
                + $appropriation->realignments_sum;

            // For Later Release
            $forLater = 0;
            if ($currentQuarter < 2) $forLater += $appropriation->quarter2 ?? 0;
            if ($currentQuarter < 3) $forLater += $appropriation->quarter3 ?? 0;
            if ($currentQuarter < 4) $forLater += $appropriation->quarter4 ?? 0;

            $appropriation->for_later_release = $forLater;
            $appropriation->allotments_sum -= $appropriation->for_later_release;

            // Obligations & Adjustments
            $obligationAmountIds = $appropriation->obligationAmounts->pluck('id');
            $obrSum = $appropriation->obligationAmounts->sum('obr_amount') ?? 0;
            $adjustmentQueryApp = ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds);
            if ($fromDate) {
                $adjustmentQueryApp->where('adjustment_date', '>=', $fromDate);
            }
            if ($toDate) {
                $adjustmentQueryApp->where('adjustment_date', '<=', $toDate);
            }
            $adjustmentSum = $obligationAmountIds->isNotEmpty()
                ? $adjustmentQueryApp->sum('adjustment_amount')
                : 0;
            $appropriation->obligations_sum = $obrSum + $adjustmentSum;

            // Authorized Appropriations
            $appropriation->authorized_appropriations = ($appropriation->appropriation_sum + $appropriation->supplemental_sum)
                - $appropriation->reversion_sum
                + $appropriation->realignments_sum;

            // Accomplishments
            $appropriation->appropriation_accomplishment = $appropriation->authorized_appropriations > 0
                ? ($appropriation->obligations_sum / $appropriation->authorized_appropriations) * 100
                : 0;

            $appropriation->balance_allotments = $appropriation->allotments_sum - $appropriation->obligations_sum;

            $appropriation->allotment_accomplishment = $appropriation->allotments_sum > 0
                ? ($appropriation->obligations_sum / $appropriation->allotments_sum) * 100
                : 0;

            $appropriation->balance_appropriations = $appropriation->authorized_appropriations - $appropriation->obligations_sum;

            // Disbursements
            $disburseQuery = Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds);
            if ($fromDate) {
                $disburseQuery->where('disbursement_date', '>=', $fromDate);
            }
            if ($toDate) {
                $disburseQuery->where('disbursement_date', '<=', $toDate);
            }
            $appropriation->disbursements = $obligationAmountIds->isNotEmpty()
                ? $disburseQuery->sum('disbursement_amount') : 0;

            // Disbursement / Obligations
            $appropriation->disbursements_to_obligations = $appropriation->obligations_sum > 0
                ? ($appropriation->disbursements / $appropriation->obligations_sum) * 100
                : 0;

            // Disbursement / Authorized Appropriations
            $appropriation->disbursements_to_appropriations = $appropriation->authorized_appropriations > 0
                ? ($appropriation->disbursements / $appropriation->authorized_appropriations) * 100
                : 0;

            // Disbursement Balance
            $appropriation->disbursement_balance = $appropriation->obligations_sum - $appropriation->disbursements;
        }

        // Get all appropriation IDs for the filtered office allotment class
        $appropriationIds = $officeAllotmentClasses->appropriations->pluck('id');

        $selectedYear = $officeAllotmentClasses->year;

        $office_allotment_classes = OfficeAllotmentClass::with(['offices', 'allotmentClass', 'fundSourceRelation', 'fund'])->where('year', $selectedYear)->get();

        $appropriations = Appropriation::with([
            'obligationAmounts.obligationAdjustments',
            'realignments',
            'supplementals'
        ])->whereIn('id', $appropriationIds)->get();

        // Calculate balance for each appropriation
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

        foreach ($appropriations as $app) {
            if ($currentQuarter < 2) $forLater += $app->quarter2 ?? 0;
            if ($currentQuarter < 3) $forLater += $app->quarter3 ?? 0;
            if ($currentQuarter < 4) $forLater += $app->quarter4 ?? 0;
        }

        // Volume Metrics for Accounts Page
        // Total Number of Obligations Created (unique obr_no from appropriations)
        $oblQueryAccounts = Obligation::whereIn('id', 
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        );
        if ($fromDate) {
            $oblQueryAccounts->where('obr_date', '>=', $fromDate);
        }
        if ($toDate) {
            $oblQueryAccounts->where('obr_date', '<=', $toDate);
        }
        $totalObligationCount = $oblQueryAccounts->distinct('obr_no')->count('obr_no');
        
        // Total Number of unique Purchase Orders (by po_number)
        $poQueryAccounts = \App\Models\PurchaseOrder::whereIn('obligation_amounts_id', 
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        );
        if ($fromDate) {
            $poQueryAccounts->where('po_date', '>=', $fromDate);
        }
        if ($toDate) {
            $poQueryAccounts->where('po_date', '<=', $toDate);
        }
        $totalPurchaseOrderCount = $poQueryAccounts->distinct('po_number')->count('po_number');
        
        // Total Number of unique Disbursements (by dv_no)
        $disbQueryAccounts = \App\Models\Disbursement::whereIn('obligation_amounts_id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        );
        if ($fromDate) {
            $disbQueryAccounts->where('disbursement_date', '>=', $fromDate);
        }
        if ($toDate) {
            $disbQueryAccounts->where('disbursement_date', '<=', $toDate);
        }
        $totalDisbursementCount = $disbQueryAccounts->distinct('dv_no')->count('dv_no');

        // Calculate days elapsed based on date range
        if ($fromDate && $toDate) {
            $startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $toDate);
            $daysElapsed = $startDate->diffInDays($endDate) + 1; // +1 to include the start date
        } else {
            // Use the current year as fallback
            $startOfYear = Carbon::createFromDate($selectedYear, 1, 1);
            $daysElapsed = $startOfYear->diffInDays(now()) + 1; // +1 to include the current day
        }
        
        // Calculate Average Obligation Count per Day
        $averageObligationCountPerDay = $daysElapsed > 0 ? round($totalObligationCount / $daysElapsed, 2) : 0;
        
        // Calculate Average Disbursement Count per Day
        $averageDisbursementCountPerDay = $daysElapsed > 0 ? round($totalDisbursementCount / $daysElapsed, 2) : 0;
        
        // Get all obligations with unique obr_no and their amounts
        $obligationsAccountsQuery = Obligation::whereIn('id', 
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        );
        if ($fromDate) {
            $obligationsAccountsQuery->where('obr_date', '>=', $fromDate);
        }
        if ($toDate) {
            $obligationsAccountsQuery->where('obr_date', '<=', $toDate);
        }
        $obligations = $obligationsAccountsQuery->get();
        
        $obligationAmountsData = [];
        foreach ($obligations as $obligation) {
            $amount = $obligation->obligationAmounts->sum('obr_amount');
            if ($amount > 0) {
                $obligationAmountsData[] = $amount;
            }
        }
        
        // Obligation Distribution by Amount Range (histogram)
        $obligationRanges = [
            ['label' => '< 10,000', 'min' => 0, 'max' => 10000, 'count' => 0],
            ['label' => '10,000 - 50,000', 'min' => 10000, 'max' => 50000, 'count' => 0],
            ['label' => '50,000 - 100,000', 'min' => 50000, 'max' => 100000, 'count' => 0],
            ['label' => '100,000 - 500,000', 'min' => 100000, 'max' => 500000, 'count' => 0],
            ['label' => '500,000 - 1,000,000', 'min' => 500000, 'max' => 1000000, 'count' => 0],
            ['label' => '> 1,000,000', 'min' => 1000000, 'max' => PHP_INT_MAX, 'count' => 0],
        ];
        
        foreach ($obligationAmountsData as $amount) {
            foreach ($obligationRanges as &$range) {
                if ($amount >= $range['min'] && $amount < $range['max']) {
                    $range['count']++;
                    break;
                }
            }
        }
        
        // Obligations by Quarter (unique obr_no) - based on when obligations were created
        $obligationsByQuarter = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarterStart = Carbon::createFromDate($selectedYear, ($q - 1) * 3 + 1, 1);
            $quarterEnd = Carbon::createFromDate($selectedYear, ($q - 1) * 3 + 3, 1)->endOfMonth();
            
            $quarterQueryAccounts = Obligation::whereIn('id',
                ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
            )->whereBetween('created_at', [$quarterStart, $quarterEnd]);
            
            if ($fromDate) {
                $quarterQueryAccounts->where('obr_date', '>=', $fromDate);
            }
            if ($toDate) {
                $quarterQueryAccounts->where('obr_date', '<=', $toDate);
            }
            
            $count = $quarterQueryAccounts->distinct('obr_no')->count('obr_no');
            
            $obligationsByQuarter[] = [
                'quarter' => 'Q' . $q,
                'count' => $count
            ];
        }
        
        return view('dashboard.accounts', compact(
            'officeAllotmentClasses',
            'obrSum',
            'breadcrumb',
            'isGuest',
            'selectedYear',
            'office_allotment_classes',
            'appropriations',
            'totalObligationCount',
            'totalPurchaseOrderCount',
            'totalDisbursementCount',
            'averageObligationCountPerDay',
            'averageDisbursementCountPerDay',
            'obligationRanges',
            'obligationsByQuarter'
        ));
    }
}
