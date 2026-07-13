<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\SAAOBCOExport;
use App\Exports\SAAOBFundSourceExport;
use App\Models\Employee;
use App\Models\Fund;
use App\Models\FundSource;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\LogsActivity;

class SAAOBFundSourceController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
        {
            $selectedYear = request('year1', date('Y'));
            $selectedFundSource = request('fund_source_filter');
            $asOfDate = request('as_of_filter', now()->toDateString());

            $allFundSources = FundSource::select('category')->distinct()->orderBy('id')->pluck('category');
            // Get all employees for signatory filter
            $employees = Employee::where('office', '12')->orderBy('employee_id')->get();
            
            $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

            $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

            $fundSourcesQuery = FundSource::whereIn('source', function ($query) use ($selectedYear) {
                $query->select('fund_source')
                    ->from('office_allotment_classes')
                    ->where('year', $selectedYear)
                    ->distinct();
            })
            ->select('source', 'category')
            ->distinct()
            ->orderBy('category', 'desc');

            // Filter by selected category
            if ($selectedFundSource) {
                $fundSourcesQuery->where('category', $selectedFundSource);
            }

            $fundSources = $fundSourcesQuery->with(['officeAllotmentClasses' => function ($query) use ($selectedYear) {
                    $query->where('year', $selectedYear)
                        ->with([
                        'appropriations', // no sorting here
                        'appropriations.realignments',
                        'appropriations.supplementals',
                        'appropriations.obligationAmounts.obligation.obligationAdjustments'
                    ]);
                }
            ])
            ->get()
            ->groupBy('category')
            ->map(function ($sources, $category) use ($currentQuarter, $asOfDate, $selectedYear) {
                $fundSourceCodes = $sources->pluck('source');
            
                $fundsInUse = OfficeAllotmentClass::whereIn('fund_source', $fundSourceCodes)
                    ->where('year', $selectedYear)
                    ->pluck('fund')
                    ->unique();
                
                $fundRecords = Fund::whereIn('fund', $fundsInUse)
                    ->select('fund', 'fund_type')
                    ->get()
                    ->groupBy('fund_type')
                    ->map(function ($funds, $fundType) use ($sources, $currentQuarter, $asOfDate) {

                        $fundData = $funds->map(function ($fund) use ($sources, $currentQuarter, $asOfDate) {
                            // Filter OACs for this fund
                            $oacs = $sources->flatMap->officeAllotmentClasses
                                ->where('fund', $fund->fund);

                                $approved_appropriations = 0;
                                $sb_appropriations = 0;
                                $reversions = 0;
                                $realignments = 0;
                                $obligations = 0;
                                $allotment = 0;
                                $forLaterRelease = 0;
                                $authorized_appropriation = 0;

                            foreach ($oacs as $oac) {
                                foreach ($oac->appropriations as $app) {
                                    $approved_appropriations += $app->appropriation;
                                    // Supplemental & Reversion (filter by supplemental_date)
                                    $sb_appropriations += $app->supplementals
                                        ->where('type', 'Supplemental')
                                        ->where('supplemental_date', '<=', $asOfDate)
                                        ->sum('amount');

                                    $reversions += $app->supplementals
                                        ->where('type', 'Reversion')
                                        ->where('supplemental_date', '<=', $asOfDate)
                                        ->sum('amount') * -1;

                                    $sbForLater = $app->supplementals
                                        ->where('type', 'Supplemental')
                                        ->where('supplemental_date', '<=', $asOfDate)
                                        ->sum(function ($supp) use ($currentQuarter) {
                                            $fl = 0;
                                            if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                                            if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                                            if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                                            return $fl;
                                        });
                                    $forLaterRelease += $sbForLater;

                                    // Realignments (filter by realignment_date)
                                    $realignments += $app->realignments
                                        ->where('realignment_date', '<=', $asOfDate)
                                        ->reduce(fn($carry, $r) =>
                                            $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                                    // Obligations (obr_date <= asOfDate)
                                    $obligationBase = $app->obligationAmounts
                                        ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                                        ->sum('obr_amount');

                                    // --- Obligation Adjustments ---
                                    $obligationAdjustments = $app->obligationAmounts
                                    ->flatMap(fn ($oa) =>
                                        $oa->obligation
                                            ? $oa->obligation->obligationAdjustments
                                                ->where('adjustment_date', '<=', $asOfDate)
                                                ->where('obligation_amounts_id', $oa->id) // restrict per obligation_amount of this appropriation
                                            : collect()
                                    )
                                    ->sum('adjustment_amount');
                                    
                                    $obligations += $obligationBase + $obligationAdjustments;
                                    if ($currentQuarter < 2) $forLaterRelease += $app->quarter2 ?? 0;
                                    if ($currentQuarter < 3) $forLaterRelease += $app->quarter3 ?? 0;
                                    if ($currentQuarter < 4) $forLaterRelease += $app->quarter4 ?? 0;
                                    
                                }
                            }

                            $authorized_appropriation = $approved_appropriations + $sb_appropriations + $reversions + $realignments;
                            $allotment = $authorized_appropriation - $forLaterRelease;

                            return [
                                'fund' => $fund->fund,
                                'approved_appropriation' => $approved_appropriations,
                                'sb_appropriation' => $sb_appropriations,
                                'reversion' => $reversions,
                                'realignment' => $realignments,
                                'authorized' => $authorized_appropriation,
                                'obligation' => $obligations,
                                'allotment' => $authorized_appropriation - $forLaterRelease,
                                'for_later_release' => $forLaterRelease,
                                'appropriation_balance' => $authorized_appropriation - $obligations,
                                'appropriation_accomplishment' => $authorized_appropriation > 0 ? ($obligations / $authorized_appropriation) * 100 : 0,
                                'allotment_balance' => $allotment - $obligations,
                                'allotment_accomplishment' => $allotment > 0 ? ($obligations / $allotment) * 100 : 0

                            ];
                        });

                        $totals = [
                            'fund' => 'Total',
                            'approved_appropriation' => $fundData->sum('approved_appropriation'),
                            'sb_appropriation' => $fundData->sum('sb_appropriation'),
                            'reversion' => $fundData->sum('reversion'),
                            'realignment' => $fundData->sum('realignment'),
                            'authorized' => $fundData->sum('authorized'),
                            'obligation' => $fundData->sum('obligation'),
                            'allotment' => $fundData->sum('allotment'),
                            'for_later_release' => $fundData->sum('for_later_release'),
                            'appropriation_balance' => $fundData->sum('appropriation_balance'),
                            'appropriation_accomplishment' => $fundData->sum('authorized') > 0 
                                ? ($fundData->sum('obligation') / $fundData->sum('authorized')) * 100 
                                : 0,
                            'allotment_balance' => $fundData->sum('allotment_balance'),
                            'allotment_accomplishment' => $fundData->sum('allotment') > 0 
                                ? ($fundData->sum('obligation') / $fundData->sum('allotment')) * 100 
                                : 0,
                        ];

                        return [
                            'fund_type' => $fundType,
                            'funds' => $fundData->values(),
                            'totals' => $totals,
                        ];
                    })
                    ->values();

                $grandTotals = [
                    'approved_appropriation' => $fundRecords->sum(fn($ft) => $ft['totals']['approved_appropriation']),
                    'sb_appropriation' => $fundRecords->sum(fn($ft) => $ft['totals']['sb_appropriation']),
                    'reversion' => $fundRecords->sum(fn($ft) => $ft['totals']['reversion']),
                    'realignment' => $fundRecords->sum(fn($ft) => $ft['totals']['realignment']),
                    'authorized' => $fundRecords->sum(fn($ft) => $ft['totals']['authorized']),
                    'obligation' => $fundRecords->sum(fn($ft) => $ft['totals']['obligation']),
                    'allotment' => $fundRecords->sum(fn($ft) => $ft['totals']['allotment']),
                    'for_later_release' => $fundRecords->sum(fn($ft) => $ft['totals']['for_later_release']),
                    'appropriation_balance' => $fundRecords->sum(fn($ft) => $ft['totals']['appropriation_balance']),
                    'appropriation_accomplishment' => $fundRecords->sum(fn($ft) => $ft['totals']['authorized']) > 0
                        ? ($fundRecords->sum(fn($ft) => $ft['totals']['obligation']) / $fundRecords->sum(fn($ft) => $ft['totals']['authorized'])) * 100
                        : 0,
                    'allotment_balance' => $fundRecords->sum(fn($ft) => $ft['totals']['allotment_balance']),
                    'allotment_accomplishment' => $fundRecords->sum(fn($ft) => $ft['totals']['allotment']) > 0
                        ? ($fundRecords->sum(fn($ft) => $ft['totals']['obligation']) / $fundRecords->sum(fn($ft) => $ft['totals']['allotment'])) * 100
                        : 0,
                ];

                return [
                    'category' => $category,
                    'fund_types' => $fundRecords,
                    'grand_totals' => $grandTotals,
                ];
            })
            ->values();

            return view('saaobfundsource.index', compact('availableYears', 'selectedYear', 'selectedFundSource', 'asOfDate', 'employees', 'fundSources', 'allFundSources'))
                ->with('status', session('status'));
        }



    public function exportExcel(Request $request)
        {
            $year = $request->input('year1');
            $fundSource = $request->input('fund_source_filter');
            $asOf = $request->input('as_of_filter');
            $signatoryName = $request->input('signatory_name');
            $signatoryDesignation = $request->input('signatory_designation');

            $fundSourceName = 'All_Fund_Sources';
            if ($fundSource) {
                $fundSourceModel = FundSource::where('category', $fundSource)->first();
                if ($fundSourceModel) {
                    $fundSourceName = str_replace(' ', '_', $fundSourceModel->category);
                }
            }

            $fileName = 'SAAOB_' . $fundSourceName . '_' . $year . '.xlsx';

            // Log the excel report generation
            self::logExcelReportGeneration('SAAOB Fund Source Report', $fileName, [
                'Year' => $year,
                'Fund Source' => $fundSource ?? 'All',
                'As Of Date' => $asOf,
            ]);

            return Excel::download(new SAAOBFundSourceExport(
                $year,
                $fundSource,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}