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
                    $v = strtoupper(trim((string) $sheet->getCell("D{$r}")->getValue()));
                    if (str_contains($v, 'PREPARED BY')) {
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

                    $sheet->setCellValue("F{$r}", "=B{$r}+C{$r}-D{$r}+E{$r}");
                    $sheet->setCellValue("G{$r}", "=F{$r}-H{$r}");
                    $sheet->setCellValue("K{$r}", "=F{$r}-I{$r}");
                    $sheet->setCellValue("J{$r}", "=IF(F{$r}=0,0,I{$r}/F{$r})");
                    $sheet->setCellValue("L{$r}", "=G{$r}-I{$r}");
                    $sheet->setCellValue("M{$r}", "=IF(G{$r}=0,0,I{$r}/G{$r})");
                    $sheet->setCellValue("O{$r}", "=IF(I{$r}=0,0,N{$r}/I{$r})");
                    $sheet->setCellValue("Q{$r}", "=IF(F{$r}=0,0,N{$r}/F{$r})");
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
                        $sheet->setCellValue("O{$r}", "=IF(I{$r}=0,0,N{$r}/I{$r})");
                        $sheet->setCellValue("Q{$r}", "=IF(F{$r}=0,0,N{$r}/F{$r})");

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
                        $sheet->setCellValue("O{$r}", "=IF(I{$r}=0,0,N{$r}/I{$r})");
                        $sheet->setCellValue("Q{$r}", "=IF(F{$r}=0,0,N{$r}/F{$r})");

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
                    $sheet->setCellValue("O{$totalRow}", "=IF(I{$totalRow}=0,0,N{$totalRow}/I{$totalRow})");
                    $sheet->setCellValue("Q{$totalRow}", "=IF(F{$totalRow}=0,0,N{$totalRow}/F{$totalRow})");
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
                    $sheet->setCellValue("O{$grandTotalRow}", "=IF(I{$grandTotalRow}=0,0,N{$grandTotalRow}/I{$grandTotalRow})");
                    $sheet->setCellValue("Q{$grandTotalRow}", "=IF(F{$grandTotalRow}=0,0,N{$grandTotalRow}/F{$grandTotalRow})");
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