<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\SAAOBFundSectorExport;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use App\Models\Fund;
use App\Models\Sector;
use Maatwebsite\Excel\Facades\Excel;

class SAAODBAllFundsController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $asOfDate = request('as_of_filter', now()->toDateString());

        $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();
        $sectors = Sector::all();
        $fundsQuery = Fund::orderBy('id');
        $allotmentClasses = AllotmentClass::all()->keyBy('class');

        $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

        $funds = $fundsQuery->with([
            'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->with([
                        'allotmentClass',
                        'fundSourceRelation',
                        'appropriations.supplementals',
                        'appropriations.realignments',
                        'appropriations.obligationAmounts.obligation.obligationAdjustments',
                    ]);
            }
        ])->get();

        // --- Helper function (must be defined before it's used)
        function computeTotals($classes)
        {
            $totals = [
                'approved_appropriation' => 0,
                'supplemental' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'obligation' => 0,
                'authorized_appropriation_balance' => 0,
                'percent_obligated_to_authorized' => 0,
                'disbursement' => 0,
                'percent_disbursed_to_obligated' => 0,
                'percent_disbursed_to_authorized' => 0,
                'obligation_balance' => 0,
            ];

            foreach ($classes as $class) {
                $totals['approved_appropriation'] += $class->approved_appropriation;
                $totals['supplemental'] += $class->supplemental;
                $totals['reversion'] += $class->reversion;
                $totals['realignment'] += $class->realignment;
                $totals['authorized_appropriation'] += $class->authorized_appropriation;
                $totals['allotment'] += $class->allotment;
                $totals['obligation'] += $class->obligation;
                $totals['authorized_appropriation_balance'] += $class->authorized_appropriation_balance;
                $totals['disbursement'] += $class->disbursement;
                $totals['obligation_balance'] += $class->obligation_balance;
            }

            // Derived % fields
            $totals['percent_obligated_to_authorized'] =
                $totals['authorized_appropriation'] > 0
                    ? ($totals['obligation'] / $totals['authorized_appropriation']) * 100
                    : 0;

            $totals['percent_disbursed_to_obligated'] =
                $totals['obligation'] > 0
                    ? ($totals['disbursement'] / $totals['obligation']) * 100
                    : 0;

            $totals['percent_disbursed_to_authorized'] =
                $totals['authorized_appropriation'] > 0
                    ? ($totals['disbursement'] / $totals['authorized_appropriation']) * 100
                    : 0;

            return $totals;
        }

        // --- Main computation
        foreach ($funds as $fund) {
            $officeAllotmentClasses = $fund->officeAllotmentClasses
                ->filter(fn($oac) => $oac->fund === $fund->fund)
                ->reject(fn($oac) => optional($oac->allotmentClass)->class === 'CCO');

            $groupedByAllotmentClass = $officeAllotmentClasses->groupBy(fn($oac) => $oac->allotmentClass->class ?? 'Unknown');

            $uniqueAllotmentClasses = collect();
            $supplementalClasses = collect();

            foreach ($groupedByAllotmentClass as $className => $oacGroup) {
                $allotmentClass = $oacGroup->first()->allotmentClass;
                if (!$allotmentClass) continue;

                // --- Approved Appropriation ---
                $approvedAppropriation = $oacGroup->flatMap->appropriations->sum('appropriation');

                // --- Supplementals ---
                $supplemental = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->supplementals
                    ->where('type', 'Supplemental')
                    ->where('supplemental_date', '<=', $asOfDate)
                    ->sum('amount');

                // --- Reversions ---
                $reversion = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->supplementals
                    ->where('type', 'Reversion')
                    ->where('supplemental_date', '<=', $asOfDate)
                    ->sum('amount') * -1;

                // --- Realignments ---
                $realignment = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->realignments
                    ->where('realignment_date', '<=', $asOfDate)
                    ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

                // --- Authorized Appropriation ---
                $authorizedAppropriation = $approvedAppropriation + $supplemental + $reversion + $realignment;

                // --- Allotment ---
                $allotment = $oacGroup
                    ->flatMap->appropriations
                    ->sum(fn($a) => ($a->quarter1 ?? 0) + ($a->quarter2 ?? 0) + ($a->quarter3 ?? 0) + ($a->quarter4 ?? 0))
                    + $supplemental + $reversion + $realignment;

                // --- For Later Release ---
                $forLaterRelease = $oacGroup
                    ->flatMap->appropriations
                    ->sum(fn($a) => $a->for_later_release ?? 0);

                $allotment -= $forLaterRelease;

                // --- Obligations ---
                $obligationBase = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                    ->sum('obr_amount');

                // --- Obligation Adjustments ---
                $obligationAdjustments = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->flatMap(fn($oa) =>
                        $oa->obligation
                            ? $oa->obligation->obligationAdjustments
                                ->where('adjustment_date', '<=', $asOfDate)
                                ->where('obligation_amounts_id', $oa->id)
                            : collect()
                    )
                    ->sum('adjustment_amount');

                $obligation = $obligationBase + $obligationAdjustments;

                // --- Balances and percentages ---
                $authorizedAppropriationBalance = $authorizedAppropriation - $obligation;

                $percentObligatedToAuthorized = $authorizedAppropriation > 0
                    ? ($obligation / $authorizedAppropriation) * 100
                    : 0;

                $disbursement = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                    ->sum('disbursement_amount');

                $percentDisbursedToObligated = $obligation > 0
                    ? ($disbursement / $obligation) * 100
                    : 0;

                $percentDisbursedToAuthorized = $authorizedAppropriation > 0
                    ? ($disbursement / $authorizedAppropriation) * 100
                    : 0;

                $obligationBalance = $obligation - $disbursement;

                $classObject = (object)[
                    'id' => $allotmentClass->id,
                    'class' => $allotmentClass->class,
                    'approved_appropriation' => $approvedAppropriation,
                    'supplemental' => $supplemental,
                    'reversion' => $reversion,
                    'realignment' => $realignment,
                    'authorized_appropriation' => $authorizedAppropriation,
                    'allotment' => $allotment,
                    'for_later_release' => $forLaterRelease,
                    'obligation' => $obligation,
                    'authorized_appropriation_balance' => $authorizedAppropriationBalance,
                    'percent_obligated_to_authorized' => $percentObligatedToAuthorized,
                    'disbursement' => $disbursement,
                    'percent_disbursed_to_obligated' => $percentDisbursedToObligated,
                    'percent_disbursed_to_authorized' => $percentDisbursedToAuthorized,
                    'obligation_balance' => $obligationBalance,
                ];

                $uniqueAllotmentClasses->push($classObject);

                if ($supplemental > 0) {
                    $supplementalClasses->push($classObject);
                }
            }

            // Assign computed totals to the fund
            $fund->uniqueAllotmentClasses = $uniqueAllotmentClasses->values();
            $fund->uniqueSupplementalAllotmentClasses = $supplementalClasses->values();
            $fund->regularBudgetTotals = (object) computeTotals($uniqueAllotmentClasses);
            $fund->supplementalBudgetTotals = (object) computeTotals($supplementalClasses);
        }

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('saaodballfunds.index', compact(
            'availableYears',
            'selectedYear',
            'asOfDate',
            'employees',
            'funds',
        ))->with('status', session('status'));
    }

    public function exportExcel(Request $request)
        {
            $year = $request->input('year1');
            $fund = $request->input('fund_filter');
            $asOf = $request->input('as_of_filter');
            $signatoryName = $request->input('signatory_name');
            $signatoryDesignation = $request->input('signatory_designation');

           // Sanitize fund name for filename
            $fundName = 'All_Funds';

            if (!empty($fund)) {
                if ($fund === 'others') {
                    $fundName = 'BEGHEE_SEF';
                } else {
                    $fundName = preg_replace('/[^A-Za-z0-9_]/', '_', $fund);
                }
            }

            $fileName = 'SAAOB_' . $fundName . '_' . $year . '.xlsx';

            return Excel::download(new SAAOBFundSectorExport(
                $year,
                $fund,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}
