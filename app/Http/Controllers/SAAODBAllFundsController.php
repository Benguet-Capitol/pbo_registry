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
        $fundsQuery = Fund::whereNot('id', 3)
            ->orderBy('fund');
        $allotmentClasses = AllotmentClass::all()->keyBy('class');

        $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

        $funds = $fundsQuery->with([
            'officeAllotmentClasses' => fn($query) => $query->where('year', $selectedYear),
            'officeAllotmentClasses.allotmentClass',
            'officeAllotmentClasses.fundSourceRelation',
            'officeAllotmentClasses.appropriations',
            'officeAllotmentClasses.appropriations.supplementals',
            'officeAllotmentClasses.appropriations.realignments',
            'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
        ])->get();

        foreach ($funds as $fund) {
            // Get all related office allotment classes of this fund
            $officeAllotmentClasses = $fund->officeAllotmentClasses;

            // Get unique allotment classes (excluding ID 5)
            $uniqueAllotmentClasses = $officeAllotmentClasses
                ->pluck('allotmentClass')
                ->filter()
                ->unique('id')
                ->reject(fn($ac) => $ac->id == 5)
                ->values();

            $fund->uniqueAllotmentClasses = $uniqueAllotmentClasses;

            foreach ($uniqueAllotmentClasses as $allotmentClass) {
                // Gather all appropriations under this fund and this allotment class
                $appropriations = $officeAllotmentClasses
                    ->where('allotment_class_id', $allotmentClass->id)
                    ->flatMap->appropriations;

                // ✅ Get total approved appropriations
                $approvedAppropriation = $appropriations->sum('appropriation');

                // Store it for Blade
                $allotmentClass->approved_appropriation = $approvedAppropriation;
            }
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
