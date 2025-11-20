<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\RAOExport;
use App\Models\AccountCode;
use App\Models\Employee;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class AccountsSummaryController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $asOfDate = request('as_of_filter', now()->toDateString());

        // Get all employees for signatory filter
        $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('summaryaccounts.index', compact(
            'availableYears', 
            'selectedYear',
            'asOfDate',
            'employees'
        ))->with('status', session('status'));
    }

    /* public function exportExcel(Request $request)
    {
        $year = $request->input('year1');
        $asOf = $request->input('as_of_filter');
        $signatoryName = $request->input('signatory_name');
        $signatoryDesignation = $request->input('signatory_designation');

        
        $fileName = 'RAO_' .  '_' . $year . '.xlsx';

        return Excel::download(new RAOExport(
            $year,
            $officeAllotmentClassId,
            $asOf,
            $signatoryName,
            $signatoryDesignation
        ), $fileName);
    } */
}
