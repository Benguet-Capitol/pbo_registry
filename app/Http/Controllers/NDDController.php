<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\NDDExport;
use App\Models\Employee;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use App\Models\PurchaseOrder;
use App\Models\Disbursement;
use App\Models\ObligationAmount;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\LogsActivity;

class NDDController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $selectedOffice = request('office_filter');
        $asOfDate = request('as_of_filter', now()->toDateString());

        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->whereIn('class', ['MOOE', 'CO', 'CCO'])
            ->orderBy('office', 'asc')
            ->get();

        // Get all offices for the filter
        $offices = Office::orderBy('id')->get();

        // Get all employees for signatory filter
        $employees = Employee::where('office', '12')->orderBy('employee_id')->get();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        // Fetch obligations based on criteria
        $obligationsData = $this->getObligations($selectedYear, $selectedOffice, $asOfDate);
        $obligations = $obligationsData['obligations'];
        $totals = $obligationsData['totals'];

        return view('ndd.index', compact(
            'availableYears', 
            'selectedYear', 
            'selectedOffice', 
            'asOfDate', 
            'employees', 
            'officeAllotmentClasses',
            'offices',
            'obligations',
            'totals'
        ))->with('status', session('status'));
    }

    private function getObligations($year, $officeId, $asOfDate)
    {
        $query = Obligation::with([
            'disbursements',
            'obligationAdjustments',
            'obligationAmounts',
            'officeAllotmentClass.offices',
            'officeAllotmentClass.allotmentClass'
        ])
        ->where('obr_type', 'Purchase Request')
        ->whereYear('obr_date', $year)
        ->where('obr_date', '<=', $asOfDate)
        ->join('office_allotment_classes', 'obligations.office_allotment_class_id', '=', 'office_allotment_classes.id')
        ->join('offices', 'office_allotment_classes.office', '=', 'offices.id')
        ->join('allotment_classes', 'office_allotment_classes.class', '=', 'allotment_classes.class')
        ->whereIn('allotment_classes.class', ['MOOE', 'CO', 'CCO'])
        ->orderBy('offices.id', 'asc')
        ->orderBy('obligations.obr_date', 'asc')
        ->select('obligations.*');

        // Filter by office if selected
        if ($officeId) {
            $query->where('office_allotment_classes.office', $officeId);
        }

        $obligations = $query->get()->filter(function ($obligation) {
            // Calculate obligation balance
            $obrTotal = $obligation->obligationAmounts->sum('obr_amount');
            $adjustmentTotal = $obligation->obligationAdjustments->sum('adjustment_amount');
            $obligationBalance = $obrTotal + $adjustmentTotal;
            
            // Exclude obligations with zero balance
            if ($obligationBalance == 0) {
                return false;
            }
            
            // Get disbursements for this obligation
            $disbursements = $obligation->disbursements;
            
            // Case 1: No disbursements at all
            if ($disbursements->isEmpty()) {
                return true; // Include obligations without disbursements
            }
            
            // Case 2: Has disbursements but at least one is Partial Payment
            $hasPartialPayment = $disbursements->contains(function ($disbursement) {
                return $disbursement->status === 'Partial Payment';
            });
            
            return $hasPartialPayment;
        });

        $results = collect();

        foreach ($obligations as $obligation) {
            // Calculate obligation balance
            $obrTotal = $obligation->obligationAmounts->sum('obr_amount');
            $adjustmentTotal = $obligation->obligationAdjustments->sum('adjustment_amount');
            $obligationBalance = $obrTotal + $adjustmentTotal;

            // Get office information
            $office = $obligation->officeAllotmentClass?->offices;
            $officeName = $office ? $office->office_name : 'Unknown Office';
            $officeAbbr = $office ? $office->office_abbreviation : 'N/A';
            $officeId = $office ? $office->id : 999999;

            // Get OBR number from Obligation
            $obrNumber = $obligation->obr_no ?? '';
            
            // Get allotment class
            $allotmentClass = $obligation->officeAllotmentClass?->allotmentClass?->class ?? '';
            
            // Format budget control number with allotment class: MOOE-431-01-25-100
            $budgetControlNo = $allotmentClass ? $allotmentClass . '-' . $obrNumber : $obrNumber;

            // Remarks - leave blank for now
            $remarks = '';

            // Default payee from obligation
            $defaultPayee = $obligation->payee ?? $obligation->particulars ?? 'N/A';

            if ($obligation->obr_type === 'Purchase Request') {
                // Get all Purchase Orders related to this obligation's ObligationAmounts
                $obligationAmountIds = $obligation->obligationAmounts->pluck('id');
                
                if ($obligationAmountIds->isNotEmpty()) {
                    $purchaseOrders = PurchaseOrder::whereIn('obligation_amounts_id', $obligationAmountIds)
                        ->orderBy('po_date', 'asc')
                        ->get();
                    
                    if ($purchaseOrders->isNotEmpty()) {
                        // If there are Purchase Orders, create a row for each PO
                        foreach ($purchaseOrders as $po) {
                            // Use supplier from Purchase Order, fallback to default payee
                            $poPayee = $po->supplier ?? $defaultPayee;
                            $poDateFormatted = $po->po_date ? Carbon::parse($po->po_date)->format('m/d/Y') : '';

                            $results->push([
                                'office_id' => $officeId,
                                'office_name' => $officeName,
                                'office_abbr' => $officeAbbr,
                                'payee' => $poPayee,
                                'budget_control_no' => $budgetControlNo,
                                'po_number' => $po->po_number ?? '',
                                'po_date' => $poDateFormatted,
                                'po_date_sort' => $po->po_date ? Carbon::parse($po->po_date)->format('Y-m-d') : '',
                                'obr_date_sort' => $obligation->obr_date ? Carbon::parse($obligation->obr_date)->format('Y-m-d') : '',
                                'amount' => number_format($po->po_amount ?? 0, 2),
                                'remarks' => $remarks,
                                'obligation_id' => $obligation->id,
                                'obligation_balance' => $obligationBalance
                            ]);
                        }
                    } else {
                        // No Purchase Orders yet, show obligation with total obr_amount
                        $totalObrAmount = $obligation->obligationAmounts->sum('obr_amount');
                        
                        $results->push([
                            'office_id' => $officeId,
                            'office_name' => $officeName,
                            'office_abbr' => $officeAbbr,
                            'payee' => $defaultPayee,
                            'budget_control_no' => $budgetControlNo,
                            'po_number' => '',
                            'po_date' => '',
                            'po_date_sort' => '',
                            'obr_date_sort' => $obligation->obr_date ? Carbon::parse($obligation->obr_date)->format('Y-m-d') : '',
                            'amount' => number_format($totalObrAmount, 2),
                            'remarks' => $remarks,
                            'obligation_id' => $obligation->id,
                            'obligation_balance' => $obligationBalance
                        ]);
                    }
                }
            } else {
                // Not a Purchase Request, display as normal
                $totalObrAmount = $obligation->obligationAmounts->sum('obr_amount');
                
                $results->push([
                    'office_id' => $officeId,
                    'office_name' => $officeName,
                    'office_abbr' => $officeAbbr,
                    'payee' => $defaultPayee,
                    'budget_control_no' => $budgetControlNo,
                    'po_number' => '',
                    'po_date' => '',
                    'po_date_sort' => '',
                    'obr_date_sort' => $obligation->obr_date ? Carbon::parse($obligation->obr_date)->format('Y-m-d') : '',
                    'amount' => number_format($totalObrAmount, 2),
                    'remarks' => $remarks,
                    'obligation_id' => $obligation->id,
                    'obligation_balance' => $obligationBalance
                ]);
            }
        }

        // Sort results by office_id, then by obr_date_sort, then by po_date_sort
        $results = $results->sortBy([
            ['office_id', 'asc'],
            ['obr_date_sort', 'asc'],
            ['po_date_sort', 'asc']
        ]);

        $groupedResults = $results->groupBy('office_name');
        
        // Re-sort each office's group by po_date_sort and obr_date_sort to maintain order after grouping
        $groupedResults = $groupedResults->map(function ($officeObligations) {
            return $officeObligations->sortBy([
                ['po_date_sort', 'asc'],
                ['obr_date_sort', 'asc']
            ]);
        });
        
        // Calculate totals per office and grand total
        $totals = [];
        $grandTotal = 0;
        
        foreach ($groupedResults as $officeName => $officeRows) {
            $officeTotal = 0;
            foreach ($officeRows as $row) {
                // Remove formatting and convert to float
                $amount = (float) str_replace(',', '', $row['amount']);
                $officeTotal += $amount;
            }
            $totals[$officeName] = number_format($officeTotal, 2);
            $grandTotal += $officeTotal;
        }
        
        $totals['GRAND_TOTAL'] = number_format($grandTotal, 2);
        
        return [
            'obligations' => $groupedResults,
            'totals' => $totals
        ];
    }

    public function exportExcel(Request $request)
    {
        // Validate only required fields
        $validated = $request->validate([
            'year1' => 'required',
            'as_of_filter' => 'required|date',
            'signatory_name' => 'required',
            'signatory_designation' => 'required',
        ], [
            'signatory_name.required' => 'Please select a signatory name.',
            'signatory_designation.required' => 'Please select a designation.',
        ]);

        $year = $request->input('year1');
        $officeId = $request->input('office_filter'); // Can be null
        $asOf = $request->input('as_of_filter');
        $signatoryName = $request->input('signatory_name');
        $signatoryDesignation = $request->input('signatory_designation');

        // Get obligations data using the same method
        $obligationsData = $this->getObligations($year, $officeId, $asOf);

        // Check if there's data to export
        if (empty($obligationsData['obligations']) || count($obligationsData['obligations']) == 0) {
            return back()->with('error', 'No data available to export for the selected filters.');
        }

        // Determine filename based on office selection
        if ($officeId) {
            $office = Office::find($officeId);
            $officeAbbr = $office ? preg_replace('/[^A-Za-z0-9_]/', '_', $office->office_abbreviation) : 'AllOffices';
            $officeName = $office;
        } else {
            $officeAbbr = 'AllOffices';
            $officeName = (object)['office_name' => 'All Offices', 'office_abbreviation' => 'All'];
        }
        
        $fileName = 'NDD_' . $officeAbbr . '_' . $year . '.xlsx';

        // Log the excel report generation
        self::logExcelReportGeneration('NDD Report', $fileName, [
            'Year' => $year,
            'Office' => $officeName->office_abbreviation ?? 'All',
            'As Of Date' => $asOf,
        ]);

        return Excel::download(new NDDExport(
            $year,
            $officeName,
            $obligationsData['obligations'],
            $obligationsData['totals'],
            $asOf,
            $signatoryName,
            $signatoryDesignation
        ), $fileName);
    }
}