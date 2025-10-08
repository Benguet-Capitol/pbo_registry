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

        foreach ($funds as $fund) {
    // 🔹 Filter OACs that truly belong to this fund
    $officeAllotmentClasses = $fund->officeAllotmentClasses
        ->filter(fn($oac) => $oac->fund === $fund->fund)
        ->reject(fn($oac) => optional($oac->allotmentClass)->class === 'CCO'); // exclude CCO

    // 🔹 Group by allotment class (not by id, since some may be null)
    $groupedByAllotmentClass = $officeAllotmentClasses->groupBy(fn($oac) => $oac->allotmentClass->class ?? 'Unknown');

    $uniqueAllotmentClasses = collect();

    foreach ($groupedByAllotmentClass as $className => $oacGroup) {
        $allotmentClass = $oacGroup->first()->allotmentClass;
        if (!$allotmentClass) continue;

        // --- Approved Appropriation ---
        $approvedAppropriation = $oacGroup
            ->flatMap->appropriations
            ->sum('appropriation');

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
            ->sum(function ($appropriation) {
                return ($appropriation->quarter1 ?? 0)
                    + ($appropriation->quarter2 ?? 0)
                    + ($appropriation->quarter3 ?? 0)
                    + ($appropriation->quarter4 ?? 0);
            })
            + $supplemental + $reversion + $realignment;

        // --- For Later Release ---
        $forLaterRelease = $oacGroup
            ->flatMap->appropriations
            ->sum(function ($appropriation) use ($currentQuarter) {
                return $appropriation->for_later_release ?? (
                    ($currentQuarter < 2 ? ($appropriation->quarter2 ?? 0) : 0) +
                    ($currentQuarter < 3 ? ($appropriation->quarter3 ?? 0) : 0) +
                    ($currentQuarter < 4 ? ($appropriation->quarter4 ?? 0) : 0)
                );
            });

        $allotment -= $forLaterRelease;

        // --- Obligations (filter by obr_date) ---
        $obligationBase = $oacGroup
            ->flatMap->appropriations
            ->flatMap->obligationAmounts
            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
            ->sum('obr_amount');
        
        // --- Obligation Adjustments (filter by adjustment_date) ---
        $obligationAdjustments = $oacGroup
            ->flatMap->appropriations
            ->flatMap->obligationAmounts
            ->flatMap(fn($oa) =>
                $oa->obligation
                    ? $oa->obligation->obligationAdjustments
                        ->where('adjustment_date', '<=', $asOfDate)
                        ->where('obligation_amounts_id', $oa->id) // restrict per obligation_amount
                    : collect()
            )
            ->sum('adjustment_amount');
        
        // Obligation
        $obligation = $obligationBase + $obligationAdjustments;


        // --- Assign computed fields ---
        $uniqueAllotmentClasses->push((object)[
            'id' => $allotmentClass->id,
            'class' => $allotmentClass->class,
            'approved_appropriation' => $approvedAppropriation,
            'supplemental' => $supplemental,
            'reversion' => $reversion,
            'realignment' => $realignment,
            'authorized_appropriation' => $authorizedAppropriation,
            'allotment' => $allotment,
            'for_later_release' => $forLaterRelease,
            'obligationBase' => $obligationBase,
            'obligationAdjustments' => $obligationAdjustments,
            'obligation' => $obligation,
        ]);
    }

    $fund->uniqueAllotmentClasses = $uniqueAllotmentClasses->values();
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
