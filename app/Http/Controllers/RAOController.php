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

        // Get calculated data
        $calculatedData = $this->getCalculatedData($selectedYear, $selectedOfficeAllotmentClass, $asOfDate);

        // Get all employees for signatory filter
        $employees = Employee::where('office', '12')->orderBy('employee_id')->get();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('rao.index', array_merge([
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'selectedOffice' => $selectedOffice,
            'asOfDate' => $asOfDate,
            'employees' => $employees,
            'officeAllotmentClasses' => $officeAllotmentClasses,
        ], $calculatedData))->with('status', session('status'));
    }

    private function getCalculatedData($selectedYear, $selectedOfficeAllotmentClass, $asOfDate)
    {
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
        
        // Initialize quarterly adjustments with details
        $quarterlyAdjustments = [];
        $quarterlyObligations = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarterlyAdjustments[$q] = [
                'supplementals' => collect(),
                'reversions' => collect(),
                'realignments' => collect(),
            ];
            $quarterlyObligations[$q] = collect();
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
                
                // Get quarterly adjustments with details
                $quarterlyData = [];
                for ($q = 1; $q <= 4; $q++) {
                    // Get quarter date range
                    $quarterStart = date('Y-m-d', strtotime("$selectedYear-" . (($q - 1) * 3 + 1) . "-01"));
                    $quarterEnd = date('Y-m-t', strtotime("$selectedYear-" . ($q * 3) . "-01"));
                    
                    // Get supplementals for this quarter with details
                    $qSupplementals = $appropriation->supplementals()
                        ->where('type', 'Supplemental')
                        ->whereBetween('supplemental_date', [$quarterStart, min($quarterEnd, $asOfDate)])
                        ->get();
                    
                    // Get reversions for this quarter with details
                    $qReversions = $appropriation->supplementals()
                        ->where('type', 'Reversion')
                        ->whereBetween('supplemental_date', [$quarterStart, min($quarterEnd, $asOfDate)])
                        ->get();
                    
                    // Get realignments for this quarter with details
                    $qRealignments = $appropriation->realignments()
                        ->whereBetween('realignment_date', [$quarterStart, min($quarterEnd, $asOfDate)])
                        ->get();
                    
                    $quarterlyData[$q] = [
                        'supplementals' => $qSupplementals,
                        'reversions' => $qReversions,
                        'realignments' => $qRealignments,
                    ];
                    
                    // Add to quarterly totals for overall tracking
                    foreach ($qSupplementals as $supp) {
                        $quarterlyAdjustments[$q]['supplementals']->push([
                            'appropriation_id' => $appropriation->id,
                            'reference' => $supp->supplemental_no ?? 'N/A',
                            'date' => $supp->supplemental_date,
                            'amount' => $supp->amount,
                        ]);
                    }
                    
                    foreach ($qReversions as $rev) {
                        $quarterlyAdjustments[$q]['reversions']->push([
                            'appropriation_id' => $appropriation->id,
                            'reference' => $rev->supplemental_no ?? 'N/A',
                            'date' => $rev->supplemental_date,
                            'amount' => $rev->amount * -1,
                        ]);
                    }
                    
                    foreach ($qRealignments as $real) {
                        $adjustmentAmount = $real->type == 'Recipient' ? $real->amount : -$real->amount;
                        $quarterlyAdjustments[$q]['realignments']->push([
                            'appropriation_id' => $appropriation->id,
                            'reference' => $real->realignment_no ?? 'N/A',
                            'date' => $real->realignment_date,
                            'type' => $real->type,
                            'amount' => $adjustmentAmount,
                        ]);
                    }
                }
                
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
                    'quarterly_adjustments' => $quarterlyData,
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

            // Get obligation adjustments for this office allotment class
            $obligationAdjustments = ObligationAdjustment::with(['obligation', 'obligationAmount.appropriation'])
                ->whereHas('obligationAmount.appropriation', function ($query) use ($selectedOfficeAllotmentClass) {
                    $query->where('office_allotment_class_id', $selectedOfficeAllotmentClass);
                })
                ->where('adjustment_date', '<=', $asOfDate)
                ->orderBy('adjustment_date')
                ->get();

            // Group obligations by quarter
            foreach ($obligations as $obligation) {
                $obrDate = \Carbon\Carbon::parse($obligation->obr_date);
                $quarter = $obrDate->quarter;
                
                // Only include if in selected year
                if ($obrDate->year == $selectedYear) {
                    // Calculate amounts per appropriation for this obligation
                    $obligationAmountsByAppropriationId = [];
                    $totalObligationAmount = 0;
                    
                    // Check if obligationAmounts relationship exists and has data
                    if ($obligation->obligationAmounts) {
                        foreach ($obligation->obligationAmounts as $obligationAmount) {
                            // Check if appropriation belongs to selected office allotment class
                            if ($obligationAmount->appropriation && 
                                $obligationAmount->appropriation->office_allotment_class_id == $selectedOfficeAllotmentClass) {
                                
                                $appId = $obligationAmount->appropriation_id;
                                $amount = floatval($obligationAmount->obr_amount ?? 0);
                                
                                if (!isset($obligationAmountsByAppropriationId[$appId])) {
                                    $obligationAmountsByAppropriationId[$appId] = 0;
                                }
                                $obligationAmountsByAppropriationId[$appId] += $amount;
                                $totalObligationAmount += $amount;
                            }
                        }
                    }
                    
                    // Add obligation
                    $quarterlyObligations[$quarter]->push([
                        'type' => 'obligation',
                        'date' => $obligation->obr_date,
                        'obr_no' => $obligation->obr_no,
                        'obr_date' => $obligation->obr_date,
                        'particulars' => $obligation->particulars ?? '',
                        'total_amount' => $totalObligationAmount,
                        'amounts_by_appropriation' => $obligationAmountsByAppropriationId,
                    ]);
                }
            }

            // Group obligation adjustments by quarter
            foreach ($obligationAdjustments as $adjustment) {
                $adjustmentDate = \Carbon\Carbon::parse($adjustment->adjustment_date);
                $quarter = $adjustmentDate->quarter;
                
                // Only include if in selected year
                if ($adjustmentDate->year == $selectedYear) {
                    // Get appropriation through obligationAmount relationship
                    $appropriation = $adjustment->obligationAmount && $adjustment->obligationAmount->appropriation 
                        ? $adjustment->obligationAmount->appropriation 
                        : null;
                    
                    if ($appropriation && $appropriation->office_allotment_class_id == $selectedOfficeAllotmentClass) {
                        $appId = $appropriation->id;
                        $amount = floatval($adjustment->adjustment_amount ?? 0);
                        
                        $adjustmentAmountsByAppropriationId = [];
                        if ($appId) {
                            $adjustmentAmountsByAppropriationId[$appId] = $amount;
                        }
                        
                        // Add adjustment
                        $quarterlyObligations[$quarter]->push([
                            'type' => 'adjustment',
                            'date' => $adjustment->adjustment_date,
                            'adjustment_date' => $adjustment->adjustment_date,
                            'obr_no' => $adjustment->obligation ? $adjustment->obligation->obr_no : 'N/A',
                            'particulars' => $adjustment->adjustment_remarks ?? '',
                            'total_amount' => $amount,
                            'amounts_by_appropriation' => $adjustmentAmountsByAppropriationId,
                        ]);
                    }
                }
            }

            // Sort each quarter's obligations and adjustments by date
            foreach ($quarterlyObligations as $quarter => $items) {
                $quarterlyObligations[$quarter] = $items->sortBy('date')->values();
            }
        }

        return [
            'appropriations' => $appropriations,
            'obligations' => $obligations,
            'appropriationData' => $appropriationData,
            'totalAppropriations' => $totalAppropriations,
            'totalSupplemental' => $totalSupplemental,
            'totalReversions' => $totalReversions,
            'totalRealignments' => $totalRealignments,
            'grandTotal' => $grandTotal,
            'totalQuarter1' => $totalQuarter1,
            'totalQuarter2' => $totalQuarter2,
            'totalQuarter3' => $totalQuarter3,
            'totalQuarter4' => $totalQuarter4,
            'quarterlyAdjustments' => $quarterlyAdjustments,
            'quarterlyObligations' => $quarterlyObligations,
        ];
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

        // Get pre-calculated data
        $calculatedData = $this->getCalculatedData($year, $officeAllotmentClassId, $asOf);

        // Sanitize filename - remove special characters
        $officeAbbr = preg_replace('/[^A-Za-z0-9_]/', '_', $officeAllotmentClass->offices->office_abbreviation);
        $className = preg_replace('/[^A-Za-z0-9_]/', '_', $officeAllotmentClass->allotmentClass->class);
        
        $fileName = 'RAO_' . $officeAbbr . '-' . $className . '_' . $year . '.xlsx';

        return Excel::download(new RAOExport(
            $year,
            $officeAllotmentClass,
            $asOf,
            $signatoryName,
            $signatoryDesignation,
            $calculatedData
        ), $fileName);
    }
}