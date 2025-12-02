<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
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
    protected $officeAllotmentClass;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;
    protected $calculatedData;

    public function __construct($selectedYear, $officeAllotmentClass, $asOfDate, $signatoryName, $signatoryDesignation, $calculatedData)
    {
        $this->selectedYear = $selectedYear;
        $this->officeAllotmentClass = $officeAllotmentClass;
        $this->asOfDate = $asOfDate;
        $this->signatoryName = $signatoryName;
        $this->signatoryDesignation = $signatoryDesignation;
        $this->calculatedData = $calculatedData;
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
                $releasedAppropriationRows = [];
                $adjustmentStartRows = [];
                $totalReleasedRows = [];
                $obligationsHeaderRows = [];
                $firstObligationRows = [];
                $totalExpensesRows = [];
                $balanceRows = [];
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
                    
                    $cellValue = '';
                    if (!empty($cellC)) {
                        $cellValue = $cellC;
                    } elseif (!empty($cellB)) {
                        $cellValue = $cellB;
                    } elseif (!empty($cellA)) {
                        $cellValue = $cellA;
                    }
                    
                    $mergedValue = '';
                    foreach ($sheet->getMergeCells() as $mergeRange) {
                        if (preg_match('/^[A-Z]+(\d+):/', $mergeRange, $matches)) {
                            $startRow = (int) $matches[1];
                            
                            if ($startRow == $row) {
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
                            $cellValue === 'Obligations and Adjustments') {
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

                // Define helper function
                $applyTotalColumnFormula = function($row, $startCol, $endCol) use ($sheet) {
                    $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                    $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCol);
                    $formula = "=SUM({$startColLetter}{$row}:{$endColLetter}{$row})";
                    $sheet->setCellValue("D{$row}", $formula);
                };

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
                            $sumRange = "{$colLetter}" . ($quarterStartRow + 1) . ":{$colLetter}" . ($totalRow - 1);
                            $formula = "=SUM({$sumRange})";
                        } else {
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
                                $sumRange = "{$colLetter}" . ($quarterStartRow + 1) . ":{$colLetter}" . ($totalRow - 1);
                                $formula = "={$colLetter}{$prevTotalRow}+SUM({$sumRange})";
                            } else {
                                $formula = "={$colLetter}{$prevTotalRow}";
                            }
                        }
                        
                        $sheet->setCellValue("{$colLetter}{$totalRow}", $formula);
                    }
                }

                // Apply formulas for Released Appropriations and Adjustments
                $quarterStartRowsKeys = array_keys($quarterStartRows);
                for ($i = 0; $i < count($quarterStartRowsKeys); $i++) {
                    $q = $quarterStartRowsKeys[$i];
                    
                    if (!isset($quarterStartRows[$q]) || !isset($totalReleasedRows[$q])) continue;
                    
                    $quarterStartRow = $quarterStartRows[$q];
                    $totalReleasedRow = $totalReleasedRows[$q];
                    
                    for ($row = $quarterStartRow + 1; $row < $totalReleasedRow; $row++) {
                        $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                        $sheet->setCellValue("D{$row}", "=SUM(E{$row}:{$endColLetter}{$row})");
                    }
                }

                // Apply formulas for Total Expenses
                $totalExpensesRowsKeys = array_keys($totalExpensesRows);
                for ($i = 0; $i < count($totalExpensesRowsKeys); $i++) {
                    $q = $totalExpensesRowsKeys[$i];
                    
                    if (!isset($totalExpensesRows[$q]) || !isset($obligationsHeaderRows[$q])) continue;
                    
                    $totalRow = $totalExpensesRows[$q];
                    $obligationsHeaderRow = $obligationsHeaderRows[$q];
                    
                    for ($col = 4; $col <= $columnIndex; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $sumRange = "{$colLetter}" . ($obligationsHeaderRow + 1) . ":{$colLetter}" . ($totalRow - 1);
                        $formula = "=SUM({$sumRange})";
                        $sheet->setCellValue("{$colLetter}{$totalRow}", $formula);
                    }
                }

                // Apply formulas for individual obligations and adjustments
                $obligationsHeaderRowsKeys = array_keys($obligationsHeaderRows);
                for ($i = 0; $i < count($obligationsHeaderRowsKeys); $i++) {
                    $q = $obligationsHeaderRowsKeys[$i];
                    
                    if (!isset($obligationsHeaderRows[$q]) || !isset($totalExpensesRows[$q])) continue;
                    
                    $obligationsHeaderRow = $obligationsHeaderRows[$q];
                    $totalExpensesRow = $totalExpensesRows[$q];
                    
                    for ($row = $obligationsHeaderRow + 1; $row < $totalExpensesRow; $row++) {
                        $applyTotalColumnFormula($row, 5, $columnIndex);
                    }
                }

                // Apply formulas for Balance from Released Appropriations per quarter
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

                // Apply formulas for Grand Total Expenses
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

                // Apply formula for Balance from Released Appropriations (grand)
                if ($grandBalanceReleasedRow && count($totalReleasedRows) > 0 && $grandTotalExpensesRow) {
                    $totalReleasedRowsKeys = array_keys($totalReleasedRows);
                    $lastQuarterKey = end($totalReleasedRowsKeys);
                    $lastTotalReleasedRow = $totalReleasedRows[$lastQuarterKey];
                    
                    for ($col = 4; $col <= $columnIndex; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $formula = "={$colLetter}{$lastTotalReleasedRow}-{$colLetter}{$grandTotalExpensesRow}";
                        $sheet->setCellValue("{$colLetter}{$grandBalanceReleasedRow}", $formula);
                    }
                }

                // Apply formula for Balance from Authorized Appropriations
                if ($grandBalanceAuthRow && $totalAppropriationsRow && $grandTotalExpensesRow) {
                    for ($col = 4; $col <= $columnIndex; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $formula = "={$colLetter}{$totalAppropriationsRow}-{$colLetter}{$grandTotalExpensesRow}";
                        $sheet->setCellValue("{$colLetter}{$grandBalanceAuthRow}", $formula);
                    }
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        
        return [
            'A1:' . $highestColumn . '10000' => [
                'font' => [
                    'name' => 'Arial Narrow',
                    'size' => 10,
                ],
            ],
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
        // Pass all the pre-calculated data directly to the view
        return view('exports.rao', array_merge([
            'selectedYear' => $this->selectedYear,
            'selectedOfficeAllotmentClass' => $this->officeAllotmentClass->id,
            'officeAllotmentClass' => $this->officeAllotmentClass,
            'asOfDate' => $this->asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
        ], $this->calculatedData));
    }
}