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

class NDDExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $selectedOffice;
    protected $obligations;
    protected $totals;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;

    public function __construct($selectedYear, $selectedOffice, $obligations, $totals, $asOfDate, $signatoryName, $signatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
        $this->selectedOffice = $selectedOffice;
        $this->obligations = $obligations;
        $this->totals = $totals;
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

                // Freeze rows above row 12 (header row)
                $sheet->freezePane('A12');

                // Set rows 1 to 12 to repeat on printed pages
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 11);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(35); // Payee
                $sheet->getColumnDimension('B')->setWidth(20); // Budget Control No
                $sheet->getColumnDimension('C')->setWidth(15); // PO Number
                $sheet->getColumnDimension('D')->setWidth(12); // PO Date
                $sheet->getColumnDimension('E')->setWidth(15); // Amount
                $sheet->getColumnDimension('F')->setWidth(20); // Remarks

                // Format amount column (E) as currency
                $sheet->getStyle('E13:E' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');

                // Center align specific columns
                $sheet->getStyle('B13:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Right align amount column
                $sheet->getStyle('E13:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Apply formulas to total rows
                $officeTotalRows = [];
                $startRow = null;
                
                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = trim((string) $sheet->getCell("A{$row}")->getValue());
                    
                    // Check if this is an office header row
                    if ($sheet->getCell("B{$row}")->getValue() === null && 
                        $sheet->getCell("C{$row}")->getValue() === null &&
                        !str_starts_with(strtoupper($cellValue), 'TOTAL') &&
                        !str_contains(strtoupper($cellValue), 'GRAND TOTAL') &&
                        !empty($cellValue)) {
                        // This is an office header row
                        $startRow = $row + 1; // Data starts on next row
                    }
                    
                    // Check if this is an office total row
                    if (str_starts_with(strtoupper($cellValue), 'TOTAL (') && $startRow !== null) {
                        // Calculate the end row (current row - 1)
                        $endRow = $row - 1;
                        
                        // Apply SUM formula for this office
                        $sheet->setCellValue("E{$row}", "=SUM(E{$startRow}:E{$endRow})");
                        
                        // Store this total row for grand total calculation
                        $officeTotalRows[] = $row;
                        
                        // Bold the total row
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                        ]);
                        
                        // Reset for next office
                        $startRow = null;
                    }
                    
                    // Check if this is grand total row
                    if (str_contains(strtoupper($cellValue), 'GRAND TOTAL') && !empty($officeTotalRows)) {
                        // Create formula that sums all office total rows
                        $totalRefs = array_map(fn($r) => "E{$r}", $officeTotalRows);
                        $formula = "=" . implode("+", $totalRefs);
                        $sheet->setCellValue("E{$row}", $formula);
                        
                        // Bold the grand total row
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                        ]);
                    }
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1:F1000' => [
                'font' => [
                    'name' => 'Arial Narrow',
                    'size' => 10,
                ],
            ],
            'A1:F11' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A11:F11' => [
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
        return view('exports.ndd', [
            'selectedYear' => $this->selectedYear,
            'selectedOffice' => $this->selectedOffice,
            'obligations' => $this->obligations,
            'totals' => $this->totals,
            'asOfDate' => $this->asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
        ]);
    }
}