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
use App\Models\AccountCode;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class SAAOBCOController extends Controller
{
    public function index(Request $request)
        {
            $selectedYear = request('year1', date('Y'));
            $selectedOffice = request('office_filter');
            $selectedAccountCode = request('account_code');
            $asOfDate = request('as_of_filter', now()->toDateString());

            $allOffices = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Continuing');
                    });
            })->orderBy('id', 'asc')->get();

            $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();
            $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

            // Get account codes
            $usedAccountCodes = Appropriation::whereHas('officeAllotmentClass', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Continuing');
                    });
            })
            ->distinct()
            ->pluck('account_code');

            $accounts = AccountCode::whereIn('code', $usedAccountCodes)
                ->orderBy('code')
                ->pluck(DB::raw("CONCAT(code, ' - ', description)"), 'code');
            
            $officesQuery = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Continuing');
                    });
            });

            if (!empty($selectedOffice)) {
                $officesQuery->where('id', $selectedOffice);
            }
            
            $offices = $officesQuery->with([
                'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                    $query->where('year', $selectedYear)
                        ->whereHas('allotmentClass', function ($subQuery) {
                            $subQuery->where('category', 'Continuing');
                        });
                },
                'officeAllotmentClasses.allotmentClass',
                'officeAllotmentClasses.appropriations' => function ($query) use ($selectedAccountCode) {
                    if (!empty($selectedAccountCode)) {
                        $query->where('account_code', 'LIKE', $selectedAccountCode . '%');
                    }
                    $query->orderByRaw("CASE WHEN programs IS NULL OR programs = '' THEN 0 ELSE 1 END ASC")
                        ->orderBy('account_code', 'asc');
                },
                'officeAllotmentClasses.appropriations.realignments',
                'officeAllotmentClasses.appropriations.supplementals',
                'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
            ])->get();

            if (!empty($selectedAccountCode)) {
                $offices = $offices->filter(function($office) {
                    foreach ($office->officeAllotmentClasses as $oac) {
                        if ($oac->appropriations->isNotEmpty()) {
                            return true;
                        }
                    }
                    return false;
                })->values();
            }

            $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

            // Process each office with year and CCO year grouping
            foreach ($offices as $office) {
                $office->officeAllotmentClasses = $office->officeAllotmentClasses
                    ->filter(function($oac) {
                        return $oac->appropriations->isNotEmpty();
                    })
                    ->sortBy(fn ($oac) => $oac->allotmentClass->id)
                    ->values();

                // Initialize arrays
                $ccoYears = [];
                $appropriationsByYear = [];
                $yearlyTotals = [];
                $grandTotal = [
                    'appropriation' => 0,
                    'sb' => 0,
                    'rev' => 0,
                    'realignment' => 0,
                    'authorized' => 0,
                    'allotment' => 0,
                    'for_later_release' => 0,
                    'obligation' => 0,
                    'appropriation_balance' => 0,
                    'appropriation_accomplishment' => 0,
                    'allotment_balance' => 0,
                    'allotment_accomplishment' => 0,
                ];

                foreach ($office->officeAllotmentClasses as $oac) {
                    foreach ($oac->appropriations as $app) {
                        // Calculate supplementals, realignments, obligations
                        $sb = $app->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $rev = $app->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        $realignment = $app->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->reduce(fn ($carry, $r) =>
                                $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                        $obligationBase = $app->obligationAmounts
                            ->filter(fn ($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        $obligationAdjustments = $app->obligationAmounts
                            ->flatMap(fn ($oa) =>
                                $oa->obligation
                                    ? $oa->obligation->obligationAdjustments
                                        ->where('adjustment_date', '<=', $asOfDate)
                                        ->where('obligation_amounts_id', $oa->id)
                                    : collect()
                            )
                            ->sum('adjustment_amount');

                        $obligation = $obligationBase + $obligationAdjustments;

                        $authorized = $app->appropriation + $sb + $rev + $realignment;
                        $allotment = ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4) + $sb + $rev + $realignment;

                        $forLater = 0;
                        if ($currentQuarter < 2) $forLater += $app->quarter2;
                        if ($currentQuarter < 3) $forLater += $app->quarter3;
                        if ($currentQuarter < 4) $forLater += $app->quarter4;

                        $allotment -= $forLater;

                        $app->sb_appropriation = $sb;
                        $app->reversion = $rev;
                        $app->realignment = $realignment;
                        $app->obligation = $obligation;
                        $app->authorized_appropriation = $authorized;
                        $app->allotment = $allotment;
                        $app->for_later_release = $forLater;

                        $app->appropriation_balance = $authorized - $obligation;
                        $app->appropriation_accomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                        $app->allotment_balance = $allotment - $obligation;
                        $app->allotment_accomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

                        // Group by CCO year
                    $ccoYear = $app->cco_year;
                    
                    if (!in_array($ccoYear, $ccoYears)) {
                        $ccoYears[] = $ccoYear;
                        $appropriationsByYear[$ccoYear] = [];
                        $yearlyTotals[$ccoYear] = [
                            'appropriation' => 0,
                            'sb' => 0,
                            'rev' => 0,
                            'realignment' => 0,
                            'authorized' => 0,
                            'allotment' => 0,
                            'for_later_release' => 0,
                            'obligation' => 0,
                            'appropriation_balance' => 0,
                            'appropriation_accomplishment' => 0,
                            'allotment_balance' => 0,
                            'allotment_accomplishment' => 0,
                        ];
                    }

                    $appropriationsByYear[$ccoYear][] = $app;

                    // Add to yearly totals
                    $yearlyTotals[$ccoYear]['appropriation'] += $app->appropriation;
                    $yearlyTotals[$ccoYear]['sb'] += $app->sb_appropriation;
                    $yearlyTotals[$ccoYear]['rev'] += $app->reversion;
                    $yearlyTotals[$ccoYear]['realignment'] += $app->realignment;
                    $yearlyTotals[$ccoYear]['authorized'] += $app->authorized_appropriation;
                    $yearlyTotals[$ccoYear]['allotment'] += $app->allotment;
                    $yearlyTotals[$ccoYear]['for_later_release'] += $app->for_later_release;
                    $yearlyTotals[$ccoYear]['obligation'] += $app->obligation;
                    $yearlyTotals[$ccoYear]['appropriation_balance'] += $app->appropriation_balance;
                    $yearlyTotals[$ccoYear]['allotment_balance'] += $app->allotment_balance;
                    $yearlyTotals[$ccoYear]['appropriation_accomplishment'] += $app->appropriation_accomplishment;
                    $yearlyTotals[$ccoYear]['allotment_accomplishment'] += $app->allotment_accomplishment;

                    // Add to grand total
                    $grandTotal['appropriation'] += $app->appropriation;
                    $grandTotal['sb'] += $app->sb_appropriation;
                    $grandTotal['rev'] += $app->reversion;
                    $grandTotal['realignment'] += $app->realignment;
                    $grandTotal['authorized'] += $app->authorized_appropriation;
                    $grandTotal['allotment'] += $app->allotment;
                    $grandTotal['for_later_release'] += $app->for_later_release;
                    $grandTotal['obligation'] += $app->obligation;
                    $grandTotal['appropriation_balance'] += $app->appropriation_balance;
                    $grandTotal['allotment_balance'] += $app->allotment_balance;
                    $grandTotal['appropriation_accomplishment'] += $app->appropriation_accomplishment;
                    $grandTotal['allotment_accomplishment'] += $app->allotment_accomplishment;
                }
            }

                // Calculate averages for accomplishments per year
            $appCount = 0;
            foreach ($appropriationsByYear as $year => $apps) {
                $appCount += count($apps);
            }

            if ($appCount > 0) {
                foreach ($yearlyTotals as $year => $totals) {
                    $yearAppCount = count($appropriationsByYear[$year]);
                    $yearlyTotals[$year]['appropriation_accomplishment'] /= $yearAppCount ?: 1;
                    $yearlyTotals[$year]['allotment_accomplishment'] /= $yearAppCount ?: 1;
                }
                $grandTotal['appropriation_accomplishment'] /= $appCount;
                $grandTotal['allotment_accomplishment'] /= $appCount;
            }

            // Assign back to model at the end
            $office->ccoYears = $ccoYears;
            $office->appropriationsByYear = $appropriationsByYear;
            $office->yearlyTotals = $yearlyTotals;
            $office->grandTotal = $grandTotal;
        }

            // Calculate overall total if all offices are selected
            $overallTotal = null;
            if (empty($selectedOffice) && count($offices) > 0) {
                $overallTotal = [
                    'appropriation' => 0,
                    'sb' => 0,
                    'rev' => 0,
                    'realignment' => 0,
                    'authorized' => 0,
                    'allotment' => 0,
                    'for_later_release' => 0,
                    'obligation' => 0,
                    'appropriation_balance' => 0,
                    'appropriation_accomplishment' => 0,
                    'allotment_balance' => 0,
                    'allotment_accomplishment' => 0,
                ];

                $totalAppCount = 0;
                foreach ($offices as $office) {
                    $overallTotal['appropriation'] += $office->grandTotal['appropriation'];
                    $overallTotal['sb'] += $office->grandTotal['sb'];
                    $overallTotal['rev'] += $office->grandTotal['rev'];
                    $overallTotal['realignment'] += $office->grandTotal['realignment'];
                    $overallTotal['authorized'] += $office->grandTotal['authorized'];
                    $overallTotal['allotment'] += $office->grandTotal['allotment'];
                    $overallTotal['for_later_release'] += $office->grandTotal['for_later_release'];
                    $overallTotal['obligation'] += $office->grandTotal['obligation'];
                    $overallTotal['appropriation_balance'] += $office->grandTotal['appropriation_balance'];
                    $overallTotal['allotment_balance'] += $office->grandTotal['allotment_balance'];
                    $overallTotal['appropriation_accomplishment'] += $office->grandTotal['appropriation_accomplishment'];
                    $overallTotal['allotment_accomplishment'] += $office->grandTotal['allotment_accomplishment'];
                    
                    foreach ($office->appropriationsByYear as $apps) {
                        $totalAppCount += count($apps);
                    }
                }

                if ($totalAppCount > 0) {
                    $overallTotal['appropriation_accomplishment'] /= count($offices);
                    $overallTotal['allotment_accomplishment'] /= count($offices);
                }
            }

            return view('saaobco.index', compact('availableYears', 'offices', 'selectedYear', 'selectedOffice', 'selectedAccountCode', 'asOfDate', 'employees', 'allOffices', 'accounts', 'overallTotal'))
                ->with('status', session('status'));
        }



    public function exportExcel(Request $request)
        {
            $year = $request->input('year1');
            $officeId = $request->input('office_filter');
            $accountCode = $request->input('account_code');
            $asOf = $request->input('as_of_filter');
            $signatoryName = $request->input('signatory_name');
            $signatoryDesignation = $request->input('signatory_designation');

            // Get the office name by ID, or use "All_Offices" if none is selected
            $officeName = 'All_Offices';
            if (!empty($officeId)) {
                $office = Office::find($officeId);
                if ($office) {
                    $officeName = preg_replace('/[^A-Za-z0-9_]/', '_', $office->office_abbreviation); // sanitize filename
                }
            }

            // Get the account code display (code only, without description)
            $accountCodeName = '';
            if (!empty($accountCode)) {
                $accountCodeName = '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $accountCode); // sanitize filename
            }

            $fileName = 'SAAOB_' . $officeName . $accountCodeName . '_' . $year . '.xlsx';

            return Excel::download(new SAAOBCOExport(
                $year,
                $officeId,
                $accountCode,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}
