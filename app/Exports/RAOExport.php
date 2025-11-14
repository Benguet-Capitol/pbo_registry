<?php

namespace App\Exports;

use App\Models\Appropriation;
use App\Models\Employee;
use App\Models\Obligation;
use Illuminate\Contracts\View\View;
use App\Models\Office;
use Carbon\Carbon;
use App\Models\ObligationAdjustment;
use App\Models\OfficeAllotmentClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class RAOExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $selectedOfficeAllotmentClass;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;

    public function __construct($selectedYear, $selectedOfficeAllotmentClass, $asOfDate, $signatoryName, $signatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
        $this->selectedOfficeAllotmentClass = $selectedOfficeAllotmentClass;
        $this->asOfDate = $asOfDate;
        $this->signatoryName = $signatoryName;
        $this->signatoryDesignation = $signatoryDesignation;
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Freeze rows above row 12
            $sheet->freezePane('A12');

            // Set rows to repeat on printed pages
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 11);

            // Apply number formatting to all data columns (from column D onwards)
            $sheet->getStyle("D12:{$highestColumn}{$highestRow}")
                ->getNumberFormat()
                ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');

            // Find key rows
            $appropriationsRow = null;
            $supplementalRow = null;
            $reversionsRow = null;
            $realignmentsRow = null;
            $totalAppropriationsRow = null;
            $quarterStartRows = [];
            $releasedAppropriationRows = []; // Per quarter
            $adjustmentStartRows = []; // First adjustment row per quarter
            $totalReleasedRows = []; // Total Released Appropriations per quarter
            $obligationsHeaderRows = []; // "Obligations" header per quarter
            $firstObligationRows = []; // First obligation row per quarter
            $totalExpensesRows = []; // Total Expenses per quarter
            $balanceRows = []; // Balance from Released per quarter
            $grandTotalExpensesRow = null;
            $grandBalanceReleasedRow = null;
            $grandBalanceAuthRow = null;

            // Scan rows to identify key rows
            $currentQuarter = -1;
            $inQuarterSection = false;
            $inObligationsSection = false;
            $lastQuarterHeaderRow = -1;

            for ($row = 12; $row <= $highestRow; $row++) {
                $cellA = trim((string) $sheet->getCell("A{$row}")->getValue());
                $cellB = trim((string) $sheet->getCell("B{$row}")->getValue());
                $cellC = trim((string) $sheet->getCell("C{$row}")->getValue());
                
                // Check all columns for the value
                $cellValue = '';
                if (!empty($cellC)) {
                    $cellValue = $cellC;
                } elseif (!empty($cellB)) {
                    $cellValue = $cellB;
                } elseif (!empty($cellA)) {
                    $cellValue = $cellA;
                }
                
                // Also check if it's a merged cell spanning multiple columns
                $mergedValue = '';
                foreach ($sheet->getMergeCells() as $mergeRange) {
                    // Extract row number using regex (e.g., "A10:D10" -> get "10")
                    if (preg_match('/^[A-Z]+(\d+):/', $mergeRange, $matches)) {
                        $startRow = (int) $matches[1];
                        
                        if ($startRow == $row) {
                            // Get the first cell reference
                            list($startCell) = explode(':', $mergeRange);
                            $mergedValue = trim((string) $sheet->getCell($startCell)->getValue());
                            break;
                        }
                    }
                }
                
                if (!empty($mergedValue)) {
                    $cellValue = $mergedValue;
                }
                
                // Check for key labels
                if (stripos($cellValue, 'Appropriations') !== false && 
                    stripos($cellValue, 'Total') === false && 
                    stripos($cellValue, 'Supplemental') === false && 
                    stripos($cellValue, 'Released') === false && 
                    stripos($cellValue, 'Balance') === false && 
                    stripos($cellValue, 'Authorized') === false) {
                    $appropriationsRow = $row;
                } 
                elseif (stripos($cellValue, 'Supplemental Appropriations') !== false) {
                    $supplementalRow = $row;
                } 
                elseif (stripos($cellValue, 'Reversions') !== false && stripos($cellValue, 'Total') === false) {
                    $reversionsRow = $row;
                } 
                elseif (stripos($cellValue, 'Realignments') !== false && stripos($cellValue, 'Total') === false) {
                    $realignmentsRow = $row;
                } 
                elseif (stripos($cellValue, 'Total Appropriations') !== false) {
                    $totalAppropriationsRow = $row;
                } 
                // ONLY match if it's EXACTLY the quarter header (entire cell equals "1st Quarter", etc.)
                elseif (preg_match('/^(1st|2nd|3rd|4th)\s+Quarter$/i', $cellValue) && $row > $lastQuarterHeaderRow + 5) {
                    $currentQuarter++;
                    $quarterStartRows[$currentQuarter] = $row;
                    $lastQuarterHeaderRow = $row;
                    $inQuarterSection = true;
                    $inObligationsSection = false;
                } 
                elseif ($inQuarterSection && $currentQuarter >= 0 && 
                        stripos($cellValue, 'Released Appropriation') !== false && 
                        stripos($cellValue, 'Total') === false && 
                        stripos($cellValue, 'Balance') === false) {
                    $releasedAppropriationRows[$currentQuarter] = $row;
                } 
                elseif ($inQuarterSection && $currentQuarter >= 0 &&
                        (stripos($cellValue, 'Supplemental') !== false || 
                        stripos($cellValue, 'Reversion') !== false || 
                        stripos($cellValue, 'Realignment') !== false) &&
                        stripos($cellValue, 'dated') !== false) {
                    if (!isset($adjustmentStartRows[$currentQuarter])) {
                        $adjustmentStartRows[$currentQuarter] = $row;
                    }
                } 
                elseif ($inQuarterSection && $currentQuarter >= 0 && 
                        stripos($cellValue, 'Total Released Appropriations') !== false) {
                    $totalReleasedRows[$currentQuarter] = $row;
                } 
                elseif ($inQuarterSection && $currentQuarter >= 0 && 
                        $cellValue === 'Obligations') { // Exact match for "Obligations" header
                    $obligationsHeaderRows[$currentQuarter] = $row;
                    $inObligationsSection = true;
                } 
                elseif ($inObligationsSection && $currentQuarter >= 0 && 
                        preg_match('/Total Expenses.*Quarter/i', $cellValue)) {
                    $totalExpensesRows[$currentQuarter] = $row;
                } 
                elseif ($inObligationsSection && $currentQuarter >= 0 && 
                        preg_match('/Balance from Released Appropriations.*Quarter/i', $cellValue)) {
                    $balanceRows[$currentQuarter] = $row;
                    $inQuarterSection = false;
                    $inObligationsSection = false;
                } 
                // Grand totals - only match when NOT in quarter section
                elseif (!$inQuarterSection && 
                        (stripos($cellValue, 'Grand Total Expenses') !== false || 
                        stripos($cellValue, 'Grant Total Expenses') !== false)) {
                    $grandTotalExpensesRow = $row;
                } 
                elseif (!$inQuarterSection && 
                        preg_match('/^Balance from Released Appropriations$/i', $cellValue)) {
                    $grandBalanceReleasedRow = $row;
                } 
                elseif (!$inQuarterSection && 
                        stripos($cellValue, 'Balance from Authorized Appropriations') !== false) {
                    $grandBalanceAuthRow = $row;
                }
            }

            $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            // Apply formulas for Total Appropriations row
            if ($appropriationsRow && $supplementalRow && $reversionsRow && $realignmentsRow && $totalAppropriationsRow) {
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $formula = "={$colLetter}{$appropriationsRow}+{$colLetter}{$supplementalRow}+{$colLetter}{$reversionsRow}+{$colLetter}{$realignmentsRow}";
                    $sheet->setCellValue("{$colLetter}{$totalAppropriationsRow}", $formula);
                }
            }

            // Apply formulas for Total Released Appropriations per quarter
            $totalReleasedRowsKeys = array_keys($totalReleasedRows);
            for ($i = 0; $i < count($totalReleasedRowsKeys); $i++) {
                $q = $totalReleasedRowsKeys[$i];
                
                if (!isset($totalReleasedRows[$q]) || !isset($quarterStartRows[$q])) continue;
                
                $totalRow = $totalReleasedRows[$q];
                $quarterStartRow = $quarterStartRows[$q];
                
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    
                    if ($i == 0) {
                        // 1st Quarter: Sum Released Appropriation + Adjustments (if present)
                        $sumRange = "{$colLetter}" . ($quarterStartRow + 1) . ":{$colLetter}" . ($totalRow - 1);
                        $formula = "=SUM({$sumRange})";
                    } else {
                        // 2nd, 3rd, 4th Quarter: Check if there are any rows between quarter start and total
                        $hasData = false;
                        for ($checkRow = $quarterStartRow + 1; $checkRow < $totalRow; $checkRow++) {
                            $checkValue = $sheet->getCell("{$colLetter}{$checkRow}")->getValue();
                            if (!empty($checkValue) && $checkValue !== '-' && $checkValue !== 0) {
                                $hasData = true;
                                break;
                            }
                        }
                        
                        $prevQ = $totalReleasedRowsKeys[$i - 1];
                        $prevTotalRow = $totalReleasedRows[$prevQ];
                        
                        if ($hasData) {
                            // Has Released Appropriation or Adjustments: Previous Total + Current items
                            $sumRange = "{$colLetter}" . ($quarterStartRow + 1) . ":{$colLetter}" . ($totalRow - 1);
                            $formula = "={$colLetter}{$prevTotalRow}+SUM({$sumRange})";
                        } else {
                            // No Released Appropriation or Adjustments: Just reference previous total
                            $formula = "={$colLetter}{$prevTotalRow}";
                        }
                    }
                    
                    $sheet->setCellValue("{$colLetter}{$totalRow}", $formula);
                }
            }

            // Apply formulas for Total Expenses (sum obligations and adjustments per quarter)
            $totalExpensesRowsKeys = array_keys($totalExpensesRows);
            for ($i = 0; $i < count($totalExpensesRowsKeys); $i++) {
                $q = $totalExpensesRowsKeys[$i];
                
                if (!isset($totalExpensesRows[$q]) || !isset($obligationsHeaderRows[$q])) continue;
                
                $totalRow = $totalExpensesRows[$q];
                $obligationsHeaderRow = $obligationsHeaderRows[$q];
                
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    // Sum from row after "Obligations" header to row before "Total Expenses"
                    $sumRange = "{$colLetter}" . ($obligationsHeaderRow + 1) . ":{$colLetter}" . ($totalRow - 1);
                    $formula = "=SUM({$sumRange})";
                    $sheet->setCellValue("{$colLetter}{$totalRow}", $formula);
                }
            }

            // Apply formulas for Balance from Released Appropriations (per quarter)
            $balanceRowsKeys = array_keys($balanceRows);
            for ($i = 0; $i < count($balanceRowsKeys); $i++) {
                $q = $balanceRowsKeys[$i];
                
                if (!isset($balanceRows[$q]) || !isset($totalReleasedRows[$q]) || !isset($totalExpensesRows[$q])) continue;
                
                $balanceRow = $balanceRows[$q];
                $totalReleasedRow = $totalReleasedRows[$q];
                $totalExpensesRow = $totalExpensesRows[$q];
                
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $formula = "={$colLetter}{$totalReleasedRow}-{$colLetter}{$totalExpensesRow}";
                    $sheet->setCellValue("{$colLetter}{$balanceRow}", $formula);
                }
            }

            // Apply formulas for Grand Total Expenses (sum of all quarterly expenses)
            if ($grandTotalExpensesRow && count($totalExpensesRows) > 0) {
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $refs = [];
                    foreach ($totalExpensesRows as $row) {
                        $refs[] = "{$colLetter}{$row}";
                    }
                    $formula = "=SUM(" . implode(',', $refs) . ")";
                    $sheet->setCellValue("{$colLetter}{$grandTotalExpensesRow}", $formula);
                }
            }

            // Apply formula for Balance from Released Appropriations (grand total)
            if ($grandBalanceReleasedRow && count($totalReleasedRows) > 0 && $grandTotalExpensesRow) {
                $totalReleasedRowsKeys = array_keys($totalReleasedRows);
                $lastQuarterKey = end($totalReleasedRowsKeys); // Get the last key (should be 3 for 4th quarter)
                $lastTotalReleasedRow = $totalReleasedRows[$lastQuarterKey];
                
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $formula = "={$colLetter}{$lastTotalReleasedRow}-{$colLetter}{$grandTotalExpensesRow}";
                    $sheet->setCellValue("{$colLetter}{$grandBalanceReleasedRow}", $formula);
                }
            }

            // Apply formula for Balance from Authorized Appropriations (grand total)
            if ($grandBalanceAuthRow && $totalAppropriationsRow && $grandTotalExpensesRow) {
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $formula = "={$colLetter}{$totalAppropriationsRow}-{$colLetter}{$grandTotalExpensesRow}";
                    $sheet->setCellValue("{$colLetter}{$grandBalanceAuthRow}", $formula);
                }
            }

            // Apply formulas for Total column (column D) and all appropriation columns for each data row
            // This helper function applies SUM formula for the Total column
            $applyTotalColumnFormula = function($row, $startCol, $endCol) use ($sheet) {
                $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCol);
                $formula = "=SUM({$startColLetter}{$row}:{$endColLetter}{$row})";
                $sheet->setCellValue("D{$row}", $formula);
            };

            // Apply formulas for Total Appropriations row
            if ($appropriationsRow && $supplementalRow && $reversionsRow && $realignmentsRow) {
                for ($col = 4; $col <= $columnIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    
                    if ($col == 4) {
                        // Total column: sum all appropriation columns
                        $applyTotalColumnFormula($appropriationsRow, 5, $columnIndex);
                        $applyTotalColumnFormula($supplementalRow, 5, $columnIndex);
                        $applyTotalColumnFormula($reversionsRow, 5, $columnIndex);
                        $applyTotalColumnFormula($realignmentsRow, 5, $columnIndex);
                    } else {
                        // Appropriation columns: sum the 4 rows above
                        $formula = "={$colLetter}{$appropriationsRow}+{$colLetter}{$supplementalRow}+{$colLetter}{$reversionsRow}+{$colLetter}{$realignmentsRow}";
                        $sheet->setCellValue("{$colLetter}{$totalAppropriationsRow}", $formula);
                    }
                }
            }
        },
    ];
}

    public function styles(Worksheet $sheet)
    {
        // Get the highest column (last column with data)
        $highestColumn = $sheet->getHighestColumn();
        
        return [
            // Global font style for entire sheet
            'A1:' . $highestColumn . '10000' => [
                'font' => [
                    'name' => 'Arial Narrow',
                    'size' => 10,
                ],
            ],
            // Header rows 9 and 10 styling
            'A10:' . $highestColumn . '11' => [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'wrapText' => true,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function view(): View
    {
        $selectedYear = $this->selectedYear;
        $selectedOfficeAllotmentClass = $this->selectedOfficeAllotmentClass;
        $asOfDate = $this->asOfDate;

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

        // Get the selected office allotment class details
        $officeAllotmentClass = null;
        if ($selectedOfficeAllotmentClass) {
            $officeAllotmentClass = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
                ->find($selectedOfficeAllotmentClass);
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
                        'obr_date' => $obligation->obr_date,
                        'obr_no' => $obligation->obr_no,
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
                $quarterlyObligations[$quarter] = $items->sortBy(function($item) {
                    return $item['type'] == 'obligation' ? $item['obr_date'] : $item['adjustment_date'];
                })->values();
            }
        }

        // Pass all necessary data to the view
        return view('exports.rao', [
            'selectedYear' => $selectedYear,
            'selectedOfficeAllotmentClass' => $selectedOfficeAllotmentClass,
            'officeAllotmentClass' => $officeAllotmentClass,
            'asOfDate' => $asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
            'appropriations' => $appropriations,
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
            'obligations' => $obligations,
        ]);
    }

}
