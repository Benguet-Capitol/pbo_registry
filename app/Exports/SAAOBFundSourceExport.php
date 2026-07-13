<?php

namespace App\Exports;

use App\Models\AllotmentClass;
use App\Models\Employee;
use App\Models\Fund;
use App\Models\FundSource;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\ObligationAdjustment;
use App\Models\OfficeAllotmentClass;
use App\Models\Sector;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SAAOBFundSourceExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $selectedFundSource;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;

    public function __construct($selectedYear, $selectedFundSource, $asOfDate, $signatoryName, $signatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
        $this->selectedFundSource = $selectedFundSource;
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
                $sheet->freezePane('A12');

                // Set rows 6 to 10 to repeat on printed pages
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 11);

                // Hide specific columns
                foreach (['C', 'D', 'E', 'B'] as $col) {
                    $sheet->getColumnDimension($col)->setVisible(false);
                }

                // Identify the certified correct row
                $certifiedRow = null;
                for ($row = 12; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    if (str_contains($cellValue, 'CERTIFIED CORRECT')) {
                        $certifiedRow = $row;
                        break;
                    }
                }

                // Default to 2 rows above certified correct row, or fallback to highestRow
                $lastDataRow = $certifiedRow ? $certifiedRow - 2 : $highestRow;


                // Format number columns
                foreach (range('A', 'M') as $column) {
                    if (!in_array($column, ['K', 'M'])) {
                        $sheet->getStyle("{$column}11:{$column}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                }

                // Format percentage columns
                foreach (['K', 'M'] as $column) {
                    $sheet->getStyle("{$column}11:{$column}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('0.00%');
                }

                // Classify rows based on Z column
                $contentRows = [];
                $subtotalRows = [];
                $totalRows = [];
                $grandTotalRow = null;

                for ($row = 12; $row <= $lastDataRow; $row++) {
                    $firstCellValue = trim((string) $sheet->getCell("A{$row}")->getValue());

                    if (
                        str_starts_with(strtolower($firstCellValue), 'total') ||
                        str_contains(strtolower($firstCellValue), 'grand total') ||
                        str_contains(strtolower($firstCellValue), 'certified correct')
                    ) {
                        continue; // Skip subtotal/total/certified rows
                    }

                    // Apply formulas to content rows
                    $sheet->setCellValue("F{$row}", "=D{$row}+E{$row}+B{$row}+C{$row}");
                    $sheet->setCellValue("G{$row}", "=F{$row}-H{$row}");
                    $sheet->setCellValue("J{$row}", "=F{$row}-G{$row}");
                    $sheet->setCellValue("K{$row}", "=IF(F{$row}>0,I{$row}/F{$row},0.00)");
                    $sheet->setCellValue("L{$row}", "=G{$row}-I{$row}");
                    $sheet->setCellValue("M{$row}", "=IF(G{$row}>0,I{$row}/G{$row},0.00)");
                }

                // Utility: Apply percentage formulas for columns M and O
                function applyPercentageFormulas($sheet, $row)
                {
                    $f = "F{$row}";
                    $g = "G{$row}";
                    $i = "I{$row}";
                    $sheet->setCellValue("K{$row}", "=IF($f>0,$i/$f,0)");
                    $sheet->setCellValue("M{$row}", "=IF($g>0,$i/$g,0)");
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

                        foreach (range('B', 'M') as $col) {
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
                            foreach (range('B', 'M') as $col) {
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
            'A11:M11' => [
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
        $selectedFundSource = $this->selectedFundSource;
        $asOfDate = $this->asOfDate;

        $month = Carbon::parse($asOfDate)->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        $fundSourcesQuery = FundSource::whereIn('source', function ($query) {
            $query->select('fund_source')
                ->from('office_allotment_classes')
                ->distinct();
        })
            ->select('source', 'category')
            ->distinct()
            ->orderBy('category', 'desc');

        // Filter by selected category
        if ($selectedFundSource) {
            $fundSourcesQuery->where('category', $selectedFundSource);
        }

        $fundSources = $fundSourcesQuery->with(['officeAllotmentClasses' => function ($query) use ($selectedYear) {
                    $query->where('year', $selectedYear)
                        ->with([
                        'appropriations', // no sorting here
                        'appropriations.realignments',
                        'appropriations.supplementals',
                        'appropriations.obligationAmounts.obligation.obligationAdjustments'
                    ]);
                }
            ])
            ->get()
            ->groupBy('category')
            ->map(function ($sources, $category) use ($currentQuarter, $asOfDate) {
                $fundSourceCodes = $sources->pluck('source');

                $fundsInUse = OfficeAllotmentClass::whereIn('fund_source', $fundSourceCodes)
                    ->where('year', $this->selectedYear)
                    ->pluck('fund')
                    ->unique();

                $fundRecords = Fund::whereIn('fund', $fundsInUse)
                    ->select('fund', 'fund_type')
                    ->get()
                    ->groupBy('fund_type')
                    ->map(function ($funds, $fundType) use ($sources, $currentQuarter, $asOfDate) {

                        $fundData = $funds->map(function ($fund) use ($sources, $currentQuarter, $asOfDate) {
                            // Filter OACs for this fund
                            $oacs = $sources->flatMap->officeAllotmentClasses
                                ->where('fund', $fund->fund);

                            $approved_appropriations = 0;
                            $sb_appropriations = 0;
                            $reversions = 0;
                            $realignments = 0;
                            $obligations = 0;
                            $allotment = 0;
                            $forLaterRelease = 0;

                            foreach ($oacs as $oac) {
                                foreach ($oac->appropriations as $app) {
                                    $approved_appropriations += $app->appropriation;
                                    // Supplemental & Reversion (filter by supplemental_date)
                                    $sb_appropriations += $app->supplementals
                                        ->where('type', 'Supplemental')
                                        ->where('supplemental_date', '<=', $asOfDate)
                                        ->sum('amount');

                                    $reversions += $app->supplementals
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
                                    $forLaterRelease += $sbForLater;

                                    // Realignments (filter by realignment_date)
                                    $realignments += $app->realignments
                                        ->where('realignment_date', '<=', $asOfDate)
                                        ->reduce(fn($carry, $r) =>
                                        $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                                    // Obligations (obr_date <= asOfDate)
                                    $obligationBase = $app->obligationAmounts
                                        ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                                        ->sum('obr_amount');

                                    // Obligation Adjustments (adjustment_date <= asOfDate)
                                    $obligationAdjustments = $app->obligationAmounts
                                        ->flatMap(
                                            fn($oa) => $oa->obligation
                                                ? $oa->obligation->obligationAdjustments->where('adjustment_date', '<=', $asOfDate)
                                                : collect()
                                        )
                                        ->sum('adjustment_amount');
                                    $obligations += $obligationBase + $obligationAdjustments;
                                    $authorized_appropriation = $approved_appropriations + $sb_appropriations + $reversions + $realignments;
                                    if ($currentQuarter < 2) $forLaterRelease += $app->quarter2 ?? 0;
                                    if ($currentQuarter < 3) $forLaterRelease += $app->quarter3 ?? 0;
                                    if ($currentQuarter < 4) $forLaterRelease += $app->quarter4 ?? 0;
                                    $allotment = $authorized_appropriation - $forLaterRelease;
                                }
                            }

                            return [
                                'fund' => $fund->fund,
                                'approved_appropriation' => $approved_appropriations,
                                'sb_appropriation' => $sb_appropriations,
                                'reversion' => $reversions,
                                'realignment' => $realignments,
                                'authorized' => $authorized_appropriation,
                                'obligation' => $obligations,
                                'allotment' => $authorized_appropriation - $forLaterRelease,
                                'for_later_release' => $forLaterRelease,
                                'appropriation_balance' => $authorized_appropriation - $obligations,
                                'appropriation_accomplishment' => $authorized_appropriation > 0 ? ($obligations / $authorized_appropriation) * 100 : 0,
                                'allotment_balance' => $allotment - $obligations,
                                'allotment_accomplishment' => $allotment > 0 ? ($obligations / $allotment) * 100 : 0

                            ];
                        });

                        $totals = [
                            'fund' => 'TOTAL',
                            'approved_appropriation' => $fundData->sum('approved_appropriation'),
                            'sb_appropriation' => $fundData->sum('sb_appropriation'),
                            'reversion' => $fundData->sum('reversion'),
                            'realignment' => $fundData->sum('realignment'),
                            'authorized' => $fundData->sum('authorized'),
                            'obligation' => $fundData->sum('obligation'),
                            'allotment' => $fundData->sum('allotment'),
                            'for_later_release' => $fundData->sum('for_later_release'),
                            'appropriation_balance' => $fundData->sum('appropriation_balance'),
                            'appropriation_accomplishment' => $fundData->sum('authorized') > 0
                                ? ($fundData->sum('obligation') / $fundData->sum('authorized')) * 100
                                : 0,
                            'allotment_balance' => $fundData->sum('allotment_balance'),
                            'allotment_accomplishment' => $fundData->sum('allotment') > 0
                                ? ($fundData->sum('obligation') / $fundData->sum('allotment')) * 100
                                : 0,
                        ];

                        return [
                            'fund_type' => $fundType,
                            'funds' => $fundData->values(),
                            'totals' => $totals,
                        ];
                    })
                    ->values();

                $grandTotals = [
                    'approved_appropriation' => $fundRecords->sum(fn($ft) => $ft['totals']['approved_appropriation']),
                    'sb_appropriation' => $fundRecords->sum(fn($ft) => $ft['totals']['sb_appropriation']),
                    'reversion' => $fundRecords->sum(fn($ft) => $ft['totals']['reversion']),
                    'realignment' => $fundRecords->sum(fn($ft) => $ft['totals']['realignment']),
                    'authorized' => $fundRecords->sum(fn($ft) => $ft['totals']['authorized']),
                    'obligation' => $fundRecords->sum(fn($ft) => $ft['totals']['obligation']),
                    'allotment' => $fundRecords->sum(fn($ft) => $ft['totals']['allotment']),
                    'for_later_release' => $fundRecords->sum(fn($ft) => $ft['totals']['for_later_release']),
                    'appropriation_balance' => $fundRecords->sum(fn($ft) => $ft['totals']['appropriation_balance']),
                    'appropriation_accomplishment' => $fundRecords->sum(fn($ft) => $ft['totals']['authorized']) > 0
                        ? ($fundRecords->sum(fn($ft) => $ft['totals']['obligation']) / $fundRecords->sum(fn($ft) => $ft['totals']['authorized'])) * 100
                        : 0,
                    'allotment_balance' => $fundRecords->sum(fn($ft) => $ft['totals']['allotment_balance']),
                    'allotment_accomplishment' => $fundRecords->sum(fn($ft) => $ft['totals']['allotment']) > 0
                        ? ($fundRecords->sum(fn($ft) => $ft['totals']['obligation']) / $fundRecords->sum(fn($ft) => $ft['totals']['allotment'])) * 100
                        : 0,
                ];

                return [
                    'category' => $category,
                    'fund_types' => $fundRecords,
                    'grand_totals' => $grandTotals,
                ];
            })
            ->values();

        return view('exports.saaobFundSource', [
            'fundSources' => $fundSources,
            'selectedYear' => $selectedYear,
            'selectedFundSource' => $selectedFundSource,
            'asOfDate' => $asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
        ]);
    }
}
