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
use App\Models\Employee;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class NDDController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $selectedOffice = request('office_filter');
        $asOfDate = request('as_of_filter', now()->toDateString());
        $selectedOfficeAllotmentClass = request('office_allotment_class_filter');

        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->orderBy('office', 'asc')
            ->get();

        // Get all employees for signatory filter
        $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('ndd.index', compact(
            'availableYears', 
            'selectedYear', 
            'selectedOffice', 
            'asOfDate', 
            'employees', 
            'officeAllotmentClasses'
        ))->with('status', session('status'));
    }

    public function exportExcel(Request $request)
    {
        $year = $request->input('year1');
        $officeAllotmentClassId = $request->input('office_allotment_class_filter');
        $asOf = $request->input('as_of_filter');
        $signatoryName = $request->input('signatory_name');
        $signatoryDesignation = $request->input('signatory_designation');

        // Require office allotment class selection
        if (empty($officeAllotmentClassId)) {
            return back()->with('error', 'Please select an Office Allotment Class to export the report.');
        }

        // Get the office and allotment class name
        $officeAllotmentClass = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->find($officeAllotmentClassId);
        
        if (!$officeAllotmentClass) {
            return back()->with('error', 'Office Allotment Class not found.');
        }

        // Sanitize filename - remove special characters
        $officeAbbr = preg_replace('/[^A-Za-z0-9_]/', '_', $officeAllotmentClass->offices->office_abbreviation);
        $className = preg_replace('/[^A-Za-z0-9_]/', '_', $officeAllotmentClass->allotmentClass->class);
        
        $fileName = 'RAO_' . $officeAbbr . '-' . $className . '_' . $year . '.xlsx';

        return Excel::download(new RAOExport(
            $year,
            $officeAllotmentClassId,
            $asOf,
            $signatoryName,
            $signatoryDesignation
        ), $fileName);
    }
}
