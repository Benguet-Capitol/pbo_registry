<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use App\Models\Office;
use Carbon\Carbon;
use App\Models\ObligationAdjustment;
use App\Models\OfficeAllotmentClass;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class SAAOBCOExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $selectedOffice;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;

    public function __construct($selectedYear, $selectedOffice, $asOfDate, $signatoryName, $signatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
        $this->selectedOffice = $selectedOffice;
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

                // Freeze rows above row 11
                $sheet->freezePane('A11');

                // Set rows 6 to 10 to repeat on printed pages
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 11);

                // Hide specific columns
                foreach (['E', 'F', 'G', 'H', 'K', 'M', 'N'] as $col) {
                    $sheet->getColumnDimension($col)->setVisible(false);
                }


                // Identify the certified correct row
                $certifiedRow = null;
                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    if (str_contains($cellValue, 'CERTIFIED CORRECT')) {
                        $certifiedRow = $row;
                        break;
                    }
                }

                // Default to 2 rows above certified correct row, or fallback to highestRow
                $lastDataRow = $certifiedRow ? $certifiedRow - 2 : $highestRow;

                // Format number columns
                foreach (range('E', 'P') as $column) {
                    if (!in_array($column, ['N', 'P'])) {
                        $sheet->getStyle("{$column}13:{$column}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                }

                // Format percentage columns
                foreach (['N', 'P'] as $column) {
                    $sheet->getStyle("{$column}13:{$column}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('0.00%');
                }

                // Classify rows based on Z column
                $contentRows = [];
                $subtotalRows = [];
                $totalRows = [];
                $grandTotalRow = null;

                for ($row = 13; $row <= $lastDataRow; $row++) {
                    $firstCellValue = trim((string) $sheet->getCell("A{$row}")->getValue());

                    if (
                        str_starts_with(strtolower($firstCellValue), 'total') ||
                        str_contains(strtolower($firstCellValue), 'grand total') ||
                        str_contains(strtolower($firstCellValue), 'certified correct')
                    ) {
                        continue; // Skip subtotal/total/certified rows
                    }

                    // Apply formulas to content rows
                    $sheet->setCellValue("I{$row}", "=H{$row}+E{$row}+F{$row}+G{$row}");
                    $sheet->setCellValue("J{$row}", "=I{$row}-K{$row}");
                    $sheet->setCellValue("M{$row}", "=I{$row}-L{$row}");
                    $sheet->setCellValue("N{$row}", "=IF(I{$row}>0,L{$row}/I{$row},0.00)");
                    $sheet->setCellValue("O{$row}", "=J{$row}-L{$row}");
                    $sheet->setCellValue("P{$row}", "=IF(J{$row}>0,L{$row}/J{$row},0.00)");
                }

                // Utility: Apply percentage formulas for columns N and P
                function applyPercentageFormulas($sheet, $row)
                {
                    $i = "I{$row}";
                    $j = "J{$row}";
                    $l = "L{$row}";
                    $sheet->setCellValue("N{$row}", "=IF($i>0,$l/$i,0)");
                    $sheet->setCellValue("P{$row}", "=IF($j>0,$l/$j,0)");
                }

                // Loop through all rows to apply formulas
                for ($row = 13; $row <= $lastDataRow; $row++) {
                    $label = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));

                    // === TOTAL ROW ===
                    if (str_starts_with($label, 'TOTAL')) {
                        $startRow = $row - 1;
                        while ($startRow > 13) {
                            $prevLabel = strtoupper(trim((string) $sheet->getCell("A{$startRow}")->getValue()));
                            if (
                                str_starts_with($prevLabel, 'TOTAL') ||
                                str_contains($prevLabel, 'GRAND TOTAL') ||
                                str_contains($prevLabel, 'CERTIFIED CORRECT')
                            ) {
                                $startRow++;
                                break;
                            }
                            $startRow--;
                        }
                        if ($startRow < 13) $startRow = 13;

                        foreach (range('E', 'P') as $col) {
                            $sheet->setCellValue("{$col}{$row}", "=SUM({$col}{$startRow}:{$col}" . ($row - 1) . ")");
                        }
                        applyPercentageFormulas($sheet, $row);
                    }

                    // === GRAND TOTAL ROW ===
                    elseif (str_contains($label, 'GRAND TOTAL')) {
                        $totalRows = [];
                        for ($i = $row - 1; $i >= 13; $i--) {
                            $check = strtoupper(trim((string) $sheet->getCell("A{$i}")->getValue()));
                            if (
                                str_contains($check, 'GRAND TOTAL') ||
                                str_contains($check, 'CERTIFIED CORRECT')
                            ) break;
                            if (str_starts_with($check, 'TOTAL')) $totalRows[] = $i;
                        }
                        if (!empty($totalRows)) {
                            foreach (range('E', 'P') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($totalRows)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
                    }
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1:Z10000' => [
                'font' => [
                    'name' => 'Arial Narrow', // Or 'Calibri', 'Verdana', etc.
                    'size' => 10,
                ],
            ],
            'A10:P10' => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'wrapText' => true,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // or BORDER_MEDIUM for thicker lines
                        'color' => ['argb' => '000000'], // Black color
                    ],
                ],
            ],
        ];
    }

    public function view(): View
    {
        $selectedYear = $this->selectedYear;
        $selectedOffice = $this->selectedOffice;
        $asOfDate = $this->asOfDate;

        // Only include offices with OfficeAllotmentClasses for the selected year and “Current” category
        $officesQuery = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
            $query->where('year', $selectedYear)
                ->whereHas('allotmentClass', function ($subQuery) {
                    $subQuery->where('category', 'Continuing');
                });
        });

        // If a specific office is selected, filter it
        if (!empty($selectedOffice)) {
            $officesQuery->where('id', $selectedOffice);
        }
        // Get all offices with their OfficeAllotmentClasses and related data
        $offices = $officesQuery->with([
            'officeAllotmentClasses' => function ($query) {
                $query->whereHas('allotmentClass', function ($subQuery) {
                    $subQuery->where('category', 'Continuing');
                });
            },
            'officeAllotmentClasses.allotmentClass',
            'officeAllotmentClasses.appropriations' => function ($query) {
                $query->orderByRaw("CASE WHEN programs IS NULL OR programs = '' THEN 0 ELSE 1 END ASC")
                    ->orderBy('account_code', 'asc');
            },
            'officeAllotmentClasses.appropriations.realignments',
            'officeAllotmentClasses.appropriations.supplementals',
            'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
        ])->get();

        $month = Carbon::parse($asOfDate)->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        foreach ($offices as $office) {
            // Display OfficeAllotmentClasses for selected year only
            $office->filteredOfficeAllotmentClasses = $office->officeAllotmentClasses
                ->where('year', $selectedYear)
                ->sortBy(fn($oac) => $oac->allotmentClass->id)
                ->values();

            $ccoYears = collect();
            $appropriationsByYear = [];

            foreach ($office->officeAllotmentClasses as $oac) {
                foreach ($oac->appropriations as $app) {
                    if (!empty($app->cco_year)) {
                        $ccoYear = $app->cco_year;
                        $ccoYears->push($ccoYear);

                        // --- Supplementals ---
                        $sb = $app->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $rev = $app->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        // --- Realignments ---
                        $realignment = $app->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->reduce(fn($carry, $r) =>
                            $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                        // --- Obligations ---
                        $obligationBase = $app->obligationAmounts
                            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        // --- Obligation Adjustments ---
                        $obligationAdjustments = $app->obligationAmounts
                            ->flatMap(
                                fn($oa) => $oa->obligation
                                    ? $oa->obligation->obligationAdjustments->where('adjustment_date', '<=', $asOfDate)
                                    : collect()
                            )
                            ->sum('adjustment_amount');

                        $obligation = $obligationBase + $obligationAdjustments;

                        $authorized = $app->appropriation + $sb + $rev + $realignment;

                        $allotment = ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4)
                            + $sb + $rev + $realignment;

                        $forLater = 0;
                        if ($currentQuarter < 2) $forLater += $app->quarter2;
                        if ($currentQuarter < 3) $forLater += $app->quarter3;
                        if ($currentQuarter < 4) $forLater += $app->quarter4;

                        $allotment -= $forLater;

                        $app->sb_appropriation = $sb;
                        $app->reversion = $rev;
                        $app->realignment = $realignment;
                        $app->obligation = $obligation;
                        $app->authorized_appropriation = $authorized;
                        $app->allotment = $allotment;
                        $app->for_later_release = $forLater;
                        $app->appropriation_balance = $authorized - $obligation;
                        $app->appropriation_accomplishment = $authorized > 0
                            ? ($obligation / $authorized) * 100 : 0;
                        $app->allotment_balance = $allotment - $obligation;
                        $app->allotment_accomplishment = $allotment > 0
                            ? ($obligation / $allotment) * 100 : 0;

                        if (!isset($appropriationsByYear[$ccoYear])) {
                            $appropriationsByYear[$ccoYear] = [];
                        }

                        $appropriationsByYear[$ccoYear][] = $app;
                    }
                }
            }

            $office->ccoYears = $ccoYears->unique()->sort()->values();
            $office->appropriationsByYear = $appropriationsByYear;

            // ---- Compute totals per cco_year ----
            $yearlyTotals = [];

            foreach ($office->appropriationsByYear as $ccoYear => $apps) {
                $totals = [
                    'appropriation' => 0,
                    'sb' => 0,
                    'rev' => 0,
                    'realignment' => 0,
                    'authorized' => 0,
                    'allotment' => 0,
                    'for_later_release' => 0,
                    'obligation' => 0,
                    'appropriation_balance' => 0,
                    'appropriation_accomplishment' => 0,
                    'allotment_balance' => 0,
                    'allotment_accomplishment' => 0,
                ];

                foreach ($apps as $app) {
                    $totals['appropriation'] += $app->appropriation;
                    $totals['sb'] += $app->sb_appropriation;
                    $totals['rev'] += $app->reversion;
                    $totals['realignment'] += $app->realignment;
                    $totals['authorized'] += $app->authorized_appropriation;
                    $totals['allotment'] += $app->allotment;
                    $totals['for_later_release'] += $app->for_later_release;
                    $totals['obligation'] += $app->obligation;
                    $totals['appropriation_balance'] += $app->appropriation_balance;
                    $totals['allotment_balance'] += $app->allotment_balance;
                }

                // Compute percentages
                $totals['appropriation_accomplishment'] = $totals['authorized'] > 0
                    ? ($totals['obligation'] / $totals['authorized']) * 100 : 0;

                $totals['allotment_accomplishment'] = $totals['allotment'] > 0
                    ? ($totals['obligation'] / $totals['allotment']) * 100 : 0;

                $yearlyTotals[$ccoYear] = $totals;
            }

            $office->yearlyTotals = $yearlyTotals;

            $office->grandTotal = [
                'appropriation' => collect($office->yearlyTotals)->sum('appropriation'),
                'sb' => collect($office->yearlyTotals)->sum('sb'),
                'rev' => collect($office->yearlyTotals)->sum('rev'),
                'realignment' => collect($office->yearlyTotals)->sum('realignment'),
                'authorized' => collect($office->yearlyTotals)->sum('authorized'),
                'allotment' => collect($office->yearlyTotals)->sum('allotment'),
                'for_later_release' => collect($office->yearlyTotals)->sum('for_later_release'),
                'obligation' => collect($office->yearlyTotals)->sum('obligation'),
                'appropriation_balance' => collect($office->yearlyTotals)->sum('appropriation_balance'),
                'appropriation_accomplishment' => collect($office->yearlyTotals)->avg('appropriation_accomplishment'),
                'allotment_balance' => collect($office->yearlyTotals)->sum('allotment_balance'),
                'allotment_accomplishment' => collect($office->yearlyTotals)->avg('allotment_accomplishment'),
            ];
        }

        // Pass signatory info to the view as well
        return view('exports.saaobco', [
            'offices' => $offices,
            'selectedYear' => $selectedYear,
            'selectedOffice' => $selectedOffice,
            'asOfDate' => $asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
        ]);
    }
}
