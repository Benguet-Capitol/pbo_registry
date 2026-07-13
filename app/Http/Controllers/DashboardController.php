<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // -------------------------------------------------------------------------
    // SHARED HELPERS
    // -------------------------------------------------------------------------

    /**
     * Determine the fiscal quarter (1–4) for a given reference date.
     * Falls back to the actual current date when no reference is provided,
     * so unfiltered views behave exactly as before.
     */
    private function currentQuarter(?string $referenceDate = null): int
    {
        $month = $referenceDate
            ? Carbon::parse($referenceDate)->month
            : Carbon::now()->month;

        return match (true) {
            $month <= 3  => 1,
            $month <= 6  => 2,
            $month <= 9  => 3,
            default      => 4,
        };
    }

    /**
     * Build the four batched lookup maps used by both index() and accounts()
     * for obligation amounts, adjustments, and disbursements.
     *
     * Returns: [ obrAmountIds, obrAmountsByAppropriationId,
     *            obrAmountIdsByAppropriationId, adjustmentsByObrAmount,
     *            disbursementsByObrAmount ]
     */
    private function buildObligationMaps(
        array $appropriationIds,
        ?string $fromDate,
        ?string $toDate
    ): array {
        // All OBR-amount rows for the given appropriations
        $obrAmountRows = ObligationAmount::whereIn('appropriation_id', $appropriationIds)
            ->when($fromDate, fn($q) => $q->whereHas('obligation', fn($q2) => $q2->where('obr_date', '>=', $fromDate)))
            ->when($toDate,   fn($q) => $q->whereHas('obligation', fn($q2) => $q2->where('obr_date', '<=', $toDate)))
            ->get(['id', 'appropriation_id', 'obr_amount']);

        // Cached collection-based lookups (no extra queries later)
        $obrAmountIds = $obrAmountRows->pluck('id');                            // Collection<int>

        $obrAmountsByAppropriationId = $obrAmountRows                           // [ appId => sumObr ]
            ->groupBy('appropriation_id')
            ->map(fn($items) => $items->sum('obr_amount'));

        $obrAmountIdsByAppropriationId = $obrAmountRows                         // [ appId => [obrAmountId, …] ]
            ->groupBy('appropriation_id')
            ->map(fn($items) => $items->pluck('id')->toArray());

        // Adjustments grouped by obr-amount id
        $adjustmentsByObrAmount = ObligationAdjustment::whereIn('obligation_amounts_id', $obrAmountIds)
            ->when($fromDate, fn($q) => $q->where('adjustment_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('adjustment_date', '<=', $toDate))
            ->get(['obligation_amounts_id', 'adjustment_amount'])
            ->groupBy('obligation_amounts_id')
            ->map(fn($items) => $items->sum('adjustment_amount'));

        // Disbursements grouped by obr-amount id
        $disbursementsByObrAmount = Disbursement::whereIn('obligation_amounts_id', $obrAmountIds)
            ->when($fromDate, fn($q) => $q->where('disbursement_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('disbursement_date', '<=', $toDate))
            ->get(['obligation_amounts_id', 'disbursement_amount'])
            ->groupBy('obligation_amounts_id')
            ->map(fn($items) => $items->sum('disbursement_amount'));

        return [
            $obrAmountIds,
            $obrAmountsByAppropriationId,
            $obrAmountIdsByAppropriationId,
            $adjustmentsByObrAmount,
            $disbursementsByObrAmount,
        ];
    }

    /**
     * Stamp financial metrics onto a single OfficeAllotmentClass instance.
     * Uses pre-built maps so zero extra queries are fired per class/appropriation.
     */
    private function stampClassMetrics(
        OfficeAllotmentClass $class,
        int     $currentQuarter,
        array   $supplementalsByClass,
        array   $reversionsByClass,
        array   $obrAmountsByAppropriationId,
        array   $obrAmountIdsByAppropriationId,
        array   $adjustmentsByObrAmount,
        array   $disbursementsByObrAmount,
        array   $forLaterSupplementalsByClass = []
    ): void {
        // --- Appropriations ---
        $class->appropriations_sum  = $class->appropriations->sum('appropriation');
        $class->supplemental_sum    = $supplementalsByClass[$class->id] ?? 0;
        $class->reversion_sum       = $reversionsByClass[$class->id]    ?? 0;
        $class->realignments_sum    = $class->realignments->sum(
            fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount
        );

        // --- Allotments (Q1–Q4 sum, then adjust) ---
        $rawAllotments = $class->appropriations->sum(
            fn($app) => ($app->quarter1 ?? 0) + ($app->quarter2 ?? 0)
                      + ($app->quarter3 ?? 0) + ($app->quarter4 ?? 0)
        );

        // For Later Release (future quarters not yet released)
        $forLater = 0;
        foreach ($class->appropriations as $app) {
            if ($currentQuarter < 2) $forLater += $app->quarter2 ?? 0;
            if ($currentQuarter < 3) $forLater += $app->quarter3 ?? 0;
            if ($currentQuarter < 4) $forLater += $app->quarter4 ?? 0;
        }
        // Supplementals are released on their own quarter schedule too
        $forLater += $forLaterSupplementalsByClass[$class->id] ?? 0;
        $class->for_later_release = $forLater;

        $class->allotments_sum = $rawAllotments
            + $class->supplemental_sum
            - $class->reversion_sum
            + $class->realignments_sum
            - $class->for_later_release;

        // --- Obligations (base + adjustments) ---
        $obrSum        = 0;
        $adjustmentSum = 0;
        $disbursementsSum = 0;

        foreach ($class->appropriations as $app) {
            $obrSum        += $obrAmountsByAppropriationId[$app->id]      ?? 0;
            $appObrIds      = $obrAmountIdsByAppropriationId[$app->id]    ?? [];
            foreach ($appObrIds as $obrAmountId) {
                $adjustmentSum    += $adjustmentsByObrAmount[$obrAmountId]    ?? 0;
                $disbursementsSum += $disbursementsByObrAmount[$obrAmountId]  ?? 0;
            }
        }

        $class->obligations_sum   = $obrSum + $adjustmentSum;
        $class->disbursements_sum = $disbursementsSum;

        // --- Authorized Appropriations ---
        $class->authorized_appropriations =
            ($class->appropriations_sum + $class->supplemental_sum + $class->realignments_sum)
            - $class->reversion_sum;

        // --- Balances & Accomplishments ---
        $class->balance_appropriations = $class->authorized_appropriations - $class->obligations_sum;
        $class->balance_allotments     = $class->allotments_sum - $class->obligations_sum;
        $class->disbursement_balance   = $class->obligations_sum - $class->disbursements_sum;

        $class->appropriation_accomplishment = $class->authorized_appropriations > 0
            ? ($class->obligations_sum / $class->authorized_appropriations) * 100 : 0;

        $class->allotment_accomplishment = $class->allotments_sum > 0
            ? ($class->obligations_sum / $class->allotments_sum) * 100 : 0;

        $class->disbursements_to_obligations = $class->obligations_sum > 0
            ? ($class->disbursements_sum / $class->obligations_sum) * 100 : 0;

        $class->disbursements_to_appropriations = $class->authorized_appropriations > 0
            ? ($class->disbursements_sum / $class->authorized_appropriations) * 100 : 0;
    }

    /**
     * Build histogram buckets from a flat array of amounts.
     */
    private function buildObligationRanges(array $amounts): array
    {
        $ranges = [
            ['label' => '< 10,000',            'min' => 0,       'max' => 10000,       'count' => 0],
            ['label' => '10,000 - 50,000',      'min' => 10000,   'max' => 50000,       'count' => 0],
            ['label' => '50,000 - 100,000',     'min' => 50000,   'max' => 100000,      'count' => 0],
            ['label' => '100,000 - 500,000',    'min' => 100000,  'max' => 500000,      'count' => 0],
            ['label' => '500,000 - 1,000,000',  'min' => 500000,  'max' => 1000000,     'count' => 0],
            ['label' => '> 1,000,000',          'min' => 1000000, 'max' => PHP_INT_MAX, 'count' => 0],
        ];

        foreach ($amounts as $amount) {
            foreach ($ranges as &$range) {
                if ($amount >= $range['min'] && $amount < $range['max']) {
                    $range['count']++;
                    break;
                }
            }
            unset($range);
        }

        return $ranges;
    }

    /**
     * Count obligations per fiscal quarter via a single DB query per quarter.
     * The four queries are small (date-bounded) and cannot easily be collapsed
     * into one without a raw CASE-WHEN, which is less readable.
     */
    private function buildObligationsByQuarter(
        array   $appropriationIds,
        int     $currentYear,
        ?string $fromDate,
        ?string $toDate
    ): array {
        $result = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarterStart = Carbon::createFromDate($currentYear, ($q - 1) * 3 + 1, 1);
            $quarterEnd   = Carbon::createFromDate($currentYear, ($q - 1) * 3 + 3, 1)->endOfMonth();

            $count = DB::table('obligations as o')
                ->join('obligation_amounts as oa', 'o.id', '=', 'oa.obligation_id')
                ->whereIn('oa.appropriation_id', $appropriationIds)
                ->whereBetween('o.created_at', [$quarterStart, $quarterEnd])
                ->when($fromDate, fn($q) => $q->where('o.obr_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('o.obr_date', '<=', $toDate))
                ->distinct('o.obr_no')
                ->count('o.obr_no');

            $result[] = ['quarter' => 'Q' . $q, 'count' => $count];
        }
        return $result;
    }

    /**
     * Calculate days elapsed for rate metrics.
     */
    private function daysElapsed(?string $fromDate, ?string $toDate, int $fallbackYear): int
    {
        if ($fromDate && $toDate) {
            return Carbon::createFromFormat('Y-m-d', $fromDate)
                ->diffInDays(Carbon::createFromFormat('Y-m-d', $toDate)) + 1;
        }
        return Carbon::createFromDate($fallbackYear, 1, 1)->diffInDays(now()) + 1;
    }

    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        // --- Request parameters ---
        $perPage               = $request->input('per_page', 'all');
        $search                = $request->input('search');
        $sortBy                = $request->query('sort_by', 'office');
        $sortOrder             = $request->query('sort_order', 'asc');
        $currentYear           = $request->input('year1', date('Y'));
        $groupFilter           = $request->input('group_filter');
        $fundTypeFilter        = $request->input('fund_type_filter');
        $fundFilter            = $request->input('fund_filter');
        $officeFilter          = $request->input('office_filter');
        $allotmentClassFilter  = $request->input('allotment_class_filter');
        $fromDate              = $request->input('from_date');
        $toDate                = $request->input('to_date');

        // --- Auth / role ---
        $isGuest      = auth()->user()->hasRole('Guest');
        $userOfficeId = auth()->user()->office;
        if ($isGuest && $userOfficeId) {
            $officeFilter = $userOfficeId;
        }

        // --- Dropdown data ---
        $offices = $isGuest && $userOfficeId
            ? Office::where('id', $userOfficeId)->get()
            : Office::all();

        $branches       = $offices->pluck('branch')->unique()->filter()->sort()->values();
        $fundTypes      = FundSource::select('category')->orderBy('category')->pluck('category')->unique()->values();
        $funds          = Fund::select('fund_type')->distinct()->pluck('fund_type')->filter()->unique()->sort()->values();
        $allotmentClasses = OfficeAllotmentClass::with('allotmentClass')
            ->select('class')->distinct()->get()->unique('class')
            ->sortBy(fn($item) => optional($item->allotmentClass)->id)->values();

        // --- Base query ---
        $query = OfficeAllotmentClass::with([
            'appropriations',
            'fundSourceRelation',
            'allotmentClass',
            'offices',
            'realignments' => function ($q) use ($fromDate, $toDate) {
                $q->when($fromDate, fn($q) => $q->where('realignment_date', '>=', $fromDate))
                  ->when($toDate,   fn($q) => $q->where('realignment_date', '<=', $toDate));
            },
        ])->where('year', $currentYear);

        // --- Filters ---
        if ($isGuest && $userOfficeId) {
            $query->where('office_allotment_classes.office', $userOfficeId);
        }
        if ($groupFilter) {
            $officeIds = $offices->where('branch', $groupFilter)->pluck('id');
            $query->whereIn('office_allotment_classes.office', $officeIds);
        }
        if ($fundTypeFilter) {
            $sources = FundSource::where('category', $fundTypeFilter)->pluck('source');
            $query->whereIn('office_allotment_classes.fund_source', $sources);
        }
        if ($fundFilter) {
            $fundTypes2 = Fund::where('fund_type', $fundFilter)->pluck('fund');
            $query->whereIn('office_allotment_classes.fund', $fundTypes2);
        }

        // SEF consolidation logic
        $isSEFConsolidated = false;
        if ($officeFilter && !$isGuest) {
            $selectedOfficeRecord = Office::find($officeFilter);
            if ($selectedOfficeRecord && $selectedOfficeRecord->fund === 'Special Education Fund') {
                $sefIds = Office::where('fund', 'Special Education Fund')->pluck('id');
                $query->whereIn('office_allotment_classes.office', $sefIds);
                $isSEFConsolidated = true;
            } else {
                $query->where('office_allotment_classes.office', $officeFilter);
            }
        }

        if ($allotmentClassFilter) {
            $query->where('office_allotment_classes.class', $allotmentClassFilter);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('office_allotment_classes.fpp_code', 'like', "%$search%")
                  ->orWhere('office_allotment_classes.responsibility_code', 'like', "%$search%")
                  ->orWhere('office_allotment_classes.class', 'like', "%$search%")
                  ->orWhere('office_allotment_classes.fund', 'like', "%$search%")
                  ->orWhere('office_allotment_classes.fund_source', 'like', "%$search%")
                  ->orWhereHas('offices', fn($q2) => $q2
                      ->where('office_abbreviation', 'like', "%$search%")
                      ->orWhere('office_name', 'like', "%$search%"));
            });
        }

        // --- Pagination / fetch ---
        $orderedQuery = $query
            ->orderBy('office_allotment_classes.office')
            ->join('allotment_classes', 'office_allotment_classes.class', '=', 'allotment_classes.class')
            ->orderBy('allotment_classes.id')
            ->select('office_allotment_classes.*');

        $selectedYear = $currentYear;

        $officeAllotmentClasses = $perPage === 'all'
            ? $orderedQuery->get()
            : $orderedQuery->paginate($perPage)->appends([
                'search'     => $search,
                'sort_by'    => $sortBy,
                'sort_order' => $sortOrder,
                'year1'      => $selectedYear,
            ]);

        // --- Shared derived IDs (defined ONCE, reused everywhere) ---
        $classIds = $officeAllotmentClasses->pluck('id')->toArray();

        // Flat list of all appropriation IDs for the loaded classes
        $appropriationIds = $officeAllotmentClasses
            ->flatMap(fn($c) => $c->appropriations->pluck('id'))
            ->unique()
            ->values()
            ->toArray();

        $currentQuarter = $this->currentQuarter($toDate);

        // --- Batch aggregations ---
        $supplementalRows = Supplemental::where('type', 'Supplemental')
            ->whereIn('office_allotment_classes_id', $classIds)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->get(['office_allotment_classes_id', 'amount', 'quarter1', 'quarter2', 'quarter3', 'quarter4']);

        $supplementalsByClass = $supplementalRows
            ->groupBy('office_allotment_classes_id')
            ->map(fn($items) => $items->sum('amount'))
            ->toArray();

        $forLaterSupplementalsByClass = $supplementalRows
        ->groupBy('office_allotment_classes_id')
        ->map(function ($items) use ($currentQuarter) {
            return $items->sum(function ($supp) use ($currentQuarter) {
                $fl = 0;
                if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                return $fl;
            });
        })
        ->toArray();

        $reversionsByClass = Supplemental::where('type', 'Reversion')
            ->whereIn('office_allotment_classes_id', $classIds)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->get(['office_allotment_classes_id', 'amount'])
            ->groupBy('office_allotment_classes_id')
            ->map(fn($items) => $items->sum('amount'))
            ->toArray();

        [
            $obrAmountIds,
            $obrAmountsByAppropriationId,
            $obrAmountIdsByAppropriationId,
            $adjustmentsByObrAmount,
            $disbursementsByObrAmount,
        ] = $this->buildObligationMaps($appropriationIds, $fromDate, $toDate);

        // Convert maps to plain arrays for array-access in stampClassMetrics
        $obrAmountsByAppropriationId    = $obrAmountsByAppropriationId->toArray();
        $obrAmountIdsByAppropriationId  = $obrAmountIdsByAppropriationId->toArray();
        $adjustmentsByObrAmount         = $adjustmentsByObrAmount->toArray();
        $disbursementsByObrAmount       = $disbursementsByObrAmount->toArray();

        // --- Per-class metric stamping (no DB queries inside loop) ---
        foreach ($officeAllotmentClasses as $class) {
            $this->stampClassMetrics(
                $class,
                $currentQuarter,
                $supplementalsByClass,
                $reversionsByClass,
                $obrAmountsByAppropriationId,
                $obrAmountIdsByAppropriationId,
                $adjustmentsByObrAmount,
                $disbursementsByObrAmount,
                $forLaterSupplementalsByClass
            );
        }

        // --- Summary card totals ---
        $totalAppropriations = Appropriation::whereIn('id', $appropriationIds)->sum('appropriation');

        $totalSupplementals = Supplemental::where('type', 'Supplemental')
            ->whereIn('office_allotment_classes_id', $classIds)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->sum('amount');

        $totalReversions = Supplemental::where('type', 'Reversion')
            ->whereIn('office_allotment_classes_id', $classIds)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->sum('amount');

        $totalRealignments = Realignment::whereIn('appropriations_id', $appropriationIds)
            ->when($fromDate, fn($q) => $q->where('realignment_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('realignment_date', '<=', $toDate))
            ->get()
            ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

        // Allotments: single query for all four quarters
        $quarterTotals = Appropriation::whereIn('id', $appropriationIds)
            ->selectRaw('SUM(quarter1) as q1, SUM(quarter2) as q2, SUM(quarter3) as q3, SUM(quarter4) as q4')
            ->first();

        $totalForLaterRelease = 0;
        if ($currentQuarter < 2) $totalForLaterRelease += $quarterTotals->q2 ?? 0;
        if ($currentQuarter < 3) $totalForLaterRelease += $quarterTotals->q3 ?? 0;
        if ($currentQuarter < 4) $totalForLaterRelease += $quarterTotals->q4 ?? 0;

        $totalForLaterRelease += array_sum($forLaterSupplementalsByClass);

        $totalAllotments = (($quarterTotals->q1 ?? 0) + ($quarterTotals->q2 ?? 0)
                          + ($quarterTotals->q3 ?? 0) + ($quarterTotals->q4 ?? 0))
                         + $totalSupplementals
                         + $totalRealignments
                         - $totalReversions
                         - $totalForLaterRelease;

        // Obligations total (base + adjustments via preloaded maps)
        $totalObligations  = array_sum(array_intersect_key($obrAmountsByAppropriationId, array_flip($appropriationIds)));
        $totalAdjustments  = $adjustmentsByObrAmount ? array_sum($adjustmentsByObrAmount) : 0;
        $totalObligations += $totalAdjustments;

        // Disbursements total
        $totalDisbursements = $disbursementsByObrAmount ? array_sum($disbursementsByObrAmount) : 0;

        // Authorized appropriations
        $totalAuthorizedAppropriations = ($totalAppropriations + $totalSupplementals + $totalRealignments) - $totalReversions;

        // Derived summary metrics
        $allotmentBalance                           = $totalAllotments - $totalObligations;
        $allotmentAccomplishment                    = $totalAllotments > 0 ? ($totalObligations / $totalAllotments) * 100 : 0;
        $totalAuthorizedAppropriationsBalance       = $totalAuthorizedAppropriations - $totalObligations;
        $totalAuthorizedAppropriationsAccomplishment = $totalAuthorizedAppropriations > 0
            ? ($totalObligations / $totalAuthorizedAppropriations) * 100 : 0;
        $totalDisbursementsToObligations            = $totalObligations > 0
            ? ($totalDisbursements / $totalObligations) * 100 : 0;
        $totalDisbursementsToAppropriations         = $totalAuthorizedAppropriations > 0
            ? ($totalDisbursements / $totalAuthorizedAppropriations) * 100 : 0;
        $disbursementBalance                        = $totalObligations - $totalDisbursements;

        // --- Volume metrics ---
        $obligationQuery = Obligation::whereIn('id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        )->when($fromDate, fn($q) => $q->where('obr_date', '>=', $fromDate))
         ->when($toDate,   fn($q) => $q->where('obr_date', '<=', $toDate));

        $totalObligationCount = $obligationQuery->distinct('obr_no')->count('obr_no');

        // Reuse the already-fetched $obrAmountIds instead of re-querying
        $totalPurchaseOrderCount = PurchaseOrder::whereIn('obligation_amounts_id', $obrAmountIds)
            ->when($fromDate, fn($q) => $q->where('po_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('po_date', '<=', $toDate))
            ->distinct('po_number')->count('po_number');

        $totalDisbursementCount = Disbursement::whereIn('obligation_amounts_id', $obrAmountIds)
            ->when($fromDate, fn($q) => $q->where('disbursement_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('disbursement_date', '<=', $toDate))
            ->distinct('dv_no')->count('dv_no');

        $daysElapsed                  = $this->daysElapsed($fromDate, $toDate, $currentYear);
        $averageObligationCountPerDay  = $daysElapsed > 0 ? round($totalObligationCount  / $daysElapsed, 2) : 0;
        $averageDisbursementCountPerDay = $daysElapsed > 0 ? round($totalDisbursementCount / $daysElapsed, 2) : 0;

        // --- Histogram: build from preloaded maps (no extra queries) ---
        $obligationsForHistogram = DB::table('obligations as o')
            ->join('obligation_amounts as oa', 'o.id', '=', 'oa.obligation_id')
            ->whereIn('oa.appropriation_id', $appropriationIds)
            ->when($fromDate, fn($q) => $q->where('o.obr_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('o.obr_date', '<=', $toDate))
            ->selectRaw('oa.id as oa_id, SUM(oa.obr_amount) as base_amount')
            ->groupBy('o.id', 'oa.id')
            ->get();

        $obligationAmounts = [];
        foreach ($obligationsForHistogram as $record) {
            $total = $record->base_amount + ($adjustmentsByObrAmount[$record->oa_id] ?? 0);
            if ($total > 0) {
                $obligationAmounts[] = $total;
            }
        }

        $obligationRanges    = $this->buildObligationRanges($obligationAmounts);
        $obligationsByQuarter = $this->buildObligationsByQuarter($appropriationIds, $currentYear, $fromDate, $toDate);

        // --- Appropriations for create modal ---
        $appropriations = Appropriation::with([
            'obligationAmounts.obligationAdjustments',
            'realignments',
            'supplementals',
        ])->whereIn('id', $appropriationIds)->get();

        $appropriations->each(function ($appropriation) use ($currentQuarter) {
            $totalAppropriation = collect([
                $appropriation->quarter1, $appropriation->quarter2,
                $appropriation->quarter3, $appropriation->quarter4,
            ])->take($currentQuarter)->sum();

            $totalObrAmount = $appropriation->obligationAmounts->sum(
                fn($oa) => $oa->obr_amount + $oa->obligationAdjustments->sum('adjustment_amount')
            );

            $realignmentTotal = $appropriation->realignments->sum(
                fn($r) => $r->type === 'Recipient' ? $r->amount : ($r->type === 'Source' ? -$r->amount : 0)
            );

            $supplementalTotal = $appropriation->supplementals->sum(
                fn($s) => $s->type === 'Supplemental' ? $s->amount : ($s->type === 'Reversion' ? -$s->amount : 0)
            );

            $appropriation->balance = ($totalAppropriation + $realignmentTotal + $supplementalTotal) - $totalObrAmount;
        });

        // --- Misc view data ---
        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // Broad unfiltered set used only for the obligation/create modals
        $office_allotment_classes = OfficeAllotmentClass::with([
            'offices', 'allotmentClass', 'fundSourceRelation', 'fund',
        ])->where('year', $currentYear)->get();

        // Display labels for active filters
        $selectedOfficeName = null;
        if ($officeFilter) {
            $selectedOfficeName = $isSEFConsolidated
                ? 'Special Education Fund'
                : optional(Office::find($officeFilter))->office_name;
        }

        $selectedAllotmentClassDesc = $allotmentClassFilter
            ? optional(AllotmentClass::where('class', $allotmentClassFilter)->first())->description
            : null;

        $selectedGroup = $groupFilter
            ? Office::where('branch', $groupFilter)->value('branch')
            : null;

        $selectedFundType = $fundTypeFilter
            ? FundSource::where('category', $fundTypeFilter)->value('category')
            : null;

        $selectedFund = $fundFilter
            ? Fund::where('fund_type', $fundFilter)->value('fund_type')
            : null;

        $breadcrumb = [['label' => 'Dashboard', 'route' => route('dashboard')]];

        return view('dashboard', compact(
            'officeAllotmentClasses',
            'office_allotment_classes',
            'appropriations',
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

    // -------------------------------------------------------------------------
    // ACCOUNTS
    // -------------------------------------------------------------------------

    public function accounts($id, Request $request)
    {
        $search   = $request->input('search');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        $officeAllotmentClasses = OfficeAllotmentClass::with([
            'offices',
            'allotmentClass',
            'appropriations' => function ($query) use ($search, $fromDate, $toDate) {
                if (!empty($search)) {
                    $query->where(fn($q) => $q
                        ->where('programs', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('account_code', 'like', "%{$search}%")
                        ->orWhere('fpp_code', 'like', "%{$search}%"));
                }
                $query->with(['obligationAmounts' => fn($q) => $q
                    ->when($fromDate, fn($q2) => $q2->whereHas('obligation', fn($q3) => $q3->where('obr_date', '>=', $fromDate)))
                    ->when($toDate,   fn($q2) => $q2->whereHas('obligation', fn($q3) => $q3->where('obr_date', '<=', $toDate)))
                ]);
            },
            'supplementals' => fn($q) => $q
                ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate)),
            'realignments' => fn($q) => $q
                ->when($fromDate, fn($q) => $q->where('realignment_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('realignment_date', '<=', $toDate)),
            'obligationAmounts' => fn($q) => $q
                ->when($fromDate, fn($q2) => $q2->whereHas('obligation', fn($q3) => $q3->where('obr_date', '>=', $fromDate)))
                ->when($toDate,   fn($q2) => $q2->whereHas('obligation', fn($q3) => $q3->where('obr_date', '<=', $toDate))),
        ])->findOrFail($id);

        // Authorisation check for guests
        $isGuest      = auth()->user()->hasRole('Guest');
        $userOfficeId = auth()->user()->office;
        if ($isGuest && $userOfficeId && $officeAllotmentClasses->office != $userOfficeId) {
            abort(403, 'Unauthorized access to this office data.');
        }

        // Sort appropriations
        $officeAllotmentClasses->appropriations = $this->sortAppropriations($officeAllotmentClasses->appropriations);

        $currentQuarter = $this->currentQuarter($toDate);
        $selectedYear   = $officeAllotmentClasses->year;

        // --- Class-level aggregates ---
        $officeAllotmentClasses->appropriations_sum = $officeAllotmentClasses->appropriations->sum('appropriation');

        $officeAllotmentClasses->supplemental_sum = Supplemental::where('type', 'Supplemental')
            ->where('office_allotment_classes_id', $officeAllotmentClasses->id)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->sum('amount');

        $officeAllotmentClasses->reversion_sum = Supplemental::where('type', 'Reversion')
            ->where('office_allotment_classes_id', $officeAllotmentClasses->id)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->sum('amount');

        $officeAllotmentClasses->realignments_sum = Realignment::where('office_allotment_classes_id', $officeAllotmentClasses->id)
            ->when($fromDate, fn($q) => $q->where('realignment_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('realignment_date', '<=', $toDate))
            ->get()
            ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

        $officeAllotmentClasses->authorized_appropriations =
            ($officeAllotmentClasses->appropriations_sum
             + $officeAllotmentClasses->supplemental_sum
             + $officeAllotmentClasses->realignments_sum)
            - $officeAllotmentClasses->reversion_sum;

        // Allotments
        $rawAllotments = $officeAllotmentClasses->appropriations->sum(
            fn($app) => ($app->quarter1 ?? 0) + ($app->quarter2 ?? 0)
                      + ($app->quarter3 ?? 0) + ($app->quarter4 ?? 0)
        );

        $forLaterClass = $officeAllotmentClasses->appropriations->sum(function ($app) use ($currentQuarter) {
            $fl = 0;
            if ($currentQuarter < 2) $fl += $app->quarter2 ?? 0;
            if ($currentQuarter < 3) $fl += $app->quarter3 ?? 0;
            if ($currentQuarter < 4) $fl += $app->quarter4 ?? 0;
            return $fl;
        }) + $officeAllotmentClasses->supplementals->where('type', 'Supplemental')
            ->sum(function ($supp) use ($currentQuarter) {
                $fl = 0;
                if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                return $fl;
            });

        $officeAllotmentClasses->for_later_release = $forLaterClass;
        $officeAllotmentClasses->allotments_sum    = $rawAllotments
            + $officeAllotmentClasses->supplemental_sum
            - $officeAllotmentClasses->reversion_sum
            + $officeAllotmentClasses->realignments_sum
            - $forLaterClass;

        // Obligations
        $obligationAmountIds = $officeAllotmentClasses->obligationAmounts->pluck('id');
        $obrSum              = $officeAllotmentClasses->obligationAmounts->sum('obr_amount') ?? 0;

        $adjustmentSum = $obligationAmountIds->isNotEmpty()
            ? ObligationAdjustment::whereIn('obligation_amounts_id', $obligationAmountIds)
                ->when($fromDate, fn($q) => $q->where('adjustment_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('adjustment_date', '<=', $toDate))
                ->sum('adjustment_amount')
            : 0;

        $officeAllotmentClasses->obligations_sum = $obrSum + $adjustmentSum;

        $officeAllotmentClasses->balance_appropriations    = $officeAllotmentClasses->authorized_appropriations - $officeAllotmentClasses->obligations_sum;
        $officeAllotmentClasses->appropriation_accomplishment = $officeAllotmentClasses->authorized_appropriations > 0
            ? ($officeAllotmentClasses->obligations_sum / $officeAllotmentClasses->authorized_appropriations) * 100 : 0;
        $officeAllotmentClasses->balance_allotments           = $officeAllotmentClasses->allotments_sum - $officeAllotmentClasses->obligations_sum;
        $officeAllotmentClasses->allotment_accomplishment     = $officeAllotmentClasses->allotments_sum > 0
            ? ($officeAllotmentClasses->obligations_sum / $officeAllotmentClasses->allotments_sum) * 100 : 0;

        $disbursementsSum = $obligationAmountIds->isNotEmpty()
            ? Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds)
                ->when($fromDate, fn($q) => $q->where('disbursement_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('disbursement_date', '<=', $toDate))
                ->sum('disbursement_amount')
            : 0;

        $officeAllotmentClasses->disbursements_sum              = $disbursementsSum;
        $officeAllotmentClasses->disbursements_to_obligations   = $officeAllotmentClasses->obligations_sum > 0
            ? ($disbursementsSum / $officeAllotmentClasses->obligations_sum) * 100 : 0;
        $officeAllotmentClasses->disbursements_to_appropriations = $officeAllotmentClasses->authorized_appropriations > 0
            ? ($disbursementsSum / $officeAllotmentClasses->authorized_appropriations) * 100 : 0;
        $officeAllotmentClasses->disbursement_balance           = $officeAllotmentClasses->obligations_sum - $disbursementsSum;

        // --- Per-appropriation metrics ---
        // Build appropriation-level maps in batch to avoid N+1 per-appropriation queries
        $appIds = $officeAllotmentClasses->appropriations->pluck('id')->toArray();

        [
            ,   // $obrAmountIds – not needed here (already have obligationAmounts loaded)
            $appObrAmountsByAppId,
            $appObrAmountIdsByAppId,
            $appAdjustmentsByObrId,
            $appDisbursementsByObrId,
        ] = $this->buildObligationMaps($appIds, $fromDate, $toDate);

        $appSupplementalRows = Supplemental::where('type', 'Supplemental')
            ->whereIn('appropriations_id', $appIds)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->get(['appropriations_id', 'amount', 'quarter1', 'quarter2', 'quarter3', 'quarter4']);

        $appSupplementalsByAppId = $appSupplementalRows
            ->groupBy('appropriations_id')
            ->map(fn($items) => $items->sum('amount'))
            ->toArray();

        // Supplementals for this appropriation, released on their own quarter schedule
        $appForLaterSupplementalsByAppId = $appSupplementalRows
            ->groupBy('appropriations_id')
            ->map(function ($items) use ($currentQuarter) {
                return $items->sum(function ($supp) use ($currentQuarter) {
                    $fl = 0;
                    if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                    if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                    if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                    return $fl;
                });
            })
            ->toArray();

        $appReversionsByAppId = Supplemental::where('type', 'Reversion')
            ->whereIn('appropriations_id', $appIds)
            ->when($fromDate, fn($q) => $q->where('supplemental_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('supplemental_date', '<=', $toDate))
            ->get(['appropriations_id', 'amount'])
            ->groupBy('appropriations_id')
            ->map(fn($items) => $items->sum('amount'))
            ->toArray();

        $appRealignmentsByAppId = Realignment::whereIn('appropriations_id', $appIds)
            ->when($fromDate, fn($q) => $q->where('realignment_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('realignment_date', '<=', $toDate))
            ->get()
            ->groupBy('appropriations_id')
            ->map(fn($items) => $items->sum(
                fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount
            ))
            ->toArray();

        foreach ($officeAllotmentClasses->appropriations as $appropriation) {
            $appId = $appropriation->id;

            $appropriation->appropriation_sum  = $appropriation->appropriation;
            $appropriation->supplemental_sum   = $appSupplementalsByAppId[$appId] ?? 0;
            $appropriation->reversion_sum      = $appReversionsByAppId[$appId]    ?? 0;
            $appropriation->realignments_sum   = $appRealignmentsByAppId[$appId]  ?? 0;

            $appropriation->allotments_sum =
                ($appropriation->quarter1 ?? 0) + ($appropriation->quarter2 ?? 0)
              + ($appropriation->quarter3 ?? 0) + ($appropriation->quarter4 ?? 0)
              + $appropriation->supplemental_sum
              - $appropriation->reversion_sum
              + $appropriation->realignments_sum;

            $fl = 0;
            if ($currentQuarter < 2) $fl += $appropriation->quarter2 ?? 0;
            if ($currentQuarter < 3) $fl += $appropriation->quarter3 ?? 0;
            if ($currentQuarter < 4) $fl += $appropriation->quarter4 ?? 0;
            $fl += $appForLaterSupplementalsByAppId[$appId] ?? 0;
            $appropriation->for_later_release  = $fl;
            $appropriation->allotments_sum    -= $fl;

            // Obligations
            $appObrBase   = $appObrAmountsByAppId[$appId] ?? 0;
            $appObrIds    = $appObrAmountIdsByAppId[$appId] ?? [];
            $appAdjSum    = array_sum(array_intersect_key($appAdjustmentsByObrId->toArray(), array_flip($appObrIds)));
            $appDisbSum   = array_sum(array_intersect_key($appDisbursementsByObrId->toArray(), array_flip($appObrIds)));

            $appropriation->obligations_sum = $appObrBase + $appAdjSum;

            $appropriation->authorized_appropriations =
                ($appropriation->appropriation_sum + $appropriation->supplemental_sum)
                - $appropriation->reversion_sum
                + $appropriation->realignments_sum;

            $appropriation->appropriation_accomplishment = $appropriation->authorized_appropriations > 0
                ? ($appropriation->obligations_sum / $appropriation->authorized_appropriations) * 100 : 0;

            $appropriation->balance_allotments     = $appropriation->allotments_sum - $appropriation->obligations_sum;
            $appropriation->allotment_accomplishment = $appropriation->allotments_sum > 0
                ? ($appropriation->obligations_sum / $appropriation->allotments_sum) * 100 : 0;

            $appropriation->balance_appropriations = $appropriation->authorized_appropriations - $appropriation->obligations_sum;

            $appropriation->disbursements = $appDisbSum;
            $appropriation->disbursements_to_obligations = $appropriation->obligations_sum > 0
                ? ($appropriation->disbursements / $appropriation->obligations_sum) * 100 : 0;
            $appropriation->disbursements_to_appropriations = $appropriation->authorized_appropriations > 0
                ? ($appropriation->disbursements / $appropriation->authorized_appropriations) * 100 : 0;
            $appropriation->disbursement_balance = $appropriation->obligations_sum - $appropriation->disbursements;
        }

        // --- Modal appropriations ---
        $appropriationIds = $officeAllotmentClasses->appropriations->pluck('id')->toArray();

        $appropriations = Appropriation::with([
            'obligationAmounts.obligationAdjustments',
            'realignments',
            'supplementals',
        ])->whereIn('id', $appropriationIds)->get();

        $appropriations->each(function ($appropriation) use ($currentQuarter) {
            $totalAppropriation = collect([
                $appropriation->quarter1, $appropriation->quarter2,
                $appropriation->quarter3, $appropriation->quarter4,
            ])->take($currentQuarter)->sum();

            $totalObrAmount = $appropriation->obligationAmounts->sum(
                fn($oa) => $oa->obr_amount + $oa->obligationAdjustments->sum('adjustment_amount')
            );

            $realignmentTotal = $appropriation->realignments->sum(
                fn($r) => $r->type === 'Recipient' ? $r->amount : ($r->type === 'Source' ? -$r->amount : 0)
            );

            $supplementalTotal = $appropriation->supplementals->sum(
                fn($s) => $s->type === 'Supplemental' ? $s->amount : ($s->type === 'Reversion' ? -$s->amount : 0)
            );

            $appropriation->balance = ($totalAppropriation + $realignmentTotal + $supplementalTotal) - $totalObrAmount;
        });

        // --- Volume metrics for accounts page ---
        $oblAmountIds = ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('id');

        $totalObligationCount = Obligation::whereIn('id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        )->when($fromDate, fn($q) => $q->where('obr_date', '>=', $fromDate))
         ->when($toDate,   fn($q) => $q->where('obr_date', '<=', $toDate))
         ->distinct('obr_no')->count('obr_no');

        $totalPurchaseOrderCount = PurchaseOrder::whereIn('obligation_amounts_id', $oblAmountIds)
            ->when($fromDate, fn($q) => $q->where('po_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('po_date', '<=', $toDate))
            ->distinct('po_number')->count('po_number');

        $totalDisbursementCount = Disbursement::whereIn('obligation_amounts_id', $oblAmountIds)
            ->when($fromDate, fn($q) => $q->where('disbursement_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('disbursement_date', '<=', $toDate))
            ->distinct('dv_no')->count('dv_no');

        $daysElapsed                   = $this->daysElapsed($fromDate, $toDate, $selectedYear);
        $averageObligationCountPerDay   = $daysElapsed > 0 ? round($totalObligationCount  / $daysElapsed, 2) : 0;
        $averageDisbursementCountPerDay = $daysElapsed > 0 ? round($totalDisbursementCount / $daysElapsed, 2) : 0;

        // Histogram (without adjustments is a known inconsistency – now fixed to match index())
        $obligations = Obligation::whereIn('id',
            ObligationAmount::whereIn('appropriation_id', $appropriationIds)->pluck('obligation_id')
        )->when($fromDate, fn($q) => $q->where('obr_date', '>=', $fromDate))
         ->when($toDate,   fn($q) => $q->where('obr_date', '<=', $toDate))
         ->with('obligationAmounts')
         ->get();

        // Batch-load adjustments for the histogram amounts (fixes the inconsistency noted in review)
        $histObrAmountIds = $obligations->flatMap(fn($o) => $o->obligationAmounts->pluck('id'));
        $histAdjustments  = ObligationAdjustment::whereIn('obligation_amounts_id', $histObrAmountIds)
            ->when($fromDate, fn($q) => $q->where('adjustment_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('adjustment_date', '<=', $toDate))
            ->get(['obligation_amounts_id', 'adjustment_amount'])
            ->groupBy('obligation_amounts_id')
            ->map(fn($items) => $items->sum('adjustment_amount'));

        $obligationAmountsData = [];
        foreach ($obligations as $obligation) {
            $total = $obligation->obligationAmounts->sum(function ($oa) use ($histAdjustments) {
                return $oa->obr_amount + ($histAdjustments[$oa->id] ?? 0);
            });
            if ($total > 0) {
                $obligationAmountsData[] = $total;
            }
        }

        $obligationRanges    = $this->buildObligationRanges($obligationAmountsData);
        $obligationsByQuarter = $this->buildObligationsByQuarter($appropriationIds, $selectedYear, $fromDate, $toDate);

        $office_allotment_classes = OfficeAllotmentClass::with([
            'offices', 'allotmentClass', 'fundSourceRelation', 'fund',
        ])->where('year', $selectedYear)->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Balances | Accounts'],
        ];

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