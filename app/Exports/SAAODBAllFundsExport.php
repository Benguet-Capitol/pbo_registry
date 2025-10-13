<?php

namespace App\Exports;

use App\Models\Fund;
use Illuminate\Contracts\View\View;
use App\Models\Office;
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


class SAAODBAllFundsExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $asOfDate;
    protected $preparedSignatoryName;
    protected $preparedSignatoryDesignation;
    protected $certifiedSignatoryName;
    protected $certifiedSignatoryDesignation;

    public function __construct($selectedYear, $asOfDate, $preparedSignatoryName, $preparedSignatoryDesignation, $certifiedSignatoryName, $certifiedSignatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
        $this->asOfDate = $asOfDate;
        $this->preparedSignatoryName = $preparedSignatoryName;
        $this->preparedSignatoryDesignation = $preparedSignatoryDesignation;
        $this->certifiedSignatoryName = $certifiedSignatoryName;
        $this->certifiedSignatoryDesignation = $certifiedSignatoryDesignation;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Freeze rows above row 11
                $sheet->freezePane('A11');

                // Set rows 5 to 9 to repeat on printed pages
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 11);

                // Identify the certified correct row
                $certifiedRow = null;
                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("D{$row}")->getValue()));
                    if (str_contains($cellValue, 'PREPARED BY')) {
                        $certifiedRow = $row;
                        break;
                    }
                }

                // Default to 2 rows above certified correct row, or fallback to highestRow
                $lastDataRow = $certifiedRow ? $certifiedRow - 2 : $highestRow;

               // Format number columns as Accounting without currency symbol
                foreach (range('B', 'Q') as $column) {
                    if (!in_array($column, ['K', 'M', 'O', 'P'])) {
                        $sheet->getStyle("{$column}13:{$column}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                }

                // Format percentage columns
                foreach (['K', 'M', 'O', 'P'] as $column) {
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
                        for ($i = $row - 1; $i >= 13; $i--) {
                            $check = strtoupper(trim((string) $sheet->getCell("A{$i}")->getValue()));
                            if (
                                str_starts_with($check, 'TOTAL') ||
                                str_contains($check, 'GRAND TOTAL') ||
                                str_contains($check, 'CERTIFIED CORRECT')
                            ) break;
                            if (str_starts_with($check, 'SUBTOTAL')) $subtotalRows[] = $i;
                        }
                        if (!empty($subtotalRows)) {
                            foreach (range('D', 'S') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($subtotalRows)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
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

                // === ADD TOTAL COE & TOTAL COE + CO ROWS ===
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
            'A10:S10' => [
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
         $selectedYear = request('year1', date('Y'));
        $asOfDate = request('as_of_filter', now()->toDateString());

        $fundsQuery = Fund::orderBy('id');

        $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

       $funds = $fundsQuery->with([
            'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->with([
                        'allotmentClass',
                        'fundSourceRelation',
                        'appropriations.supplementals',
                        'appropriations.realignments',
                        'appropriations.obligationAmounts.obligation.obligationAdjustments',
                    ]);
            }
        ])->get();

        // --- Helper function for totals
        function computeTotals($classes)
        {
            $totals = [
                'approved_appropriation' => 0,
                'supplemental' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'obligation' => 0,
                'authorized_appropriation_balance' => 0,
                'percent_obligated_to_authorized' => 0,
                'disbursement' => 0,
                'percent_disbursed_to_obligated' => 0,
                'percent_disbursed_to_authorized' => 0,
                'obligation_balance' => 0,
            ];

            foreach ($classes as $class) {
                foreach ($totals as $key => $value) {
                    if (isset($class->$key)) {
                        $totals[$key] += $class->$key;
                    }
                }
            }

            // Derived percentages
            $totals['percent_obligated_to_authorized'] =
                $totals['authorized_appropriation'] > 0
                    ? ($totals['obligation'] / $totals['authorized_appropriation']) * 100
                    : 0;

            $totals['percent_disbursed_to_obligated'] =
                $totals['obligation'] > 0
                    ? ($totals['disbursement'] / $totals['obligation']) * 100
                    : 0;

            $totals['percent_disbursed_to_authorized'] =
                $totals['authorized_appropriation'] > 0
                    ? ($totals['disbursement'] / $totals['authorized_appropriation']) * 100
                    : 0;

            return $totals;
        }

        // --- Main computation
        foreach ($funds as $fund) {
            $officeAllotmentClasses = $fund->officeAllotmentClasses
                ->filter(fn($oac) => $oac->fund === $fund->fund);

            // Group by allotment class
            $groupedByAllotmentClass = $officeAllotmentClasses->groupBy(
                fn($oac) => $oac->allotmentClass->class ?? 'Unknown'
            );

            $allotmentClasses = collect();

            foreach ($groupedByAllotmentClass as $className => $oacGroup) {
                $allotmentClass = $oacGroup->first()->allotmentClass;
                if (!$allotmentClass) continue;

                // --- Approved Appropriation ---
                $approvedAppropriation = $oacGroup->flatMap->appropriations->sum('appropriation');

                // --- Supplementals ---
                $supplemental = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->supplementals
                    ->where('type', 'Supplemental')
                    ->where('supplemental_date', '<=', $asOfDate)
                    ->sum('amount');

                // --- Reversions ---
                $reversion = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->supplementals
                    ->where('type', 'Reversion')
                    ->where('supplemental_date', '<=', $asOfDate)
                    ->sum('amount') * -1;

                // --- Realignments ---
                $realignment = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->realignments
                    ->where('realignment_date', '<=', $asOfDate)
                    ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

                // --- Authorized Appropriation ---
                $authorizedAppropriation = $approvedAppropriation + $supplemental + $reversion + $realignment;

                // --- Allotment ---
                $allotment = $oacGroup
                    ->flatMap->appropriations
                    ->sum(fn($a) => ($a->quarter1 ?? 0) + ($a->quarter2 ?? 0) + ($a->quarter3 ?? 0) + ($a->quarter4 ?? 0))
                    + $supplemental + $reversion + $realignment;

                // --- For Later Release ---
                $forLaterRelease = $oacGroup
                    ->flatMap->appropriations
                    ->sum(fn($a) => $a->for_later_release ?? 0);

                $allotment -= $forLaterRelease;

                // --- Obligations ---
                $obligationBase = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                    ->sum('obr_amount');

                // --- Obligation Adjustments ---
                $obligationAdjustments = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->flatMap(fn($oa) =>
                        $oa->obligation
                            ? $oa->obligation->obligationAdjustments
                                ->where('adjustment_date', '<=', $asOfDate)
                                ->where('obligation_amounts_id', $oa->id)
                            : collect()
                    )
                    ->sum('adjustment_amount');

                $obligation = $obligationBase + $obligationAdjustments;

                // --- Disbursements ---
                $disbursement = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                    ->sum('disbursement_amount');

                // --- Balances and percentages ---
                $authorizedAppropriationBalance = $authorizedAppropriation - $obligation;
                $obligationBalance = $obligation - $disbursement;

                $percentObligatedToAuthorized = $authorizedAppropriation > 0
                    ? ($obligation / $authorizedAppropriation) * 100
                    : 0;

                $percentDisbursedToObligated = $obligation > 0
                    ? ($disbursement / $obligation) * 100
                    : 0;

                $percentDisbursedToAuthorized = $authorizedAppropriation > 0
                    ? ($disbursement / $authorizedAppropriation) * 100
                    : 0;

                // --- Collect final class summary ---
                $allotmentClasses->push((object)[
                    'id' => $allotmentClass->id,
                    'class' => $allotmentClass->class,
                    'approved_appropriation' => $approvedAppropriation,
                    'supplemental' => $supplemental,
                    'reversion' => $reversion,
                    'realignment' => $realignment,
                    'authorized_appropriation' => $authorizedAppropriation,
                    'allotment' => $allotment,
                    'for_later_release' => $forLaterRelease,
                    'obligation' => $obligation,
                    'authorized_appropriation_balance' => $authorizedAppropriationBalance,
                    'percent_obligated_to_authorized' => $percentObligatedToAuthorized,
                    'disbursement' => $disbursement,
                    'percent_disbursed_to_obligated' => $percentDisbursedToObligated,
                    'percent_disbursed_to_authorized' => $percentDisbursedToAuthorized,
                    'obligation_balance' => $obligationBalance,
                ]);
            }

            // Assign computed results under each fund
            $fund->allotmentClasses = $allotmentClasses->values();
            $fund->totals = (object) computeTotals($allotmentClasses);

            // --- Group totals by category ---
            $currentClasses = $allotmentClasses->filter(fn($c) => !str_contains(strtoupper($c->class), 'CCO'));
            $continuingClasses = $allotmentClasses->filter(fn($c) => str_contains(strtoupper($c->class), 'CCO'));

            $fund->total_current = (object) computeTotals($currentClasses);
            $fund->total_continuing = (object) computeTotals($continuingClasses);

            // Combine all for grand total
            $fund->total_overall = (object) computeTotals($allotmentClasses);

            // Ensure default totals always exist even if empty
            foreach (['total_current', 'total_continuing', 'total_overall'] as $key) {
                if (!isset($fund->$key) || !$fund->$key) {
                    $fund->$key = (object)[
                        'approved_appropriation' => 0,
                        'supplemental' => 0,
                        'reversion' => 0,
                        'realignment' => 0,
                        'authorized_appropriation' => 0,
                        'allotment' => 0,
                        'obligation' => 0,
                        'authorized_appropriation_balance' => 0,
                        'percent_obligated_to_authorized' => 0,
                        'disbursement' => 0,
                        'percent_disbursed_to_obligated' => 0,
                        'percent_disbursed_to_authorized' => 0,
                        'obligation_balance' => 0,
                    ];
                }
            }
        }

        // Grand Total
        $grandTotal = (object)[
            'approved_appropriation' => 0,
            'supplemental' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'obligation' => 0,
            'authorized_appropriation_balance' => 0,
            'percent_obligated_to_authorized' => 0, // will compute later
            'disbursement' => 0,
            'percent_disbursed_to_obligated' => 0, // will compute later
            'percent_disbursed_to_authorized' => 0, // will compute later
            'obligation_balance' => 0,
        ];

        // Sum up totals from each fund
        foreach ($funds as $fund) {
            foreach ($grandTotal as $key => $value) {
                if (property_exists($fund->total_overall, $key) && !str_starts_with($key, 'percent')) {
                    $grandTotal->$key += $fund->total_overall->$key ?? 0;
                }
            }
        }

        // Compute percentage-based fields
        $grandTotal->percent_obligated_to_authorized = $grandTotal->authorized_appropriation > 0
            ? ($grandTotal->obligation / $grandTotal->authorized_appropriation) * 100
            : 0;

        $grandTotal->percent_disbursed_to_obligated = $grandTotal->obligation > 0
            ? ($grandTotal->disbursement / $grandTotal->obligation) * 100
            : 0;

        $grandTotal->percent_disbursed_to_authorized = $grandTotal->authorized_appropriation > 0
            ? ($grandTotal->disbursement / $grandTotal->authorized_appropriation) * 100
            : 0;

        // Now attach it (optional)
        $grandTotals = $grandTotal;

        // Pass signatory info to the view as well
        return view('exports.saaodbAllFunds', [
            'funds' => $funds,
            'selectedYear' => $selectedYear,
            'asOfDate' => $asOfDate,
            'preparedSignatoryName' => $this->preparedSignatoryName,
            'preparedSignatoryDesignation' => $this->preparedSignatoryDesignation,
            'certifiedSignatoryName' => $this->certifiedSignatoryName,
            'certifiedSignatoryDesignation' => $this->certifiedSignatoryDesignation,
        ]);
    }

}