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
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class SAAOBCOController extends Controller
{
    public function index(Request $request)
        {
            $selectedYear = request('year1', date('Y'));
            $selectedOffice = request('office_filter');
            $asOfDate = request('as_of_filter', now()->toDateString());

            $allOffices = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Continuing');
                    });
            })->orderBy('id', 'asc')->get();
            // Get all employees for signatory filter
            $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();
            
            $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

            // Only include offices with OfficeAllotmentClasses for the selected year and “Current” category
            $officesQuery = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Continuing');
                    });
            });

            // If a specific office is selected, filter it
            if (!empty($selectedOffice)) {
                $officesQuery->where('id', $selectedOffice);
            }
            // Get all offices with their OfficeAllotmentClasses and related data
            $offices = $officesQuery->with([
                'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                    $query->where('year', $selectedYear)
                        ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Continuing');
                    });
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
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

            foreach ($offices as $office) {
                // Display OfficeAllotmentClasses for selected year only
                $office->filteredOfficeAllotmentClasses = $office->officeAllotmentClasses
                    ->where('year', $selectedYear)
                    ->sortBy(fn ($oac) => $oac->allotmentClass->id)
                    ->values();

                $ccoYears = collect();
                $appropriationsByYear = [];

                foreach ($office->officeAllotmentClasses as $oac) {
                    foreach ($oac->appropriations as $app) {
                        if (!empty($app->cco_year)) {
                            $ccoYear = $app->cco_year;
                            $ccoYears->push($ccoYear);

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
                                ->reduce(fn ($carry, $r) =>
                                    $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                            // --- Obligations ---
                            $obligationBase = $app->obligationAmounts
                                ->filter(fn ($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
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

                            $obligation = $obligationBase + $obligationAdjustments;

                            $authorized = $app->appropriation + $sb + $rev + $realignment;

                            $allotment = ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4)
                                        + $sb + $rev + $realignment;

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
                            $app->appropriation_accomplishment = $authorized > 0
                                ? ($obligation / $authorized) * 100 : 0;
                            $app->allotment_balance = $allotment - $obligation;
                            $app->allotment_accomplishment = $allotment > 0
                                ? ($obligation / $allotment) * 100 : 0;

                            if (!isset($appropriationsByYear[$ccoYear])) {
                                $appropriationsByYear[$ccoYear] = [];
                            }

                            $appropriationsByYear[$ccoYear][] = $app;
                        }
                    }
                }

                $office->ccoYears = $ccoYears->unique()->sort()->values();
                $office->appropriationsByYear = $appropriationsByYear;

                // ---- Compute totals per cco_year ----
                $yearlyTotals = [];

                foreach ($office->appropriationsByYear as $ccoYear => $apps) {
                    $totals = [
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

                    foreach ($apps as $app) {
                        $totals['appropriation'] += $app->appropriation;
                        $totals['sb'] += $app->sb_appropriation;
                        $totals['rev'] += $app->reversion;
                        $totals['realignment'] += $app->realignment;
                        $totals['authorized'] += $app->authorized_appropriation;
                        $totals['allotment'] += $app->allotment;
                        $totals['for_later_release'] += $app->for_later_release;
                        $totals['obligation'] += $app->obligation;
                        $totals['appropriation_balance'] += $app->appropriation_balance;
                        $totals['allotment_balance'] += $app->allotment_balance;
                    }

                    // Compute percentages
                    $totals['appropriation_accomplishment'] = $totals['authorized'] > 0
                        ? ($totals['obligation'] / $totals['authorized']) * 100 : 0;

                    $totals['allotment_accomplishment'] = $totals['allotment'] > 0
                        ? ($totals['obligation'] / $totals['allotment']) * 100 : 0;

                    $yearlyTotals[$ccoYear] = $totals;
                }

                $office->yearlyTotals = $yearlyTotals;

                $office->grandTotal = [
                    'appropriation' => collect($office->yearlyTotals)->sum('appropriation'),
                    'sb' => collect($office->yearlyTotals)->sum('sb'),
                    'rev' => collect($office->yearlyTotals)->sum('rev'),
                    'realignment' => collect($office->yearlyTotals)->sum('realignment'),
                    'authorized' => collect($office->yearlyTotals)->sum('authorized'),
                    'allotment' => collect($office->yearlyTotals)->sum('allotment'),
                    'for_later_release' => collect($office->yearlyTotals)->sum('for_later_release'),
                    'obligation' => collect($office->yearlyTotals)->sum('obligation'),
                    'appropriation_balance' => collect($office->yearlyTotals)->sum('appropriation_balance'),
                    'appropriation_accomplishment' => collect($office->yearlyTotals)->avg('appropriation_accomplishment'),
                    'allotment_balance' => collect($office->yearlyTotals)->sum('allotment_balance'),
                    'allotment_accomplishment' => collect($office->yearlyTotals)->avg('allotment_accomplishment'),
                ];

            }
            return view('saaobco.index', compact('availableYears', 'offices', 'allOffices', 'selectedYear', 'selectedOffice', 'asOfDate', 'employees', 'currentQuarter', 'ccoYears'))
                ->with('status', session('status'));
        }



    public function exportExcel(Request $request)
        {
            $year = $request->input('year1');
            $officeId = $request->input('office_filter');
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

            $fileName = 'SAAOB_' . $officeName . '_' . $year . '.xlsx';

            return Excel::download(new SAAOBCOExport(
                $year,
                $officeId,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}
