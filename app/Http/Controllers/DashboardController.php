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
use Carbon\Carbon;

class DashboardController extends Controller
{
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


        // Fetch all offices for group_filter (branch) dropdown
        $offices = Office::all();
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
        $officeAllotmentClassesQuery = OfficeAllotmentClass::with(['appropriations', 'obligationAmounts', 'fundSourceRelation', 'allotmentClass', 'offices', 'realignments'])
            ->where('year', $currentYear);

        if ($groupFilter) {
            $officeIds = $offices->where('branch', $groupFilter)->pluck('id');
            $officeAllotmentClassesQuery->whereIn('office', $officeIds);
        }
        if ($fundTypeFilter) {
            $fundSourceCategory = FundSource::where('category', $fundTypeFilter)->pluck('source');
            $officeAllotmentClassesQuery->whereIn('fund_source', $fundSourceCategory);
        }
        if ($fundFilter) {
            $fundType = Fund::where('fund_type', $fundFilter)->pluck('fund');
            $officeAllotmentClassesQuery->whereIn('fund', $fundType);
        }
        if ($officeFilter) {
            $officeAllotmentClassesQuery->where('office', $officeFilter);
        }
        if ($allotmentClassFilter) {
            $officeAllotmentClassesQuery->where('class', $allotmentClassFilter);
        }
        if ($search) {
            $officeAllotmentClassesQuery->where(function ($q) use ($search) {
                $q->where('fpp_code', 'like', "%$search%")
                    ->orWhere('responsibility_code', 'like', "%$search%")
                    ->orWhere('class', 'like', "%$search%")
                    ->orWhere('fund', 'like', "%$search%")
                    ->orWhere('fund_source', 'like', "%$search%");
            });
        }

        // Use pagination for the main table
        $query = $officeAllotmentClassesQuery;
        $selectedYear = $currentYear; // for appends
        $officeAllotmentClasses = $perPage == 'all'
            ? $query->orderBy('office')->get()
            : $query->orderBy('office')->paginate($perPage)->appends([
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
            $class->supplemental_sum = Supplemental::where('type', 'Supplemental')
                ->where('office_allotment_classes_id', $class->id)
                ->sum('amount');
            // Reversions
            $class->reversion_sum = Supplemental::where('type', 'Reversion')
                ->where('office_allotment_classes_id', $class->id)
                ->sum('amount');
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
            $adjustmentSum =
                $obligationAmountIds->isNotEmpty()
                ? ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds)->sum('adjustment_amount')
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
            $class->disbursements_sum =
                $obligationAmountIds->isNotEmpty()
                ? Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds)
                ->sum('disbursement_amount') : 0;

            // Disbursements / Obligation
            $class->disbursements_to_obligations = $class->obligation_sum > 0
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
        $totalSupplementals = Supplemental::where('type', 'Supplemental')
            ->whereIn('office_allotment_classes_id', $officeAllotmentClasses->pluck('id'))
            ->sum('amount');
        $totalReversions = Supplemental::where('type', 'Reversion')
            ->whereIn('office_allotment_classes_id', $officeAllotmentClasses->pluck('id'))
            ->sum('amount');

        $totalRealignments = Realignment::whereIn('appropriations_id', $appropriationIds)
            ->get()
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
        $totalObligations = ObligationAmount::whereIn('appropriation_id', $appropriationIds)->sum('obr_amount');
        $totalAdjustments = ObligationAdjustment::whereIn(
            'obligation_amounts_id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        )->sum('adjustment_amount');
        $totalObligations += $totalAdjustments;

        // Total For Later Release (all classes)
        $totalForLaterRelease = 0;
        $appropriations = Appropriation::whereIn('id', $appropriationIds)->get();
        foreach ($appropriations as $app) {
            if ($currentQuarter < 2) $totalForLaterRelease += $app->quarter2 ?? 0;
            if ($currentQuarter < 3) $totalForLaterRelease += $app->quarter3 ?? 0;
            if ($currentQuarter < 4) $totalForLaterRelease += $app->quarter4 ?? 0;
        }

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
        $totalDisbursements = Disbursement::whereIn(
            'obligation_amounts_id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id')
        )->sum('disbursement_amount');

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
            $selectedOfficeName = $selectedOffice ? $selectedOffice->office_name : null;
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
            'selectedFund'
        ));
    }

    public function accounts($id, Request $request)
    {
        $search = $request->input('search');
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
                $query->orderBy('id', 'asc');
            },
            'supplementals',
            'realignments',
        ])->findOrFail($id);

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
        $officeAllotmentClasses->supplemental_sum = Supplemental::where('type', 'Supplemental')
            ->where('office_allotment_classes_id', $officeAllotmentClasses->id)
            ->sum('amount');

        // Reversions
        $officeAllotmentClasses->reversion_sum = Supplemental::where('type', 'Reversion')
            ->where('office_allotment_classes_id', $officeAllotmentClasses->id)
            ->sum('amount');

        // Realignments (positive for Recipient, negative for Source)
        $officeAllotmentClasses->realignments_sum = Realignment::where('office_allotment_classes_id', $officeAllotmentClasses->id)
            ->get()
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
        $adjustmentSum =
            $obligationAmountIds->isNotEmpty()
            ? ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds)->sum('adjustment_amount')
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
        $officeAllotmentClasses->disbursements_sum =
            $obligationAmountIds->isNotEmpty()
            ? Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds)->sum('disbursement_amount')
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
            $appropriation->supplemental_sum = Supplemental::where('type', 'Supplemental')
                ->where('appropriations_id', $appropriation->id)
                ->sum('amount');

            // Reversions
            $appropriation->reversion_sum = Supplemental::where('type', 'Reversion')
                ->where('appropriations_id', $appropriation->id)
                ->sum('amount');

            // Realignments (positive for Recipient, negative for Source)
            $appropriation->realignments_sum = Realignment::where('appropriations_id', $appropriation->id)
                ->get()
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
            $adjustmentSum = $obligationAmountIds->isNotEmpty()
                ? ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds)->sum('adjustment_amount')
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
            $appropriation->disbursements = $obligationAmountIds->isNotEmpty()
                ? Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds)->sum('disbursement_amount') : 0;

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
        

        return view('dashboard.accounts', compact(
            'officeAllotmentClasses',
            'obrSum',
            'breadcrumb',
        ));
    }
}
