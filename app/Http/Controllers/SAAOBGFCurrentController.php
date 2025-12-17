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
use App\Exports\SAAOBGFCurrentExport;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use App\Models\Fund;
use App\Models\Sector;
use Maatwebsite\Excel\Facades\Excel;

class SAAOBGFCurrentController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $asOfDate = request('as_of_filter', now()
            ->toDateString());

        $availableYears = OfficeAllotmentClass::select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $employees = Employee::where('office', '12')
            ->orderBy('employee_id')
            ->get();
        $allotmentClasses = AllotmentClass::all()
            ->keyBy('class');
        $offices = Office::orderBy('id')
            ->get();
        $sectors = Sector::orderBy('sector_code')
            ->get();

        $blankTotals = [
            'appropriation' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'for_later_release' => 0,
            'allotment' => 0,
            'obligations' => 0,
            'authorized_balance' => 0,
            'allotment_balance' => 0,
        ];

        $grandTotals = $blankTotals;

        foreach ($sectors as $sector) {
            $sector->offices = Office::whereHas('officeAllotmentClasses', function ($query) use ($sector, $selectedYear) {
                        $query->where('year', $selectedYear)
                            ->whereIn('fund', ['General Fund', 'Provincial Development Fund'])
                            ->whereHas('appropriations', function ($q) use ($sector) {
                                $q->where('fpp_code', 'like', $sector->sector_code . '%');
                            });
                    })
                ->with([
                    'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                        $query->where('year', $selectedYear)
                            ->whereHas('allotmentClass', function ($subQuery) {
                                $subQuery->where('category', 'Current');
                            })
                            ->whereIn('fund', ['General Fund', 'Provincial Development Fund'])
                            ->orderBy(
                            AllotmentClass::select('id')
                                ->whereColumn('allotment_classes.class', 'office_allotment_classes.class'),
                            'asc'
                        );
                    },
                    'officeAllotmentClasses.allotmentClass',
                    'officeAllotmentClasses.appropriations.supplementals',
                    'officeAllotmentClasses.appropriations.realignments',
                    'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
                ])
                ->orderBy('id')
                ->get();

                $month = Carbon::parse($asOfDate)->month;
                $currentQuarter = match(true) {
                    $month <= 3 => 1,
                    $month <= 6 => 2,
                    $month <= 9 => 3,
                    default => 4,
                };
            
            $sectorTotals = $blankTotals;

            foreach ($sector->offices as $office) {

                $officeTotals = $blankTotals;

                foreach ($office->officeAllotmentClasses as $key => $oac) {
                    // Filter appropriations for the current sector
                    $oacAppropriations = $oac->appropriations->filter(function ($appropriation) use ($sector) {
                        return str_starts_with($appropriation->fpp_code, $sector->sector_code);
                    });

                    // Skip this allotment class if all sums are zero
                    if ($oacAppropriations->isEmpty()) {
                        unset($office->officeAllotmentClasses[$key]);
                        continue;
                    }

                    // Approved Appropriation
                    $oac->appropriation = $oacAppropriations->sum('appropriation');

                    // Supplementals & Reversions (filter by supplemental_date)
                    $oac->sb_appropriation = $oacAppropriations
                        ->flatMap->supplementals
                        ->where('type', 'Supplemental')
                        ->where('supplemental_date', '<=', $asOfDate)
                        ->sum('amount');

                    $oac->reversion = $oacAppropriations
                        ->flatMap->supplementals
                        ->where('type', 'Reversion')
                        ->where('supplemental_date', '<=', $asOfDate)
                        ->sum('amount') * -1;

                    // Realignments (filter by realignment_date)
                    $oac->realignment = $oacAppropriations
                        ->flatMap->realignments
                        ->where('realignment_date', '<=', $asOfDate)
                        ->reduce(function ($carry, $r) {
                            return $carry + ($r->type === 'Source' ? -$r->amount : $r->amount);
                        }, 0);

                    // Authorized Appropriation
                    $oac->authorized_appropriation = 
                        $oac->appropriation + $oac->sb_appropriation + $oac->reversion + $oac->realignment;

                    // For Later Release (unchanged, still based on quarter)
                    $oac->for_later_release = 0;
                    if ($currentQuarter < 2) $oac->for_later_release += $oacAppropriations->sum('quarter2');
                    if ($currentQuarter < 3) $oac->for_later_release += $oacAppropriations->sum('quarter3');
                    if ($currentQuarter < 4) $oac->for_later_release += $oacAppropriations->sum('quarter4');

                    // Allotment
                    $oac->allotment = $oac->authorized_appropriation - $oac->for_later_release;

                    // Obligations (filter by obr_date)
                    $obligationBase = $oacAppropriations
                        ->flatMap->obligationAmounts
                        ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                        ->sum('obr_amount');

                    // --- Obligation Adjustments (filter by adjustment_date and appropriation) ---
                    $obligationAdjustments = $oacAppropriations
                        ->flatMap->obligationAmounts
                        ->flatMap(fn($oa) => $oa->obligation
                            ? $oa->obligation->obligationAdjustments
                                ->where('adjustment_date', '<=', $asOfDate)
                                ->where('obligation_amounts_id', $oa->id) // ✅ ensure adjustment belongs to this appropriation's OA
                            : collect()
                        )
                        ->sum('adjustment_amount');
                    $oac->obligations = $obligationBase + $obligationAdjustments;
                    // Authorized Appropriation Balance
                    $oac->authorized_balance = $oac->authorized_appropriation - $oac->obligations;
                    // Authorized Appropriation Accomplishment
                    $oac->authorized_accomplishment = $oac->authorized_appropriation > 0 ? ($oac->obligations / $oac->authorized_appropriation) * 100 : 0;
                    // Allotment Balance
                    $oac->allotment_balance = $oac->allotment - $oac->obligations;
                    // Allotment Accomplishment
                    $oac->allotment_accomplishment = $oac->allotment > 0 ? ($oac->obligations / $oac->allotment) * 100 : 0;

                    // --- Add to OFFICE total ---
                        $officeTotals['appropriation'] += $oac->appropriation;
                        $officeTotals['sb_appropriation'] += $oac->sb_appropriation;
                        $officeTotals['reversion'] += $oac->reversion;
                        $officeTotals['realignment'] += $oac->realignment;
                        $officeTotals['authorized_appropriation'] += $oac->authorized_appropriation;
                        $officeTotals['for_later_release'] += $oac->for_later_release;
                        $officeTotals['allotment'] += $oac->allotment;
                        $officeTotals['obligations'] += $oac->obligations;
                        $officeTotals['authorized_balance'] += $oac->authorized_balance;
                        $officeTotals['allotment_balance'] += $oac->allotment_balance;
                    }

                    // Compute averages for office
                    $officeTotals['authorized_accomplishment'] = $officeTotals['authorized_appropriation'] > 0
                        ? ($officeTotals['obligations'] / $officeTotals['authorized_appropriation']) * 100
                        : 0;
                    $officeTotals['allotment_accomplishment'] = $officeTotals['allotment'] > 0
                        ? ($officeTotals['obligations'] / $officeTotals['allotment']) * 100
                        : 0;

                    $office->totals = $officeTotals;

                    // --- Add office total to SECTOR total ---
                    foreach ($blankTotals as $key => $_) {
                        $sectorTotals[$key] += $officeTotals[$key];
                    }
                }

                // Compute averages for sector
                $sectorTotals['authorized_accomplishment'] = $sectorTotals['authorized_appropriation'] > 0
                    ? ($sectorTotals['obligations'] / $sectorTotals['authorized_appropriation']) * 100
                    : 0;
                $sectorTotals['allotment_accomplishment'] = $sectorTotals['allotment'] > 0
                    ? ($sectorTotals['obligations'] / $sectorTotals['allotment']) * 100
                    : 0;

                $sector->totals = $sectorTotals;

                // --- Add sector total to GRAND total ---
                foreach ($blankTotals as $key => $_) {
                    $grandTotals[$key] += $sectorTotals[$key];
                }
            }

            // Compute averages for grand total
            $grandTotals['authorized_accomplishment'] = $grandTotals['authorized_appropriation'] > 0
                ? ($grandTotals['obligations'] / $grandTotals['authorized_appropriation']) * 100
                : 0;
            $grandTotals['allotment_accomplishment'] = $grandTotals['allotment'] > 0
                ? ($grandTotals['obligations'] / $grandTotals['allotment']) * 100
                : 0;

        return view('saaobgfcurrent.index', compact('offices', 'sectors', 'employees', 'availableYears', 'selectedYear', 'asOfDate', 'allotmentClasses', 'grandTotals'));
    }

    public function exportExcel(Request $request)
        {
            $year = $request->input('year1');
            $asOf = $request->input('as_of_filter');
            $signatoryName = $request->input('signatory_name');
            $signatoryDesignation = $request->input('signatory_designation');


            $fileName = 'SAAOB_GF_Current_' . $year . '.xlsx';

            return Excel::download(new SAAOBGFCurrentExport(
                $year,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}