<?php

namespace App\Exports;

use App\Models\AccountCode;
use Illuminate\Contracts\View\View;
use App\Models\Office;
use App\Traits\SortsAppropriations;
use Carbon\Carbon;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class SAAODBExport implements FromView, WithStyles, WithEvents
{
    use SortsAppropriations;
    protected $selectedYear;
    protected $selectedOffice;
    protected $accountCode;
    protected $asOfDate;
    protected $preparedSignatoryName;
    protected $preparedSignatoryDesignation;
    protected $certifiedSignatoryName;
    protected $certifiedSignatoryDesignation;
    protected $isSEFConsolidated;

    public function __construct($selectedYear, $selectedOffice, $accountCode, $asOfDate, $preparedSignatoryName, $preparedSignatoryDesignation, $certifiedSignatoryName, $certifiedSignatoryDesignation, $isSEFConsolidated = false)
    {
        $this->selectedYear = $selectedYear;
        $this->selectedOffice = $selectedOffice;
        $this->accountCode = $accountCode;
        $this->asOfDate = $asOfDate;
        $this->preparedSignatoryName = $preparedSignatoryName;
        $this->preparedSignatoryDesignation = $preparedSignatoryDesignation;
        $this->certifiedSignatoryName = $certifiedSignatoryName;
        $this->certifiedSignatoryDesignation = $certifiedSignatoryDesignation;
        $this->isSEFConsolidated = $isSEFConsolidated;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Freeze rows above row 10
                $sheet->freezePane('A10');

                // Set rows 5 to 9 to repeat on printed pages
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 10);

                // Hide specific columns
                foreach (['C', 'J', 'N', 'O'] as $col) {
                    $sheet->getColumnDimension($col)->setVisible(false);
                }

                // Identify the certified correct row
                $certifiedRow = null;
                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("D{$row}")->getValue()));
                    if (str_contains($cellValue, 'PREPARED BY')) {
                        $certifiedRow = $row;
                        break;
                    }
                }

                // Find OVERALL TOTAL row
                $overallTotalRow = null;
                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    if (str_contains($cellValue, 'OVERALL TOTAL')) {
                        $overallTotalRow = $row;
                        break;
                    }
                }

                // Default to 2 rows above certified correct row, or fallback to highestRow
                $lastDataRow = $certifiedRow ? $certifiedRow - 2 : $highestRow;

                // Find all GRAND TOTAL rows for Overall Total calculation (exclude overall total row itself)
                $grandTotalRows = [];
                $searchEndRow = $overallTotalRow ? $overallTotalRow - 1 : $lastDataRow;
                for ($row = 13; $row <= $searchEndRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    if (str_contains($cellValue, 'GRAND TOTAL')) {
                        $grandTotalRows[] = $row;
                    }
                }

                // Format number columns as Accounting without currency symbol
                foreach (range('D', 'S') as $column) {
                    if (!in_array($column, ['M', 'O', 'Q', 'R'])) {
                        $sheet->getStyle("{$column}13:{$column}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                }

                // Format percentage columns
                foreach (['M', 'O', 'Q', 'R'] as $column) {
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
                    $secondCellValue = trim((string) $sheet->getCell("B{$row}")->getValue());

                    if (
                        str_starts_with(strtolower($firstCellValue), 'subtotal') ||
                        str_starts_with(strtolower($firstCellValue), 'total') ||
                        str_contains(strtolower($firstCellValue), 'grand total') ||
                        str_contains(strtolower($secondCellValue), 'prepared by')
                    ) {
                        continue; // Skip subtotal/total/certified rows
                    }

                    // Apply formulas to content rows
                    $sheet->setCellValue("H{$row}", "=D{$row}+E{$row}+F{$row}+G{$row}");
                    $sheet->setCellValue("I{$row}", "=H{$row}-J{$row}");
                    $sheet->setCellValue("L{$row}", "=H{$row}-K{$row}");
                    $sheet->setCellValue("M{$row}", "=IF(H{$row}>0,K{$row}/H{$row},0.00)");
                    $sheet->setCellValue("N{$row}", "=I{$row}-K{$row}");
                    $sheet->setCellValue("O{$row}", "=IF(I{$row}>0,K{$row}/I{$row},0.00)");
                    $sheet->setCellValue("Q{$row}", "=IF(K{$row}>0,P{$row}/K{$row},0.00)");
                    $sheet->setCellValue("R{$row}", "=IF(H{$row}>0,P{$row}/H{$row},0.00)");
                    $sheet->setCellValue("S{$row}", "=K{$row}-P{$row}");
                }

                // Utility: Apply percentage formulas for columns M and O
                function applyPercentageFormulas($sheet, $row)
                {
                    $h = "H{$row}";
                    $i = "I{$row}";
                    $k = "K{$row}";
                    $p = "P{$row}";

                    $sheet->setCellValue("M{$row}", "=IF($h>0,$k/$h,0)");
                    $sheet->setCellValue("O{$row}", "=IF($i>0,$k/$i,0)");
                    $sheet->setCellValue("Q{$row}", "=IF($k>0,$p/$k,0)");
                    $sheet->setCellValue("R{$row}", "=IF($h>0,$p/$h,0)");
                }

                // Loop through all rows to apply formulas
                for ($row = 13; $row <= $lastDataRow; $row++) {
                    $label = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));

                    // === SUBTOTAL ROW ===
                    if (str_starts_with($label, 'SUBTOTAL')) {
                        $startRow = $row - 1;
                        while ($startRow > 13) {
                            $prevLabel = strtoupper(trim((string) $sheet->getCell("A{$startRow}")->getValue()));
                            if (
                                str_starts_with($prevLabel, 'SUBTOTAL') ||
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

                        foreach (range('D', 'S') as $col) {
                            $sheet->setCellValue("{$col}{$row}", "=SUM({$col}{$startRow}:{$col}" . ($row - 1) . ")");
                        }
                        applyPercentageFormulas($sheet, $row);
                    }

                    // === TOTAL ROW ===
                    elseif (str_starts_with($label, 'TOTAL')) {
                        $subtotalRows = [];
                        $contentRows = [];
                        
                        // Look backwards to find subtotals or content rows
                        for ($i = $row - 1; $i >= 13; $i--) {
                            $check = strtoupper(trim((string) $sheet->getCell("A{$i}")->getValue()));
                            if (
                                str_starts_with($check, 'TOTAL') ||
                                str_contains($check, 'GRAND TOTAL') ||
                                str_contains($check, 'CERTIFIED CORRECT')
                            ) break;
                            
                            if (str_starts_with($check, 'SUBTOTAL')) {
                                $subtotalRows[] = $i;
                            } elseif (!empty($check)) {
                                // This is a content row
                                $contentRows[] = $i;
                            }
                        }
                        
                        // If there are subtotals, sum them
                        if (!empty($subtotalRows)) {
                            foreach (range('D', 'S') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($subtotalRows)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
                        // If no subtotals, sum all content rows directly
                        elseif (!empty($contentRows)) {
                            $startRow = min($contentRows);
                            $endRow = max($contentRows);
                            
                            foreach (range('D', 'S') as $col) {
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$col}{$startRow}:{$col}{$endRow})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
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
                            foreach (range('D', 'S') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($totalRows)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
                    }
                }

                // === ADD TOTAL COE & TOTAL COE + CO ROWS (skip for SEF offices) ===
                if (!$this->isSEFConsolidated) {
                    $totalsMap = [];
                    for ($row = 13; $row <= $lastDataRow; $row++) {
                        $label = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                        if (str_starts_with($label, 'TOTAL')) {
                            $totalsMap[$label] = $row;
                        }
                    }

                    // Collect COE components (PS + MOOE + FE)
                    $coeComponents = [];
                    foreach ($totalsMap as $label => $rowNum) {
                        if (
                            str_contains($label, 'PERSONAL SERVICES') ||
                            str_contains($label, 'MAINTENANCE AND OTHER OPERATING EXPENDITURES') ||
                            str_contains($label, 'FINANCIAL EXPENSES')
                        ) {
                            $coeComponents[] = $rowNum;
                        }
                    }

                    $totalCOERow = null;
                    if (!empty($coeComponents)) {
                        $lastCOERow = max($coeComponents);
                        $insertRow = $lastCOERow + 1;

                        // Insert TOTAL COE row
                        $sheet->insertNewRowBefore($insertRow, 1);
                        $sheet->setCellValue("A{$insertRow}", 'Total Current Operating Expenditure (COE):');
                        $sheet->mergeCells("A{$insertRow}:B{$insertRow}");

                        foreach (range('D', 'S') as $col) {
                            $refs = implode(',', array_map(fn($r) => "{$col}{$r}", $coeComponents));
                            $sheet->setCellValue("{$col}{$insertRow}", "=SUM({$refs})");
                        }

                        applyPercentageFormulas($sheet, $insertRow);

                        $totalCOERow = $insertRow;
                    }

                    // === Find the TOTAL CAPITAL OUTLAY (CO) row (excluding CONTINUING) ===
                    $totalCORow = null;
                    foreach ($totalsMap as $label => $rowNum) {
                        $cleanLabel = strtoupper(trim($label, " :"));

                        if ($cleanLabel === 'TOTAL CAPITAL OUTLAY (CO)' && !str_contains($cleanLabel, 'CONTINUING')) {
                            $totalCORow = $rowNum;
                            break;
                        }
                    }

                    // === Insert TOTAL COE + CO row right after Total Capital Outlay ===
                    if ($totalCOERow && $totalCORow) {
                        $insertRow = $totalCORow + 2;

                        $sheet->insertNewRowBefore($insertRow, 1);
                        $sheet->setCellValue("A{$insertRow}", 'Total COE and CO:');
                        $sheet->mergeCells("A{$insertRow}:B{$insertRow}");

                        $totalCOValuesRow = $totalCORow + 1;

                        foreach (range('D', 'S') as $col) {
                            $refs = "{$col}{$totalCOERow},{$col}{$totalCOValuesRow}";
                            $sheet->setCellValue("{$col}{$insertRow}", "=SUM({$refs})");
                        }

                        applyPercentageFormulas($sheet, $insertRow);
                    }
                }

                // === RE-FIND OVERALL TOTAL ROW AND GRAND TOTAL ROWS AFTER ROW INSERTIONS ===
                $highestRow = $sheet->getHighestRow();
                
                $overallTotalRow = null;
                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    if (str_contains($cellValue, 'OVERALL TOTAL')) {
                        $overallTotalRow = $row;
                        break;
                    }
                }

                $grandTotalRows = [];
                $searchEndRow = $overallTotalRow ? $overallTotalRow - 1 : $highestRow;
                for ($row = 13; $row <= $searchEndRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    if (str_contains($cellValue, 'GRAND TOTAL')) {
                        $grandTotalRows[] = $row;
                    }
                }

                // === OVERALL TOTAL ROW ===
                if (!empty($overallTotalRow) && !empty($grandTotalRows)) {
                    foreach (range('D', 'S') as $col) {
                        $refs = implode(',', array_map(fn($r) => "{$col}{$r}", $grandTotalRows));
                        $sheet->setCellValue("{$col}{$overallTotalRow}", "=SUM({$refs})");
                    }
                    applyPercentageFormulas($sheet, $overallTotalRow);

                    // Format number and percentage columns
                    foreach (range('D', 'S') as $column) {
                        if (!in_array($column, ['M', 'O', 'Q', 'R'])) {
                            $sheet->getStyle("{$column}{$overallTotalRow}")
                                ->getNumberFormat()
                                ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                        } else {
                            $sheet->getStyle("{$column}{$overallTotalRow}")
                                ->getNumberFormat()->setFormatCode('0.00%');
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
                    'name' => 'Arial', // Or 'Calibri', 'Verdana', etc.
                    'size' => 10,
                ],
            ],
            'A9:S9' => [
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
        $accountCode = $this->accountCode;
        $asOfDate = $this->asOfDate;
        $isSEFConsolidated = $this->isSEFConsolidated;

        // Only include offices with OfficeAllotmentClasses for the selected year and "Current" category
        $officesQuery = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
            $query->where('year', $selectedYear);
        });

        // Check if selected office is a SEF office, if so get all SEF offices
        if (!empty($selectedOffice)) {
            if ($isSEFConsolidated) {
                // Get all SEF offices
                $officesQuery->where('fund', 'Special Education Fund');
            } else {
                // Otherwise, filter to just the selected office
                $officesQuery->where('id', $selectedOffice);
            }
        }
        $offices = $officesQuery->with([
            'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear);
            },
            'officeAllotmentClasses.allotmentClass',
            'officeAllotmentClasses.appropriations' => function ($query) use ($accountCode) {
                // Add account code filter
                if (!empty($accountCode)) {
                    $query->where('account_code', 'LIKE', $accountCode . '%');
                }
            },
            'officeAllotmentClasses.appropriations.realignments',
            'officeAllotmentClasses.appropriations.supplementals',
            'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
        ])->orderBy('id', 'asc')->get();

        // Filter out offices that have no appropriations after account_code filtering
        if (!empty($accountCode)) {
            $offices = $offices->filter(function($office) {
                foreach ($office->officeAllotmentClasses as $oac) {
                    if ($oac->appropriations->isNotEmpty()) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        $month = Carbon::parse($asOfDate)->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        foreach ($offices as $office) {
            $office->officeAllotmentClasses = $office->officeAllotmentClasses
            ->filter(function($oac) {
                return $oac->appropriations->isNotEmpty();
            })
            ->sortBy(fn ($oac) => $oac->allotmentClass->id)
            ->values();

            foreach ($office->officeAllotmentClasses as $oac) {
                // Apply custom sorting to appropriations
                $oac->appropriations = $this->sortAppropriations($oac->appropriations);
                
                $grouped = $oac->appropriations
                    ->groupBy(fn ($a) => $a->programs ?? '');

                foreach ($grouped as $program => $appropriations) {
                    foreach ($appropriations as $app) {
                        // --- Supplementals ---
                        $sb = $app->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $rev = $app->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        $sbForLater = $app->supplementals
                        ->where('type', 'Supplemental')
                        ->where('supplemental_date', '<=', $asOfDate)
                        ->sum(function ($supp) use ($currentQuarter) {
                            $fl = 0;
                            if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                            if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                            if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                            return $fl;
                        });

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
                                fn($oa) =>
                                $oa->obligation
                                    ? $oa->obligation->obligationAdjustments
                                    ->where('adjustment_date', '<=', $asOfDate)
                                    ->where('obligation_amounts_id', $oa->id) // restrict per obligation_amount of this appropriation
                                    : collect()
                            )
                            ->sum('adjustment_amount');

                        // --- Disbursements ---
                        $disbursement = $app->disbursements
                            ->where('disbursement_date', '<=', $asOfDate)
                            ->sum('disbursement_amount');

                        $obligation = $obligationBase + $obligationAdjustments;

                        $authorized = $app->appropriation + $sb + $rev + $realignment;
                        $allotment = ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4) + $sb + $rev + $realignment;

                        $forLater = 0;
                        if ($currentQuarter < 2) $forLater += $app->quarter2;
                        if ($currentQuarter < 3) $forLater += $app->quarter3;
                        if ($currentQuarter < 4) $forLater += $app->quarter4;
                        $forLater += $sbForLater;

                        $allotment -= $forLater;

                        $app->sb_appropriation = $sb;
                        $app->reversion = $rev;
                        $app->realignment = $realignment;
                        $app->obligation = $obligation;
                        $app->authorized_appropriation = $authorized;
                        $app->allotment = $allotment;
                        $app->for_later_release = $forLater;

                        $app->appropriation_balance = $authorized - $obligation;
                        $app->appropriation_accomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                        $app->disbursement = $disbursement;
                        $app->disbursement_balance = $obligation - $disbursement;
                        $app->disbursement_to_obligation = $obligation > 0 ? ($disbursement / $obligation) * 100 : 0;
                        $app->disbursement_to_appropriation = $authorized > 0 ? ($disbursement / $authorized) * 100 : 0;
                    }
                }

                // Subtotals per program
                $subtotals = [];
                foreach ($grouped as $program => $apps) {
                    if ($program === '') continue;
                    $subtotal = $this->computeTotals($apps);
                    $subtotal['appropriation_accomplishment'] = ($subtotal['authorized_appropriation'] > 0)
                        ? ($subtotal['obligation'] / $subtotal['authorized_appropriation']) * 100
                        : 0;
                    $subtotal['allotment_accomplishment'] = ($subtotal['allotment'] > 0)
                        ? ($subtotal['obligation'] / $subtotal['allotment']) * 100
                        : 0;
                    $subtotals[$program] = $subtotal;
                }
                $total = $this->computeTotals($grouped->get('') ?? collect());
                foreach ($subtotals as $sub) {
                    foreach ($sub as $key => $val) {
                        if ($key !== 'count') $total[$key] += $val;
                    }
                    $total['count'] += $sub['count'];
                }
                $total['appropriation_accomplishment'] = ($total['authorized_appropriation'] > 0)
                    ? ($total['obligation'] / $total['authorized_appropriation']) * 100
                    : 0;
                $total['allotment_accomplishment'] = ($total['allotment'] > 0)
                    ? ($total['obligation'] / $total['allotment']) * 100
                    : 0;
                $oac->groupedAppropriations = $grouped;
                $oac->groupSubtotals = $subtotals;
                $oac->groupTotal = $total;
            }
            $gt = $this->computeOfficeTotal($office->officeAllotmentClasses);
            $gt['appropriation_accomplishment'] = ($gt['authorized_appropriation'] > 0)
                ? ($gt['obligation'] / $gt['authorized_appropriation']) * 100
                : 0;
            $gt['allotment_accomplishment'] = ($gt['allotment'] > 0)
                ? ($gt['obligation'] / $gt['allotment']) * 100
                : 0;
            $office->grandTotal = $gt;
        }

        // Get account code description
        $accountCodeDisplay = null;
        if (!empty($this->accountCode)) {
            $accountCodeObj = AccountCode::where('code', $this->accountCode)->first();
            if ($accountCodeObj) {
                $accountCodeDisplay = $accountCodeObj->code . ' - ' . $accountCodeObj->description;
            } else {
                $accountCodeDisplay = $this->accountCode;
            }
        }

        // Calculate overall totals if all offices are selected or SEF is consolidated
        $overallTotal = null;
        if ((empty($selectedOffice) || $this->isSEFConsolidated) && count($offices) > 0) {
            $allOacs = collect($offices)->flatMap(function($office) {
                return $office->officeAllotmentClasses;
            });
            $overallTotal = $this->computeOfficeTotal($allOacs);
        }

        // Pass signatory info to the view as well
        return view('exports.saaodb', [
            'offices' => $offices,
            'selectedYear' => $selectedYear,
            'selectedOffice' => $selectedOffice,
            'accountCode' => $accountCode,
            'accountCodeDisplay' => $accountCodeDisplay,
            'overallTotal' => $overallTotal,
            'asOfDate' => $asOfDate,
            'isSEFConsolidated' => $this->isSEFConsolidated,
            'preparedSignatoryName' => $this->preparedSignatoryName,
            'preparedSignatoryDesignation' => $this->preparedSignatoryDesignation,
            'certifiedSignatoryName' => $this->certifiedSignatoryName,
            'certifiedSignatoryDesignation' => $this->certifiedSignatoryDesignation,
            'isSEFConsolidated' => $this->isSEFConsolidated,
        ]);
    }

    private function computeTotals($appropriations)
    {
        $total = [
            'appropriation' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'for_later_release' => 0,
            'obligation' => 0,
            'appropriation_balance' => 0,
            'appropriation_accomplishment' => 0,
            'allotment_balance' => 0,
            'allotment_accomplishment' => 0,
            'count' => 0,
        ];
        foreach ($appropriations as $app) {
            foreach ($total as $key => $val) {
                if ($key !== 'count') $total[$key] += $app->$key ?? 0;
            }
            $total['count']++;
        }
        $total['appropriation_accomplishment'] = $total['count'] > 0
            ? $total['appropriation_accomplishment'] / $total['count']
            : 0;
        $total['allotment_accomplishment'] = $total['count'] > 0
            ? $total['allotment_accomplishment'] / $total['count']
            : 0;
        return $total;
    }

    private function computeOfficeTotal($oacs)
    {
        $officeTotal = [
            'appropriation' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'for_later_release' => 0,
            'obligation' => 0,
            'appropriation_balance' => 0,
            'appropriation_accomplishment' => 0,
            'allotment_balance' => 0,
            'allotment_accomplishment' => 0,
            'count' => 0,
        ];
        foreach ($oacs as $oac) {
            foreach ($officeTotal as $key => $val) {
                if ($key !== 'count') $officeTotal[$key] += $oac->groupTotal[$key] ?? 0;
            }
            $officeTotal['count'] += $oac->groupTotal['count'] ?? 0;
        }
        $officeTotal['appropriation_accomplishment'] = $officeTotal['count'] > 0
            ? $officeTotal['appropriation_accomplishment'] / $officeTotal['count']
            : 0;
        $officeTotal['allotment_accomplishment'] = $officeTotal['count'] > 0
            ? $officeTotal['allotment_accomplishment'] / $officeTotal['count']
            : 0;
        return $officeTotal;
    }
}
