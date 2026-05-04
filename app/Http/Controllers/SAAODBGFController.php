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
use App\Exports\SAAODBAllFundsExport;
use App\Exports\SAAODBGFExport;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use App\Models\Fund;
use App\Models\Sector;
use App\Models\Disbursement;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\LogsActivity;

class SAAODBGFController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $asOfDate = request('as_of_filter', now()->toDateString());

        // Get all employees for signatory filter
        $employees = Employee::where('office', '10')
            ->orderBy('employee_id')
            ->get(['employee_id', 'name', 'designation']);

        $sectors = Sector::all();
        $officeQuery = Office::where('fund', 'General Fund')->orderBy('id');
        $allotmentClasses = AllotmentClass::all()->keyBy('class');
        $allAllotmentClasses = AllotmentClass::all();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

       $offices = $officeQuery->with([
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

        // --- Helper function for totals
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
                foreach ($totals as $key => $value) {
                    if (isset($class->$key)) {
                        $totals[$key] += $class->$key;
                    }
                }
            }

            // Derived percentages
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
        foreach ($offices as $office) {
            $officeAllotmentClasses = $office->officeAllotmentClasses
                ->filter(fn($oac) => $oac->office === $office->id);

            // Group by allotment class
            $groupedByAllotmentClass = $officeAllotmentClasses->groupBy(
                fn($oac) => $oac->allotmentClass->class ?? 'Unknown'
            );

            $allotmentClasses = collect();

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
                $forLaterRelease = 0;
                if ($currentQuarter < 2) $forLaterRelease += $oacGroup->flatMap->appropriations->sum(fn($a) => ($a->quarter2 ?? 0));
                if ($currentQuarter < 3) $forLaterRelease += $oacGroup->flatMap->appropriations->sum(fn($a) => ($a->quarter3 ?? 0));
                if ($currentQuarter < 4) $forLaterRelease += $oacGroup->flatMap->appropriations->sum(fn($a) => ($a->quarter4 ?? 0));

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

                // --- Disbursements ---
                // Get all obligation_amounts_ids for this allotment class group
                $obligationAmountIds = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->pluck('id')
                    ->toArray();

                $disbursement = Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds)
                    ->where('disbursement_date', '<=', $asOfDate)
                    ->sum('disbursement_amount');

                // --- Balances and percentages ---
                $authorizedAppropriationBalance = $authorizedAppropriation - $obligation;
                $obligationBalance = $obligation - $disbursement;

                $percentObligatedToAuthorized = $authorizedAppropriation > 0
                    ? ($obligation / $authorizedAppropriation) * 100
                    : 0;

                $percentDisbursedToObligated = $obligation > 0
                    ? ($disbursement / $obligation) * 100
                    : 0;

                $percentDisbursedToAuthorized = $authorizedAppropriation > 0
                    ? ($disbursement / $authorizedAppropriation) * 100
                    : 0;

                // --- Collect final class summary ---
                $allotmentClasses->push((object)[
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
                ]);
            }

            // Assign computed results under each office
            $office->allotmentClasses = $allotmentClasses->values();
            $office->totals = (object) computeTotals($allotmentClasses);

            // --- Group totals by category ---
            $currentClasses = $allotmentClasses->filter(fn($c) => !str_contains(strtoupper($c->class), 'CCO'));
            $continuingClasses = $allotmentClasses->filter(fn($c) => str_contains(strtoupper($c->class), 'CCO'));

            $office->total_current = (object) computeTotals($currentClasses);
            $office->total_continuing = (object) computeTotals($continuingClasses);

            // Combine all for grand total
            $office->total_overall = (object) computeTotals($allotmentClasses);

            // Ensure default totals always exist even if empty
            foreach (['total_current', 'total_continuing', 'total_overall'] as $key) {
                if (!isset($office->$key) || !$office->$key) {
                    $office->$key = (object)[
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
                }
            }
        }

        // Grand Total
        $grandTotal = (object)[
            'approved_appropriation' => 0,
            'supplemental' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'obligation' => 0,
            'authorized_appropriation_balance' => 0,
            'percent_obligated_to_authorized' => 0, // will compute later
            'disbursement' => 0,
            'percent_disbursed_to_obligated' => 0, // will compute later
            'percent_disbursed_to_authorized' => 0, // will compute later
            'obligation_balance' => 0,
        ];

        // Sum up totals from each office
        foreach ($offices as $office) {
            foreach ($grandTotal as $key => $value) {
                if (property_exists($office->total_overall, $key) && !str_starts_with($key, 'percent')) {
                    $grandTotal->$key += $office->total_overall->$key ?? 0;
                }
            }
        }

        // Compute percentage-based fields
        $grandTotal->percent_obligated_to_authorized = $grandTotal->authorized_appropriation > 0
            ? ($grandTotal->obligation / $grandTotal->authorized_appropriation) * 100
            : 0;

        $grandTotal->percent_disbursed_to_obligated = $grandTotal->obligation > 0
            ? ($grandTotal->disbursement / $grandTotal->obligation) * 100
            : 0;

        $grandTotal->percent_disbursed_to_authorized = $grandTotal->authorized_appropriation > 0
            ? ($grandTotal->disbursement / $grandTotal->authorized_appropriation) * 100
            : 0;

        // Now attach it (optional)
        $grandTotals = $grandTotal;

        // --- SUMMARY TOTALS PER ALLOTMENT CLASS ---
        $summaryTotals = [];

        $grandSummary = [
            'total_appropriation' => 0,
            'total_obligations' => 0,
            'total_disbursements' => 0,
            'percent_obligation_vs_authorized' => 0,
            'percent_disbursement_vs_authorized' => 0,
            'percent_disbursement_vs_obligation' => 0,
        ];

        foreach ($allAllotmentClasses as $allotmentClass) {
            $className = $allotmentClass->class;
            $totals = [
                'total_appropriation' => 0,
                'total_obligations' => 0,
                'total_disbursements' => 0,
                'percent_obligation_vs_authorized' => 0,
                'percent_disbursement_vs_authorized' => 0,
                'percent_disbursement_vs_obligation' => 0,
            ];

        foreach ($offices as $office) {
            foreach ($office->allotmentClasses as $class) {
                if (strtoupper(trim($class->class)) === strtoupper(trim($className))) {
                    $totals['total_appropriation'] += $class->authorized_appropriation ?? 0;
                    $totals['total_obligations'] += $class->obligation ?? 0;
                    $totals['total_disbursements'] += $class->disbursement ?? 0;
                }
            }
        }

        // Compute percentages
        $totals['percent_obligation_vs_authorized'] =
            $totals['total_appropriation'] > 0
                ? ($totals['total_obligations'] / $totals['total_appropriation']) * 100
                : 0;

        $totals['percent_disbursement_vs_authorized'] =
            $totals['total_appropriation'] > 0
                ? ($totals['total_disbursements'] / $totals['total_appropriation']) * 100
                : 0;

        $totals['percent_disbursement_vs_obligation'] =
            $totals['total_obligations'] > 0
                ? ($totals['total_disbursements'] / $totals['total_obligations']) * 100
                : 0;

        $summaryTotals[$className] = $totals;

        // Add to grand summary
        $grandSummary['total_appropriation'] += $totals['total_appropriation'];
        $grandSummary['total_obligations'] += $totals['total_obligations'];
        $grandSummary['total_disbursements'] += $totals['total_disbursements'];
    }

    // Compute overall percentages
    $grandSummary['percent_obligation_vs_authorized'] =
        $grandSummary['total_appropriation'] > 0
            ? ($grandSummary['total_obligations'] / $grandSummary['total_appropriation']) * 100
            : 0;

    $grandSummary['percent_disbursement_vs_authorized'] =
        $grandSummary['total_appropriation'] > 0
            ? ($grandSummary['total_disbursements'] / $grandSummary['total_appropriation']) * 100
            : 0;

    $grandSummary['percent_disbursement_vs_obligation'] =
        $grandSummary['total_obligations'] > 0
            ? ($grandSummary['total_disbursements'] / $grandSummary['total_obligations']) * 100
            : 0;

        return view('saaodbgf.index', compact(
            'availableYears',
            'selectedYear',
            'asOfDate',
            'employees',
            'offices',
            'grandTotals',
            'allAllotmentClasses',
            'summaryTotals',
            'grandSummary'
        ))->with('status', session('status'));
    }

public function exportExcel(Request $request)
    {
        $year = $request->input('year1');
        $asOf = $request->input('as_of_filter');
        $preparedSignatoryName = $request->input('prepared_signatory_name');
        $preparedSignatoryDesignation = $request->input('prepared_signatory_designation');
        $certifiedSignatoryName = $request->input('certified_signatory_name');
        $certifiedSignatoryDesignation = $request->input('certified_signatory_designation');

        $fileName = 'SAAODB_' . 'GF_' . $year . '.xlsx';

        // Log the excel report generation
        self::logExcelReportGeneration('SAAODB General Fund Report', $fileName, [
            'Year' => $year,
            'As Of Date' => $asOf,
        ]);

         return Excel::download(new SAAODBGFExport(
            $year,
            $asOf,
            $preparedSignatoryName,
            $preparedSignatoryDesignation,
            $certifiedSignatoryName,
            $certifiedSignatoryDesignation
        ), $fileName);
    }
}
