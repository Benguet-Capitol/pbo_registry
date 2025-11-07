<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\SAAOBExport;
use App\Models\Employee;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class RAOController extends Controller
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

        // Initialize variables
        $appropriations = collect();
        $obligations = collect();
        $appropriationData = [];
        $totalAppropriations = 0;
        $totalSupplemental = 0;
        $totalReversions = 0;
        $totalRealignments = 0;
        $grandTotal = 0;
        $totalQuarter1 = 0;
        $totalQuarter2 = 0;
        $totalQuarter3 = 0;
        $totalQuarter4 = 0;
        $quarterTotals = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarterTotals[$q] = [
                'released'     => 0,
                'supplemental' => 0,
                'reversion'    => 0,
                'realignment'  => 0,
            ];
        }
        
        if ($selectedOfficeAllotmentClass) {
            $appropriations = Appropriation::where('office_allotment_class_id', $selectedOfficeAllotmentClass)
                ->orderBy('account_code')
                ->orderBy('description')
                ->get();

            // Calculate all appropriation-related data
            $totalAppropriations = $appropriations->sum('appropriation');

            foreach ($appropriations as $appropriation) {
                // Calculate supplemental appropriations
                $supplementalAmount = $appropriation->supplementals()
                    ->where('type', 'Supplemental')
                    ->where('supplemental_date', '<=', $asOfDate)
                    ->sum('amount');
                
                // Calculate reversions
                $reversionAmount = $appropriation->supplementals()
                    ->where('type', 'Reversion')
                    ->where('supplemental_date', '<=', $asOfDate)
                    ->sum('amount') * -1;
                
                // Calculate realignments
                $realignmentAmount = $appropriation->realignments()
                    ->where('realignment_date', '<=', $asOfDate)
                    ->sum(DB::raw("CASE WHEN type = 'Recipient' THEN amount WHEN type = 'Source' THEN -amount ELSE 0 END"));
                
                // Get quarter values
                $quarter1 = $appropriation->quarter1 ?? 0;
                $quarter2 = $appropriation->quarter2 ?? 0;
                $quarter3 = $appropriation->quarter3 ?? 0;
                $quarter4 = $appropriation->quarter4 ?? 0;
                
                // Calculate released appropriation (allotment) = sum of all quarters
                $releasedAppropriation = $quarter1 + $quarter2 + $quarter3 + $quarter4;
                
                // Calculate total for this appropriation
                $totalForThisAppropriation = $appropriation->appropriation + $supplementalAmount + $reversionAmount + $realignmentAmount;
                
                // Store data for this appropriation
                $appropriationData[$appropriation->id] = [
                    'appropriation' => $appropriation->appropriation,
                    'supplemental' => $supplementalAmount,
                    'reversion' => $reversionAmount,
                    'realignment' => $realignmentAmount,
                    'total' => $totalForThisAppropriation,
                    'quarter1' => $quarter1,
                    'quarter2' => $quarter2,
                    'quarter3' => $quarter3,
                    'quarter4' => $quarter4,
                    'released_appropriation' => $releasedAppropriation,
                ];
                
                // Add to totals
                $totalSupplemental += $supplementalAmount;
                $totalReversions += $reversionAmount;
                $totalRealignments += $realignmentAmount;
                $totalQuarter1 += $quarter1;
                $totalQuarter2 += $quarter2;
                $totalQuarter3 += $quarter3;
                $totalQuarter4 += $quarter4;
            }
            
            $grandTotal = $totalAppropriations + $totalSupplemental + $totalReversions + $totalRealignments;

            // Get obligations for this office allotment class
            $obligations = Obligation::with(['obligationAmounts.appropriation'])
                ->whereHas('obligationAmounts.appropriation', function ($query) use ($selectedOfficeAllotmentClass) {
                    $query->where('office_allotment_class_id', $selectedOfficeAllotmentClass);
                })
                ->where('obr_date', '<=', $asOfDate)
                ->orderBy('obr_date')
                ->orderBy('obr_no')
                ->get();
        }

        // Get all employees for signatory filter
        $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('rao.index', compact(
            'availableYears', 
            'selectedYear', 
            'selectedOffice', 
            'asOfDate', 
            'employees', 
            'officeAllotmentClasses', 
            'appropriations', 
            'obligations',
            'appropriationData',
            'totalAppropriations',
            'totalSupplemental',
            'totalReversions',
            'totalRealignments',
            'grandTotal',
            'totalQuarter1',
            'totalQuarter2',
            'totalQuarter3',
            'totalQuarter4'
        ))->with('status', session('status'));
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

            return Excel::download(new SAAOBExport(
                $year,
                $officeId,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}
