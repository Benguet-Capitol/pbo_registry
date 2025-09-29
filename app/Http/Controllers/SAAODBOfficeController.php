<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\SAAODBExport;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class SAAODBOfficeController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $selectedOffice = request('office_filter');
        $asOfDate = request('as_of_filter', now()->toDateString());

        $allOffices = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
            $query->where('year', $selectedYear);
        })->orderBy('id', 'asc')->get();

        // Get all employees for signatory filter
        $employees = Employee::where('office', 'PAccO')
            ->orderBy('employee_id')
            ->get(['employee_id', 'name', 'designation']);

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        // Only include offices with OfficeAllotmentClasses for the selected year and “Current” category
        $officesQuery = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
            $query->where('year', $selectedYear);
        });

        // If a specific office is selected, filter it
        if (!empty($selectedOffice)) {
            $officesQuery->where('id', $selectedOffice);
        }

        // Get all offices with their OfficeAllotmentClasses and related data
        $offices = $officesQuery->with([
            'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear);
            },
            'officeAllotmentClasses.allotmentClass',
            'officeAllotmentClasses.appropriations' => function ($query) {
                $query->orderByRaw("CASE WHEN programs IS NULL OR programs = '' THEN 0 ELSE 1 END ASC")
                    ->orderBy('account_code', 'asc');
            },
            'officeAllotmentClasses.appropriations.realignments',
            'officeAllotmentClasses.appropriations.supplementals',
            'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
        ])->get();

        $month = Carbon::parse($asOfDate)->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        foreach ($offices as $office) {
            $office->officeAllotmentClasses = $office->officeAllotmentClasses
                ->sortBy(fn($oac) => $oac->allotmentClass->id)
                ->values();

            foreach ($office->officeAllotmentClasses as $oac) {
                $grouped = $oac->appropriations
                    ->sortBy(fn($a) => [$a->programs === null ? 0 : 1, $a->account_code])
                    ->groupBy(fn($a) => $a->programs ?? '');

                foreach ($grouped as $program => $appropriations) {
                    foreach ($appropriations as $app) {

                        // --- Supplementals ---
                        $sb = $app->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $rev = $app->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        // --- Realignments ---
                        $realignment = $app->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->reduce(fn($carry, $r) =>
                            $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                        // --- Obligations ---
                        $obligationBase = $app->obligationAmounts
                            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        // --- Obligation Adjustments ---
                        $obligationAdjustments = $app->obligationAmounts
                            ->flatMap(
                                fn($oa) =>
                                $oa->obligation
                                    ? $oa->obligation->obligationAdjustments
                                    ->where('adjustment_date', '<=', $asOfDate)
                                    ->where('obligation_amounts_id', $oa->id) // restrict per obligation_amount of this appropriation
                                    : collect()
                            )
                            ->sum('adjustment_amount');

                        // --- Disbursements ---
                        $disbursement = $app->disbursements
                            ->where('disbursement_date', '<=', $asOfDate)
                            ->sum('disbursement_amount');

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
                        $app->disbursement = $disbursement;
                        $app->disbursement_balance = $obligation - $disbursement;
                        $app->disbursement_to_obligation = $obligation > 0 ? ($disbursement / $obligation) * 100 : 0;
                        $app->disbursement_to_appropriation = $authorized > 0 ? ($disbursement / $authorized) * 100 : 0;
                    }
                }

                // Subtotals per program
                $subtotals = [];
                foreach ($grouped as $program => $apps) {
                    if ($program === '') continue;

                    $subtotal = $this->computeTotals($apps);
                    // Set accomplishment fields as requested
                    $subtotal['appropriation_accomplishment'] = ($subtotal['authorized_appropriation'] > 0)
                        ? ($subtotal['obligation'] / $subtotal['authorized_appropriation']) * 100
                        : 0;
                    $subtotals[$program] = $subtotal;
                }

                // Grand Total (includes appropriations without programs and subtotals)
                $total = $this->computeTotals($grouped->get('') ?? collect());
                foreach ($subtotals as $sub) {
                    foreach ($sub as $key => $val) {
                        if ($key !== 'count') $total[$key] += $val;
                    }
                    $total['count'] += $sub['count'];
                }


                // Use correct accomplishment logic for totals
                $total['appropriation_accomplishment'] = ($total['authorized_appropriation'] > 0)
                    ? ($total['obligation'] / $total['authorized_appropriation']) * 100
                    : 0;

                $oac->groupedAppropriations = $grouped;
                $oac->groupSubtotals = $subtotals;
                $oac->groupTotal = $total;
            }

            // Grand Total for Office
            $gt = $this->computeOfficeTotal($office->officeAllotmentClasses);
            $gt['appropriation_accomplishment'] = ($gt['authorized_appropriation'] > 0)
                ? ($gt['obligation'] / $gt['authorized_appropriation']) * 100
                : 0;
            $office->grandTotal = $gt;

            // COE and COE + CO Totals
            $coeClasses = [
                'PS',
                'PERSONAL SERVICES',
                'MOOE',
                'MAINTENANCE AND OTHER OPERATING EXPENDITURES',
                'FE',
                'FINANCIAL EXPENSES'
            ];
            $coClasses = ['CO', 'CAPITAL OUTLAY'];

            // COE OACs
            $coeOacs = $office->officeAllotmentClasses
                ->filter(fn($oac) => in_array(strtoupper($oac->allotmentClass->description), $coeClasses));

            // CO OACs
            $coOacs = $office->officeAllotmentClasses
                ->filter(fn($oac) => in_array(strtoupper($oac->allotmentClass->description), $coClasses));

            // --- COE Totals ---
            $officeCOETotals = array_fill_keys(array_keys($office->grandTotal), 0);

            foreach ($coeOacs as $oac) {
                foreach ($oac->groupTotal as $key => $val) {
                    $officeCOETotals[$key] += $val;
                }
            }

            $officeCOETotals['appropriation_accomplishment'] =
                ($officeCOETotals['authorized_appropriation'] > 0)
                ? ($officeCOETotals['obligation'] / $officeCOETotals['authorized_appropriation']) * 100
                : 0;

            unset($officeCOETotals['count']);
            $office->officeCOETotals = $officeCOETotals;


            // --- CO Totals ---
            $officeCOTotals = array_fill_keys(array_keys($office->grandTotal), 0);

            foreach ($coOacs as $oac) {
                foreach ($oac->groupTotal as $key => $val) {
                    $officeCOTotals[$key] += $val;
                }
            }

            $officeCOTotals['appropriation_accomplishment'] =
                ($officeCOTotals['authorized_appropriation'] > 0)
                ? ($officeCOTotals['obligation'] / $officeCOTotals['authorized_appropriation']) * 100
                : 0;

            unset($officeCOTotals['count']);
            $office->officeCOTotals = $officeCOTotals;


            // --- COE + CO Totals ---
            $officeCOECoTotals = array_fill_keys(array_keys($office->grandTotal), 0);

            foreach ($officeCOECoTotals as $key => $_) {
                $officeCOECoTotals[$key] =
                    ($officeCOETotals[$key] ?? 0) + ($officeCOTotals[$key] ?? 0);
            }

            $officeCOECoTotals['appropriation_accomplishment'] =
                ($officeCOECoTotals['authorized_appropriation'] > 0)
                ? ($officeCOECoTotals['obligation'] / $officeCOECoTotals['authorized_appropriation']) * 100
                : 0;

            unset($officeCOECoTotals['count']);
            $office->officeCOECoTotals = $officeCOECoTotals;
        }

        return view('saaodboffice.index', compact('availableYears', 'allOffices', 'offices', 'selectedYear', 'selectedOffice', 'asOfDate', 'employees', 'officesQuery'))
            ->with('status', session('status'));
    }

    private function computeTotals($appropriations)
    {
        $total = [
            'appropriation' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'obligation' => 0,
            'appropriation_balance' => 0,
            'appropriation_accomplishment' => 0,
            'disbursement' => 0,
            'disbursement_to_obligation' => 0,
            'disbursement_to_appropriation' => 0,
            'disbursement_balance' => 0,
            'count' => 0,
        ];

        foreach ($appropriations as $app) {
            foreach ($total as $key => $val) {
                if ($key !== 'count') $total[$key] += $app->$key ?? 0;
            }
            $total['count']++;
        }

        $total['appropriation_accomplishment'] = $total['count'] > 0
            ? $total['appropriation_accomplishment'] / $total['count']
            : 0;

        $total['disbursement_to_obligation'] = $total['obligation'] > 0
            ? ($total['disbursement'] / $total['obligation']) * 100
            : 0;

        $total['disbursement_to_appropriation'] = $total['authorized_appropriation'] > 0
            ? ($total['disbursement'] / $total['authorized_appropriation']) * 100
            : 0;

        return $total;
    }

    private function computeOfficeTotal($oacs)
    {
        $officeTotal = [
            'appropriation' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'obligation' => 0,
            'appropriation_balance' => 0,
            'appropriation_accomplishment' => 0,
            'disbursement' => 0,
            'disbursement_to_obligation' => 0,
            'disbursement_to_appropriation' => 0,
            'disbursement_balance' => 0,
            'count' => 0,
        ];

        foreach ($oacs as $oac) {
            foreach ($officeTotal as $key => $val) {
                if ($key !== 'count') $officeTotal[$key] += $oac->groupTotal[$key] ?? 0;
            }
            $officeTotal['count'] += $oac->groupTotal['count'] ?? 0;
        }

        $officeTotal['appropriation_accomplishment'] = $officeTotal['count'] > 0
            ? $officeTotal['appropriation_accomplishment'] / $officeTotal['count']
            : 0;

        $officeTotal['disbursement_to_obligation'] = $officeTotal['obligation'] > 0
            ? ($officeTotal['disbursement'] / $officeTotal['obligation']) * 100
            : 0;

        $officeTotal['disbursement_to_appropriation'] = $officeTotal['authorized_appropriation'] > 0
            ? ($officeTotal['disbursement'] / $officeTotal['authorized_appropriation']) * 100
            : 0;

        return $officeTotal;
    }

    public function exportExcel(Request $request)
    {
        $year = $request->input('year1');
        $officeId = $request->input('office_filter');
        $asOf = $request->input('as_of_filter');
        $preparedSignatoryName = $request->input('prepared_signatory_name');
        $preparedSignatoryDesignation = $request->input('prepared_signatory_designation');
        $certifiedSignatoryName = $request->input('certified_signatory_name');
        $certifiedSignatoryDesignation = $request->input('certified_signatory_designation');

        // Get the office name by ID, or use "All_Offices" if none is selected
        $officeName = 'All_Offices';
        if (!empty($officeId)) {
            $office = Office::find($officeId);
            if ($office) {
                $officeName = preg_replace('/[^A-Za-z0-9_]/', '_', $office->office_abbreviation); // sanitize filename
            }
        }

        $fileName = 'SAAODB_' . $officeName . '_' . $year . '.xlsx';

        return Excel::download(new SAAODBExport(
            $year,
            $officeId,
            $asOf,
            $preparedSignatoryName,
            $preparedSignatoryDesignation,
            $certifiedSignatoryName,
            $certifiedSignatoryDesignation
        ), $fileName);
    }
}
