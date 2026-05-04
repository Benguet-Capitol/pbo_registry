<?php

namespace App\Exports;

use App\Models\AllotmentClass;
use App\Models\Office;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use App\Models\ObligationAdjustment;
use App\Models\Disbursement;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class SAAODBGFExport implements FromView, WithStyles, WithEvents
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

                // Freeze and repeat header rows
                $sheet->freezePane('A11');
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 11);

                // Hide specific columns
                    foreach (['E', 'H', 'L', 'M'] as $col) {
                        $sheet->getColumnDimension($col)->setVisible(false);
                    }

                // detect signature area
                $certifiedRow = null;
                for ($r = 13; $r <= $highestRow; $r++) {
                    $v = strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));
                    if (str_contains($v, 'SUMMARY')) {
                        $certifiedRow = $r;
                        break;
                    }
                }
                $lastDataRow = $certifiedRow ? $certifiedRow - 2 : $highestRow;

                // numeric and percent formats
                foreach (range('B', 'Q') as $col) {
                    if (!in_array($col, ['J','M','O','Q'])) {
                        $sheet->getStyle("{$col}13:{$col}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                }
                foreach (['J','M','O','Q'] as $col) {
                    $sheet->getStyle("{$col}13:{$col}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('0.00%');
                }
                
                // Format summary section columns
                if ($certifiedRow !== null) {
                    $summaryStartRow = $certifiedRow + 1;
                    // Format numeric columns (B, C, F) in summary section
                    foreach (['B', 'C', 'F'] as $col) {
                        $sheet->getStyle("{$col}{$summaryStartRow}:{$col}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                    // Format percentage columns (D, G, I) in summary section
                    foreach (['D', 'G', 'I'] as $col) {
                        $sheet->getStyle("{$col}{$summaryStartRow}:{$col}{$highestRow}")
                            ->getNumberFormat()->setFormatCode('0.00%');
                    }
                }

                // content-row formulas
                for ($r = 13; $r <= $lastDataRow; $r++) {
                    $a = strtolower(trim((string) $sheet->getCell("A{$r}")->getValue()));

                    if (
                        str_contains($a, 'total current appropriation') ||
                        str_contains($a, 'total continuing capital outlay') ||
                        str_contains($a, 'total current and continuing') ||
                        str_contains($a, 'grand total') ||
                        str_contains($a, 'prepared by')
                    ) {
                        continue;
                    }

                    $sheet->setCellValue("F{$r}", "=B{$r}+C{$r}+D{$r}+E{$r}");
                    $sheet->setCellValue("G{$r}", "=F{$r}-H{$r}");
                    $sheet->setCellValue("K{$r}", "=F{$r}-I{$r}");
                    $sheet->setCellValue("J{$r}", "=IF(F{$r}=0,0,I{$r}/F{$r})");
                    $sheet->setCellValue("L{$r}", "=G{$r}-I{$r}");
                    $sheet->setCellValue("M{$r}", "=IF(G{$r}=0,0,I{$r}/G{$r})");
                    $sheet->setCellValue("O{$r}", "=IF(F{$r}=0,0,N{$r}/F{$r})");
                    $sheet->setCellValue("Q{$r}", "=IF(I{$r}=0,0,N{$r}/I{$r})");
                    $sheet->setCellValue("P{$r}", "=I{$r}-N{$r}");
                }

                $subtotalCurrentRows = [];
                $subtotalContinuingRows = [];
                $totalRows = [];
                $grandTotalRow = null;

                // detect subtotal and total markers
                for ($r = 13; $r <= $lastDataRow; $r++) {
                    $cellA = strtolower(trim((string) $sheet->getCell("A{$r}")->getValue()));

                    // ---------- SUBTOTAL CURRENT ----------
                    if (str_contains($cellA, 'total current appropriation')) {
                        $start = $r - 1;
                        while ($start >= 13 && trim((string)$sheet->getCell("B{$start}")->getValue()) !== '') {
                            $start--;
                        }
                        $start = max(13, $start + 1);

                        foreach (range('B','Q') as $col) {
                            if (in_array($col, ['J','M','O','Q'])) continue;
                            $sheet->setCellValue("{$col}{$r}", "=SUM({$col}{$start}:{$col}" . ($r - 1) . ")");
                        }

                        $sheet->setCellValue("G{$r}", "=F{$r}-H{$r}");
                        $sheet->setCellValue("K{$r}", "=F{$r}-I{$r}");
                        $sheet->setCellValue("L{$r}", "=G{$r}-I{$r}");
                        $sheet->setCellValue("J{$r}", "=IF(F{$r}=0,0,I{$r}/F{$r})");
                        $sheet->setCellValue("M{$r}", "=IF(G{$r}=0,0,I{$r}/G{$r})");
                        $sheet->setCellValue("O{$r}", "=IF(F{$r}=0,0,N{$r}/F{$r})");
                        $sheet->setCellValue("Q{$r}", "=IF(I{$r}=0,0,N{$r}/I{$r})");

                        $subtotalCurrentRows[] = $r;
                    }

                    // ---------- SUBTOTAL CONTINUING (fixed) ----------
                    if (str_contains($cellA, 'total continuing capital outlay')) {
                        $start = $r - 1;
                        // stop scanning upward once we hit “Total Current Appropriation”
                        while ($start >= 13) {
                            $upperText = strtolower(trim((string)$sheet->getCell("A{$start}")->getValue()));
                            if (str_contains($upperText, 'total current appropriation')) {
                                $start++; // start one row below that
                                break;
                            }
                            if (trim((string)$sheet->getCell("B{$start}")->getValue()) === '') break;
                            $start--;
                        }
                        $start = max(13, $start);

                        foreach (range('B','Q') as $col) {
                            if (in_array($col, ['J','M','O','Q'])) continue;
                            $sheet->setCellValue("{$col}{$r}", "=SUM({$col}{$start}:{$col}" . ($r - 1) . ")");
                        }

                        $sheet->setCellValue("G{$r}", "=F{$r}-H{$r}");
                        $sheet->setCellValue("K{$r}", "=F{$r}-I{$r}");
                        $sheet->setCellValue("L{$r}", "=G{$r}-I{$r}");
                        $sheet->setCellValue("J{$r}", "=IF(F{$r}=0,0,I{$r}/F{$r})");
                        $sheet->setCellValue("M{$r}", "=IF(G{$r}=0,0,I{$r}/G{$r})");
                        $sheet->setCellValue("O{$r}", "=IF(F{$r}=0,0,N{$r}/F{$r})");
                        $sheet->setCellValue("Q{$r}", "=IF(I{$r}=0,0,N{$r}/I{$r})");

                        $subtotalContinuingRows[] = $r;
                    }

                    if (str_contains($cellA, 'total current and continuing')) {
                        $totalRows[] = $r;
                    }

                    if (str_contains($cellA, 'grand total')) {
                        $grandTotalRow = $r;
                    }
                }

                // ---------- TOTAL CURRENT + CONTINUING ----------
                foreach ($totalRows as $totalRow) {
                    $scan = $totalRow - 1;
                    $foundCurrent = null;
                    $foundContinuing = null;
                    while ($scan >= 13 && trim((string)$sheet->getCell("B{$scan}")->getValue()) !== '') {
                        $txt = strtolower(trim((string)$sheet->getCell("A{$scan}")->getValue()));
                        if ($foundCurrent === null && str_contains($txt, 'total current appropriation')) $foundCurrent = $scan;
                        if ($foundContinuing === null && str_contains($txt, 'total continuing capital outlay')) $foundContinuing = $scan;
                        if ($foundCurrent && $foundContinuing) break;
                        $scan--;
                    }

                    foreach (range('B','Q') as $col) {
                        if (in_array($col, ['J','M','O','Q'])) continue;
                        if ($foundCurrent && $foundContinuing) {
                            $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}{$foundCurrent},{$col}{$foundContinuing})");
                        } elseif ($foundCurrent) {
                            $sheet->setCellValue("{$col}{$totalRow}", "={$col}{$foundCurrent}");
                        } elseif ($foundContinuing) {
                            $sheet->setCellValue("{$col}{$totalRow}", "={$col}{$foundContinuing}");
                        }
                    }

                    $sheet->setCellValue("G{$totalRow}", "=F{$totalRow}-H{$totalRow}");
                    $sheet->setCellValue("K{$totalRow}", "=F{$totalRow}-I{$totalRow}");
                    $sheet->setCellValue("L{$totalRow}", "=G{$totalRow}-I{$totalRow}");
                    $sheet->setCellValue("J{$totalRow}", "=IF(F{$totalRow}=0,0,I{$totalRow}/F{$totalRow})");
                    $sheet->setCellValue("M{$totalRow}", "=IF(G{$totalRow}=0,0,I{$totalRow}/G{$totalRow})");
                    $sheet->setCellValue("O{$totalRow}", "=IF(F{$totalRow}=0,0,N{$totalRow}/F{$totalRow})");
                    $sheet->setCellValue("Q{$totalRow}", "=IF(I{$totalRow}=0,0,N{$totalRow}/I{$totalRow})");
                }

                // ---------- GRAND TOTAL ----------
                if ($grandTotalRow !== null && count($totalRows) > 0) {
                    foreach (range('B','Q') as $col) {
                        if (in_array($col, ['J','M','O','Q'])) continue;
                        $refs = array_map(fn($r) => "{$col}{$r}", $totalRows);
                        $sheet->setCellValue("{$col}{$grandTotalRow}", '=' . 'SUM(' . implode(',', $refs) . ')');
                    }

                    $sheet->setCellValue("G{$grandTotalRow}", "=F{$grandTotalRow}-H{$grandTotalRow}");
                    $sheet->setCellValue("K{$grandTotalRow}", "=F{$grandTotalRow}-I{$grandTotalRow}");
                    $sheet->setCellValue("L{$grandTotalRow}", "=G{$grandTotalRow}-I{$grandTotalRow}");
                    $sheet->setCellValue("J{$grandTotalRow}", "=IF(F{$grandTotalRow}=0,0,I{$grandTotalRow}/F{$grandTotalRow})");
                    $sheet->setCellValue("M{$grandTotalRow}", "=IF(G{$grandTotalRow}=0,0,I{$grandTotalRow}/G{$grandTotalRow})");
                    $sheet->setCellValue("O{$grandTotalRow}", "=IF(F{$grandTotalRow}=0,0,N{$grandTotalRow}/F{$grandTotalRow})");
                    $sheet->setCellValue("Q{$grandTotalRow}", "=IF(I{$grandTotalRow}=0,0,N{$grandTotalRow}/I{$grandTotalRow})");
                }

                // ---------- SUMMARY SECTION FORMULAS ----------
                if ($certifiedRow !== null) {
                    $summaryStartRow = $certifiedRow + 1; // Row after "Summary" header
                    
                    // Find all allotment class rows in summary section
                    for ($r = $summaryStartRow; $r <= $highestRow; $r++) {
                        $cellA = trim((string) $sheet->getCell("A{$r}")->getValue());
                        
                        // Skip empty rows, grand total row, and signature rows
                        if (empty($cellA) || 
                            str_contains(strtolower($cellA), 'grand total') ||
                            str_contains(strtolower($cellA), 'prepared by') ||
                            str_contains(strtolower($cellA), 'certified correct')) {
                            continue;
                        }
                        
                        // This is an allotment class row in summary
                        // Find all rows in main data section (13 to $lastDataRow) that match this class
                        $className = $cellA;
                        $matchingRows = [];
                        
                        for ($dataRow = 13; $dataRow <= $lastDataRow; $dataRow++) {
                            $dataCellA = trim((string) $sheet->getCell("A{$dataRow}")->getValue());
                            
                            // Skip totals, grand total, fund headers, and empty rows
                            if (str_contains(strtolower($dataCellA), 'total') ||
                                str_contains(strtolower($dataCellA), 'grand total') ||
                                str_contains(strtolower($dataCellA), 'current and continuing') ||
                                empty($dataCellA)) {
                                continue;
                            }
                            
                            // Check if this row matches the allotment class (exact match)
                            if (strtoupper(trim($dataCellA)) === strtoupper(trim($className))) {
                                // Verify this is not a fund header by checking if it has data in column B
                                // Fund headers typically have empty column B, while data rows have numeric values
                                $dataCellB = trim((string) $sheet->getCell("B{$dataRow}")->getValue());
                                // Include if column B has a value (numeric or non-empty string)
                                if (!empty($dataCellB) || is_numeric($dataCellB)) {
                                    $matchingRows[] = $dataRow;
                                }
                            }
                        }
                        
                        if (!empty($matchingRows)) {
                            // Total Appropriation (Column B) = SUM of Authorized Appropriations (Column F)
                            $fRefs = array_map(fn($row) => "F{$row}", $matchingRows);
                            $sheet->setCellValue("B{$r}", '=' . 'SUM(' . implode(',', $fRefs) . ')');
                            
                            // Total Obligations (Column C) = SUM of Obligations (Column I)
                            $iRefs = array_map(fn($row) => "I{$row}", $matchingRows);
                            $sheet->setCellValue("C{$r}", '=' . 'SUM(' . implode(',', $iRefs) . ')');
                            
                            // % of Accomplishment (Obligations vs Authorized Appropriation) (Column D) = C/B
                            $sheet->setCellValue("D{$r}", "=IF(B{$r}=0,0,C{$r}/B{$r})");
                            
                            // Total Disbursements (Column F) = SUM of Disbursements (Column N)
                            $nRefs = array_map(fn($row) => "N{$row}", $matchingRows);
                            $sheet->setCellValue("F{$r}", '=' . 'SUM(' . implode(',', $nRefs) . ')');
                            
                            // % of Accomplishment (Disbursements vs Authorized Appropriation) (Column G) = F/B
                            $sheet->setCellValue("G{$r}", "=IF(B{$r}=0,0,F{$r}/B{$r})");
                            
                            // % of Accomplishment (Disbursements vs Obligations) (Column I) = F/C
                            $sheet->setCellValue("I{$r}", "=IF(C{$r}=0,0,F{$r}/C{$r})");
                        }
                    }
                    
                    // Grand Total row in summary section
                    for ($r = $summaryStartRow; $r <= $highestRow; $r++) {
                        $cellA = trim((string) $sheet->getCell("A{$r}")->getValue());
                        if (str_contains(strtolower($cellA), 'grand total')) {
                            // Find all summary allotment class rows above this
                            $summaryClassRows = [];
                            for ($sr = $summaryStartRow; $sr < $r; $sr++) {
                                $srCellA = trim((string) $sheet->getCell("A{$sr}")->getValue());
                                if (!empty($srCellA) && 
                                    !str_contains(strtolower($srCellA), 'grand total') &&
                                    !str_contains(strtolower($srCellA), 'summary')) {
                                    $summaryClassRows[] = $sr;
                                }
                            }
                            
                            if (!empty($summaryClassRows)) {
                                // Sum all summary rows
                                $bRefs = array_map(fn($row) => "B{$row}", $summaryClassRows);
                                $sheet->setCellValue("B{$r}", '=' . 'SUM(' . implode(',', $bRefs) . ')');
                                
                                $cRefs = array_map(fn($row) => "C{$row}", $summaryClassRows);
                                $sheet->setCellValue("C{$r}", '=' . 'SUM(' . implode(',', $cRefs) . ')');
                                
                                $sheet->setCellValue("D{$r}", "=IF(B{$r}=0,0,C{$r}/B{$r})");
                                
                                $fRefs = array_map(fn($row) => "F{$row}", $summaryClassRows);
                                $sheet->setCellValue("F{$r}", '=' . 'SUM(' . implode(',', $fRefs) . ')');
                                
                                $sheet->setCellValue("G{$r}", "=IF(B{$r}=0,0,F{$r}/B{$r})");
                                $sheet->setCellValue("I{$r}", "=IF(C{$r}=0,0,F{$r}/C{$r})");
                            }
                            break;
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
            'A10:Q10' => [
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
        $allAllotmentClasses = AllotmentClass::all();

        $officeQuery = Office::where('fund', 'General Fund')->orderBy('id');

        $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

       $offices = $officeQuery->with([
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
        foreach ($offices as $office) {
            $officeAllotmentClasses = $office->officeAllotmentClasses
                ->filter(fn($oac) => $oac->office === $office->id);

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
                $forLaterRelease = 0;
                if ($currentQuarter < 2) $forLaterRelease += $oacGroup->flatMap->appropriations->sum(fn($a) => ($a->quarter2 ?? 0));
                if ($currentQuarter < 3) $forLaterRelease += $oacGroup->flatMap->appropriations->sum(fn($a) => ($a->quarter3 ?? 0));
                if ($currentQuarter < 4) $forLaterRelease += $oacGroup->flatMap->appropriations->sum(fn($a) => ($a->quarter4 ?? 0));

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
                // Get all obligation_amounts_ids for this allotment class group
                $obligationAmountIds = $oacGroup
                    ->flatMap->appropriations
                    ->flatMap->obligationAmounts
                    ->pluck('id')
                    ->toArray();

                $disbursement = Disbursement::whereIn('obligation_amounts_id', $obligationAmountIds)
                    ->where('disbursement_date', '<=', $asOfDate)
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

            // Assign computed results under each office
            $office->allotmentClasses = $allotmentClasses->values();
            $office->totals = (object) computeTotals($allotmentClasses);

            // --- Group totals by category ---
            $currentClasses = $allotmentClasses->filter(fn($c) => !str_contains(strtoupper($c->class), 'CCO'));
            $continuingClasses = $allotmentClasses->filter(fn($c) => str_contains(strtoupper($c->class), 'CCO'));

            $office->total_current = (object) computeTotals($currentClasses);
            $office->total_continuing = (object) computeTotals($continuingClasses);

            // Combine all for grand total
            $office->total_overall = (object) computeTotals($allotmentClasses);

            // Ensure default totals always exist even if empty
            foreach (['total_current', 'total_continuing', 'total_overall'] as $key) {
                if (!isset($office->$key) || !$office->$key) {
                    $office->$key = (object)[
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
        foreach ($offices as $office) {
            foreach ($grandTotal as $key => $value) {
                if (property_exists($office->total_overall, $key) && !str_starts_with($key, 'percent')) {
                    $grandTotal->$key += $office->total_overall->$key ?? 0;
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

        // --- SUMMARY TOTALS PER ALLOTMENT CLASS ---
        $summaryTotals = [];

        $grandSummary = [
            'total_appropriation' => 0,
            'total_obligations' => 0,
            'total_disbursements' => 0,
            'percent_obligation_vs_authorized' => 0,
            'percent_disbursement_vs_authorized' => 0,
            'percent_disbursement_vs_obligation' => 0,
        ];

        foreach ($allAllotmentClasses as $allotmentClass) {
            $className = $allotmentClass->class;
            $totals = [
                'total_appropriation' => 0,
                'total_obligations' => 0,
                'total_disbursements' => 0,
                'percent_obligation_vs_authorized' => 0,
                'percent_disbursement_vs_authorized' => 0,
                'percent_disbursement_vs_obligation' => 0,
            ];

            foreach ($offices as $office) {
                foreach ($office->allotmentClasses as $class) {
                    if (strtoupper(trim($class->class)) === strtoupper(trim($className))) {
                        $totals['total_appropriation'] += $class->authorized_appropriation ?? 0;
                        $totals['total_obligations'] += $class->obligation ?? 0;
                        $totals['total_disbursements'] += $class->disbursement ?? 0;
                    }
                }
            }

            // Compute percentages
            $totals['percent_obligation_vs_authorized'] =
                $totals['total_appropriation'] > 0
                    ? ($totals['total_obligations'] / $totals['total_appropriation']) * 100
                    : 0;

            $totals['percent_disbursement_vs_authorized'] =
                $totals['total_appropriation'] > 0
                    ? ($totals['total_disbursements'] / $totals['total_appropriation']) * 100
                    : 0;

            $totals['percent_disbursement_vs_obligation'] =
                $totals['total_obligations'] > 0
                    ? ($totals['total_disbursements'] / $totals['total_obligations']) * 100
                    : 0;

            $summaryTotals[$className] = $totals;

            // Add to grand summary
            $grandSummary['total_appropriation'] += $totals['total_appropriation'];
            $grandSummary['total_obligations'] += $totals['total_obligations'];
            $grandSummary['total_disbursements'] += $totals['total_disbursements'];
        }

        // Compute overall percentages
        $grandSummary['percent_obligation_vs_authorized'] =
            $grandSummary['total_appropriation'] > 0
                ? ($grandSummary['total_obligations'] / $grandSummary['total_appropriation']) * 100
                : 0;

        $grandSummary['percent_disbursement_vs_authorized'] =
            $grandSummary['total_appropriation'] > 0
                ? ($grandSummary['total_disbursements'] / $grandSummary['total_appropriation']) * 100
                : 0;

        $grandSummary['percent_disbursement_vs_obligation'] =
            $grandSummary['total_obligations'] > 0
                ? ($grandSummary['total_disbursements'] / $grandSummary['total_obligations']) * 100
                : 0;

        // Pass signatory info to the view as well
        return view('exports.saaodbGF', [
            'offices' => $offices,
            'allAllotmentClasses' => $allAllotmentClasses,
            'selectedYear' => $selectedYear,
            'asOfDate' => $asOfDate,
            'preparedSignatoryName' => $this->preparedSignatoryName,
            'preparedSignatoryDesignation' => $this->preparedSignatoryDesignation,
            'certifiedSignatoryName' => $this->certifiedSignatoryName,
            'certifiedSignatoryDesignation' => $this->certifiedSignatoryDesignation,
            'summaryTotals' => $summaryTotals,
            'grandSummary' => $grandSummary,
        ]);
    }

}