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
use App\Models\Office;
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

class SAAOBGFCurrentSummaryExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;

    public function __construct($selectedYear, $asOfDate, $signatoryName, $signatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
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
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 12);

                // Hide specific columns
                foreach (['C', 'D', 'E', 'B'] as $col) {
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
                foreach (range('A', 'M') as $column) {
                    if (!in_array($column, ['A', 'M'])) {
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
                $totalRows = [];
                $grandTotalRow = null;

                for ($row = 12; $row <= $lastDataRow; $row++) {
                    $firstCellValue = trim((string) $sheet->getCell("A{$row}")->getValue());

                    if (
                        str_contains(strtolower($firstCellValue), 'total') ||
                        str_contains(strtolower($firstCellValue), 'grand total') ||
                        str_contains(strtolower($firstCellValue), 'certified correct')
                    ) {
                        continue; // Skip total/certified rows
                    }

                    // Apply formulas to content rows
                    $sheet->setCellValue("F{$row}", "=D{$row}+E{$row}+B{$row}+C{$row}");
                    $sheet->setCellValue("G{$row}", "=F{$row}-H{$row}");
                    $sheet->setCellValue("J{$row}", "=F{$row}-I{$row}");
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
                for ($row = 12; $row <= $lastDataRow; $row++) {
                    $label = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));

                    // === TOTAL ROW ===
                    if (str_starts_with($label, 'TOTAL')) {
                        $startRow = $row - 1;
                        while ($startRow > 12) {
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
                        if ($startRow < 12) $startRow = 12;

                        foreach (range('B', 'M') as $col) {
                            $sheet->setCellValue("{$col}{$row}", "=SUM({$col}{$startRow}:{$col}" . ($row - 1) . ")");
                        }
                        applyPercentageFormulas($sheet, $row);
                    }

                    // === GRAND TOTAL ROW ===
                    elseif (str_starts_with($label, 'GRAND TOTAL')) {
                        $totalRows = [];
                        for ($i = $row - 1; $i >= 12; $i--) {
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

                    // === OVERALL TOTAL / GRAND TOTAL ROW ===
                    elseif (str_contains($label, 'OVERALL TOTAL')) {
                        $lastContentRow = $row - 1;
                        $firstContentRow = null;

                        // Scan upward until the "OVERALL TOTAL OF ..." header row
                        for ($i = $lastContentRow; $i >= 12; $i--) {
                            $check = strtoupper(trim((string) $sheet->getCell("A{$i}")->getValue()));
                            if (str_contains($check, 'OVERALL TOTAL OF')) {
                                $firstContentRow = $i + 1;
                                break;
                            }
                        }

                        if ($firstContentRow && $lastContentRow >= $firstContentRow) {
                            foreach (range('B', 'M') as $col) {
                                $sheet->setCellValue(
                                    "{$col}{$row}",
                                    "=SUM({$col}{$firstContentRow}:{$col}{$lastContentRow})"
                                );
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
        $selectedYear = request('year1', date('Y'));
        $asOfDate = request('as_of_filter', now()->toDateString());

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        $offices = Office::all();
        $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();

        // Per Fund and Allotment Class
        $funds = Fund::where('fund_type', 'General Fund')
            ->with(['officeAllotmentClasses' => function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas(
                        'allotmentClass',
                        fn($subQuery) =>
                        $subQuery->where('category', 'Current')
                    )
                    ->where('fund_source', '!=', 'Continuing Capital Outlay')
                    ->with([
                        'allotmentClass',
                        'appropriations.supplementals',
                        'appropriations.realignments',
                        'appropriations.obligationAmounts.obligationAdjustments'
                    ])
                    ->orderBy(
                        AllotmentClass::select('id')
                            ->whereColumn('allotment_classes.class', 'office_allotment_classes.class'),
                        'asc'
                    );
            }])
            ->get();

        $month = Carbon::parse($asOfDate)->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        foreach ($funds as $fund) {
            // Group this fund's OACs by class
            $grouped = $fund->officeAllotmentClasses->groupBy('class');

            $presentAllotmentClasses = collect();

            foreach ($grouped as $class => $group) {
                // Take the first OAC's allotment class as a reference
                $allotmentClass = $group->first()->allotmentClass->replicate();
                $allotmentClass->class = $class;

                $oacAppropriations = $group->flatMap->appropriations;

                // Approved Appropriations
                $allotmentClass->approved_appropriations = $oacAppropriations
                    ->sum('appropriation');
                // Supplementals
                $allotmentClass->sb_appropriation = $oacAppropriations
                    ->flatMap->supplementals
                    ->where('type', 'Supplemental')
                    ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                    ->sum('amount');

                // Reversions (negative)
                $allotmentClass->reversion = $oacAppropriations
                    ->flatMap->supplementals
                    ->where('type', 'Reversion')
                    ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                    ->sum('amount') * -1;

                $allotmentClass->sbForLater = $oacAppropriations
                    ->flatMap->supplementals
                    ->where('type', 'Supplemental')
                    ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                    ->sum(function ($supp) use ($currentQuarter) {
                        $fl = 0;
                        if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                        if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                        if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                        return $fl;
                    });

                // Realignments
                $allotmentClass->realignment = $oacAppropriations
                    ->flatMap->realignments
                    ->filter(fn($r) => $asOfDate ? $r->realignment_date <= $asOfDate : true)
                    ->reduce(fn($carry, $r) => $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                // Authorized Appropriation
                $allotmentClass->authorized_appropriation =
                    $allotmentClass->approved_appropriations +
                    $allotmentClass->sb_appropriation +
                    $allotmentClass->reversion +
                    $allotmentClass->realignment;

                // For Later Release (still based on quarter only)
                $allotmentClass->for_later_release = 0;
                if ($currentQuarter < 2) $allotmentClass->for_later_release += $oacAppropriations->sum('quarter2');
                if ($currentQuarter < 3) $allotmentClass->for_later_release += $oacAppropriations->sum('quarter3');
                if ($currentQuarter < 4) $allotmentClass->for_later_release += $oacAppropriations->sum('quarter4');

                $allotmentClass->for_later_release += $allotmentClass->sbForLater;

                // Allotment
                $allotmentClass->allotment = $allotmentClass->authorized_appropriation - $allotmentClass->for_later_release;

                /// Obligations
                $obligationBase = $oacAppropriations
                    ->flatMap->obligationAmounts
                    ->filter(fn($oa) => $asOfDate 
                        ? ($oa->obligation && $oa->obligation->obr_date <= $asOfDate) 
                        : true
                    )
                    ->sum('obr_amount');

                $obligationAdjustments = $oacAppropriations
                    ->flatMap->obligationAmounts
                    ->flatMap->obligationAdjustments
                    ->filter(fn($adj) => $asOfDate ? $adj->adjustment_date <= $asOfDate : true)
                    ->sum('adjustment_amount');

                $allotmentClass->obligation = $obligationBase + $obligationAdjustments;

                // Appropriation Balance
                $allotmentClass->appropriation_balance = $allotmentClass->authorized_appropriation - $allotmentClass->obligation;

                // Appropriation Accomplishment
                $allotmentClass->appropriation_accomplishment = $allotmentClass->authorized_appropriation > 0 ? ($allotmentClass->obligation / $allotmentClass->authorized_appropriation) * 100 : 0;

                // Allotment Balance
                $allotmentClass->allotment_balance = $allotmentClass->allotment - $allotmentClass->obligation;

                // Allotment Accomplishment
                $allotmentClass->allotment_accomplishment = $allotmentClass->allotment > 0 ? ($allotmentClass->obligation / $allotmentClass->allotment) * 100 : 0;


                $presentAllotmentClasses->push($allotmentClass);
            }

            $fund->presentAllotmentClasses = $presentAllotmentClasses->sortBy('id')->values();
        }

        $grandTotal = (object) [
            'approved_appropriations' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'for_later_release' => 0,
            'allotment' => 0,
            'obligation' => 0,
            'appropriation_balance' => 0,
            'appropriation_accomplishment' => 0,
            'allotment_balance' => 0,
            'allotment_accomplishment' => 0,
        ];

        foreach ($funds as $fund) {
            // Calculate totals for this fund
            $fundTotal = (object) [
                'approved_appropriations' => $fund->presentAllotmentClasses->sum('approved_appropriations'),
                'sb_appropriation' => $fund->presentAllotmentClasses->sum('sb_appropriation'),
                'reversion' => $fund->presentAllotmentClasses->sum('reversion'),
                'realignment' => $fund->presentAllotmentClasses->sum('realignment'),
                'authorized_appropriation' => $fund->presentAllotmentClasses->sum('authorized_appropriation'),
                'for_later_release' => $fund->presentAllotmentClasses->sum('for_later_release'),
                'allotment' => $fund->presentAllotmentClasses->sum('allotment'),
                'obligation' => $fund->presentAllotmentClasses->sum('obligation'),
                'appropriation_balance' => $fund->presentAllotmentClasses->sum('appropriation_balance'),
                'allotment_balance' => $fund->presentAllotmentClasses->sum('allotment_balance'),
            ];

            // % accomplishments
            $fundTotal->appropriation_accomplishment = $fundTotal->authorized_appropriation > 0
                ? ($fundTotal->obligation / $fundTotal->authorized_appropriation) * 100
                : 0;

            $fundTotal->allotment_accomplishment = $fundTotal->allotment > 0
                ? ($fundTotal->obligation / $fundTotal->allotment) * 100
                : 0;

            $fund->total = $fundTotal;

            // Add to grand total
            foreach ($grandTotal as $key => $value) {
                if (in_array($key, ['appropriation_accomplishment', 'allotment_accomplishment'])) {
                    continue;
                }
                $grandTotal->$key += $fundTotal->$key;
            }
        }

        // Compute grand total % accomplishments
        $grandTotal->appropriation_accomplishment = $grandTotal->authorized_appropriation > 0
            ? ($grandTotal->obligation / $grandTotal->authorized_appropriation) * 100
            : 0;

        $grandTotal->allotment_accomplishment = $grandTotal->allotment > 0
            ? ($grandTotal->obligation / $grandTotal->allotment) * 100
            : 0;

        $grand = $grandTotal;


        // Per sector
        $sectors = Sector::orderBy('sector_code')
            ->get()
            ->map(function ($sector) use ($currentQuarter, $selectedYear, $asOfDate) {
                $presentAllotmentClasses = OfficeAllotmentClass::where('year', $selectedYear)
                    ->where('fund_source', '!=', 'Continuing Capital Outlay')
                    ->whereIn('fund', ['General Fund', 'Provincial Development Fund'])
                    ->whereHas('allotmentClass', function ($q) {
                        $q->where('category', 'Current');
                    })
                    ->whereHas('appropriations', function ($q) use ($sector) {
                        $q->where('fpp_code', 'like', $sector->sector_code . '%');
                    })
                    ->with([
                        'allotmentClass',
                        'appropriations.supplementals',
                        'appropriations.realignments',
                        'appropriations.obligationAmounts.obligationAdjustments',
                    ])
                    ->get()
                    ->groupBy('allotmentClass.class')
                    ->map(function ($group) use ($currentQuarter, $sector, $asOfDate) {
                        $allotmentClass = $group->first()->allotmentClass;

                        // Filter appropriations per sector here
                        $oacAppropriations = $group->flatMap->appropriations
                            ->filter(fn($app) => str_starts_with($app->fpp_code, $sector->sector_code));

                        $approvedAppropriations = $oacAppropriations->sum('appropriation');

                        $sbAppropriation = $oacAppropriations
                            ->flatMap->supplementals
                            ->where('type', 'Supplemental')
                            ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                            ->sum('amount');

                        $reversion = $oacAppropriations
                            ->flatMap->supplementals
                            ->where('type', 'Reversion')
                            ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                            ->sum('amount') * -1;

                        $sbForLater = $oacAppropriations
                            ->flatMap->supplementals
                            ->where('type', 'Supplemental')
                            ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                            ->sum(function ($supp) use ($currentQuarter) {
                                $fl = 0;
                                if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                                if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                                if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                                return $fl;
                            });

                        $realignment = $oacAppropriations
                            ->flatMap->realignments
                            ->filter(fn($r) => $asOfDate ? $r->realignment_date <= $asOfDate : true)
                            ->reduce(function ($carry, $r) {
                                return $carry + ($r->type === 'Source' ? -$r->amount : $r->amount);
                            }, 0);

                        $authorizedAppropriation =
                            $approvedAppropriations +
                            $sbAppropriation +
                            $reversion +
                            $realignment;

                        $forLaterRelease = 0;
                        if ($currentQuarter < 2) $forLaterRelease += $oacAppropriations->sum('quarter2');
                        if ($currentQuarter < 3) $forLaterRelease += $oacAppropriations->sum('quarter3');
                        if ($currentQuarter < 4) $forLaterRelease += $oacAppropriations->sum('quarter4');

                        $forLaterRelease += $sbForLater;

                        $allotment = $authorizedAppropriation - $forLaterRelease;

                        $obligationBase = $oacAppropriations
                            ->flatMap->obligationAmounts
                            ->filter(fn($oa) => $asOfDate ? $oa->obr_date <= $asOfDate : true)
                            ->sum('obr_amount');

                        $obligationAdjustments = $oacAppropriations
                            ->flatMap->obligationAmounts
                            ->flatMap->obligationAdjustments
                            ->filter(fn($adj) => $asOfDate ? $adj->adjustment_date <= $asOfDate : true)
                            ->sum('adjustment_amount');

                        $obligation = $obligationBase + $obligationAdjustments;

                        $appropriationBalance = $authorizedAppropriation - $obligation;

                        $appropriationAccomplishment = $authorizedAppropriation > 0
                            ? ($obligation / $authorizedAppropriation) * 100
                            : 0;

                        $allotmentBalance = $allotment - $obligation;

                        $allotmentAccomplishment = $allotment > 0
                            ? ($obligation / $allotment) * 100
                            : 0;

                        return (object) [
                            'id' => $allotmentClass->id,
                            'description' => $allotmentClass->description,
                            'approved_appropriations' => $approvedAppropriations,
                            'sb_appropriation' => $sbAppropriation,
                            'reversion' => $reversion,
                            'realignment' => $realignment,
                            'authorized_appropriation' => $authorizedAppropriation,
                            'for_later_release' => $forLaterRelease,
                            'allotment' => $allotment,
                            'obligation' => $obligation,
                            'appropriation_balance' => $appropriationBalance,
                            'appropriation_accomplishment' => $appropriationAccomplishment,
                            'allotment_balance' => $allotmentBalance,
                            'allotment_accomplishment' => $allotmentAccomplishment,
                        ];
                    })
                    ->sortBy('id')
                    ->values();

                // ---- Compute sector totals ----
                $sector->presentAllotmentClasses = $presentAllotmentClasses;
                $sector->totals = (object) [
                    'approved_appropriations' => $presentAllotmentClasses->sum('approved_appropriations'),
                    'sb_appropriation' => $presentAllotmentClasses->sum('sb_appropriation'),
                    'reversion' => $presentAllotmentClasses->sum('reversion'),
                    'realignment' => $presentAllotmentClasses->sum('realignment'),
                    'authorized_appropriation' => $presentAllotmentClasses->sum('authorized_appropriation'),
                    'for_later_release' => $presentAllotmentClasses->sum('for_later_release'),
                    'allotment' => $presentAllotmentClasses->sum('allotment'),
                    'obligation' => $presentAllotmentClasses->sum('obligation'),
                    'appropriation_balance' => $presentAllotmentClasses->sum('appropriation_balance'),
                    'allotment_balance' => $presentAllotmentClasses->sum('allotment_balance'),
                ];

                // Recompute accomplishments at sector-level (%)
                $sector->totals->appropriation_accomplishment =
                    $sector->totals->authorized_appropriation > 0
                    ? ($sector->totals->obligation / $sector->totals->authorized_appropriation) * 100
                    : 0;

                $sector->totals->allotment_accomplishment =
                    $sector->totals->allotment > 0
                    ? ($sector->totals->obligation / $sector->totals->allotment) * 100
                    : 0;

                return $sector;
            });

        // ---- Compute grand totals across all sectors ----
        $grandTotals = (object) [
            'approved_appropriations' => $sectors->sum(fn($s) => $s->totals->approved_appropriations),
            'sb_appropriation' => $sectors->sum(fn($s) => $s->totals->sb_appropriation),
            'reversion' => $sectors->sum(fn($s) => $s->totals->reversion),
            'realignment' => $sectors->sum(fn($s) => $s->totals->realignment),
            'authorized_appropriation' => $sectors->sum(fn($s) => $s->totals->authorized_appropriation),
            'for_later_release' => $sectors->sum(fn($s) => $s->totals->for_later_release),
            'allotment' => $sectors->sum(fn($s) => $s->totals->allotment),
            'obligation' => $sectors->sum(fn($s) => $s->totals->obligation),
            'appropriation_balance' => $sectors->sum(fn($s) => $s->totals->appropriation_balance),
            'allotment_balance' => $sectors->sum(fn($s) => $s->totals->allotment_balance),
        ];

        // Recompute accomplishments at grand-total level
        $grandTotals->appropriation_accomplishment =
            $grandTotals->authorized_appropriation > 0
            ? ($grandTotals->obligation / $grandTotals->authorized_appropriation) * 100
            : 0;

        $grandTotals->allotment_accomplishment =
            $grandTotals->allotment > 0
            ? ($grandTotals->obligation / $grandTotals->allotment) * 100
            : 0;

        $allotmentClasses = AllotmentClass::where('category', 'Current')
            ->orderBy('id')
            ->with(['officeAllotmentClasses' => function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->where('fund_source', '!=', 'Continuing Capital Outlay')
                    ->whereIn('fund', ['General Fund', 'Provincial Development Fund'])
                    ->with(['appropriations.supplementals', 'appropriations.realignments', 'appropriations.obligationAmounts.obligationAdjustments']);
            }])
            ->get();

        $computedAllotmentClasses = collect();

        foreach ($allotmentClasses as $allotmentClass) {

            // Filter appropriations for all sectors combined
            $oacAppropriations = $allotmentClass->officeAllotmentClasses
                ->flatMap->appropriations
                ->filter(function ($app) use ($sectors) {
                    // Keep if fpp_code starts with any sector code
                    return $sectors->contains(fn($sector) => str_starts_with($app->fpp_code, $sector->sector_code));
                });

            if ($oacAppropriations->isEmpty()) continue; // skip if no appropriations

            // === COMPUTATIONS ===
            $approvedAppropriations = $oacAppropriations->sum('appropriation');

            $sbAppropriation = $oacAppropriations
                ->flatMap->supplementals
                ->where('type', 'Supplemental')
                ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                ->sum('amount');

            $reversion = $oacAppropriations
                ->flatMap->supplementals
                ->where('type', 'Reversion')
                ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                ->sum('amount') * -1;

            $sbForLater = $oacAppropriations
                ->flatMap->supplementals
                ->where('type', 'Supplemental')
                ->filter(fn($s) => $asOfDate ? $s->supplemental_date <= $asOfDate : true)
                ->sum(function ($supp) use ($currentQuarter) {
                    $fl = 0;
                    if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                    if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                    if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                    return $fl;
                });

            $realignment = $oacAppropriations
                ->flatMap->realignments
                ->filter(fn($r) => $asOfDate ? $r->realignment_date <= $asOfDate : true)
                ->reduce(fn($carry, $r) => $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

            $authorizedAppropriation =
                $approvedAppropriations +
                $sbAppropriation +
                $reversion +
                $realignment;

            // For Later Release based on current quarter
            $forLaterRelease = 0;
            if ($currentQuarter < 2) $forLaterRelease += $oacAppropriations->sum('quarter2');
            if ($currentQuarter < 3) $forLaterRelease += $oacAppropriations->sum('quarter3');
            if ($currentQuarter < 4) $forLaterRelease += $oacAppropriations->sum('quarter4');

            $forLaterRelease += $sbForLater;

            $allotment = $authorizedAppropriation - $forLaterRelease;

            // Obligations (filter by obligation date)
            $obligationBase = $oacAppropriations
                ->flatMap->obligationAmounts
                ->filter(fn($oa) => $oa->obligation && ($asOfDate ? $oa->obligation->obr_date <= $asOfDate : true))
                ->sum('obr_amount');

            $obligationAdjustments = $oacAppropriations
                ->flatMap->obligationAmounts
                ->flatMap->obligationAdjustments
                ->filter(fn($adj) => $asOfDate ? $adj->adjustment_date <= $asOfDate : true)
                ->sum('adjustment_amount');

            $obligation = $obligationBase + $obligationAdjustments;

            // Balances & Accomplishments
            $appropriationBalance = $authorizedAppropriation - $obligation;
            $appropriationAccomplishment = $authorizedAppropriation > 0
                ? ($obligation / $authorizedAppropriation) * 100
                : 0;

            $allotmentBalance = $allotment - $obligation;
            $allotmentAccomplishment = $allotment > 0
                ? ($obligation / $allotment) * 100
                : 0;

            // Add to final collection
            $computedAllotmentClasses->push((object) [
                'allotment_class' => $allotmentClass->description,
                'approved_appropriations' => $approvedAppropriations,
                'sb_appropriation' => $sbAppropriation,
                'reversion' => $reversion,
                'realignment' => $realignment,
                'authorized_appropriation' => $authorizedAppropriation,
                'for_later_release' => $forLaterRelease,
                'allotment' => $allotment,
                'obligation' => $obligation,
                'appropriation_balance' => $appropriationBalance,
                'appropriation_accomplishment' => $appropriationAccomplishment,
                'allotment_balance' => $allotmentBalance,
                'allotment_accomplishment' => $allotmentAccomplishment,
            ]);
        }
        $computedAllotmentClasses = $computedAllotmentClasses->sortBy('id')->values();

        $overAllGrandTotal = (object) [
            'approved_appropriations' => $computedAllotmentClasses->sum('approved_appropriations'),
            'sb_appropriation'        => $computedAllotmentClasses->sum('sb_appropriation'),
            'reversion'               => $computedAllotmentClasses->sum('reversion'),
            'realignment'             => $computedAllotmentClasses->sum('realignment'),
            'authorized_appropriation' => $computedAllotmentClasses->sum('authorized_appropriation'),
            'for_later_release'       => $computedAllotmentClasses->sum('for_later_release'),
            'allotment'               => $computedAllotmentClasses->sum('allotment'),
            'obligation'              => $computedAllotmentClasses->sum('obligation'),
            'appropriation_balance'   => $computedAllotmentClasses->sum('appropriation_balance'),
            'allotment_balance'       => $computedAllotmentClasses->sum('allotment_balance'),
            // Weighted average for accomplishments
            'appropriation_accomplishment' => $computedAllotmentClasses->sum(function ($item) {
                return $item->authorized_appropriation > 0
                    ? $item->obligation
                    : 0;
            }) / max($computedAllotmentClasses->sum('authorized_appropriation'), 1) * 100,
            'allotment_accomplishment' => $computedAllotmentClasses->sum(function ($item) {
                return $item->allotment > 0
                    ? $item->obligation
                    : 0;
            }) / max($computedAllotmentClasses->sum('allotment'), 1) * 100,
        ];

        return view('exports.saaobGFCurrentSummary', [
            'sectors' => $sectors,
            'selectedYear' => $selectedYear,
            'asOfDate' => $asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
            'overallGrandTotal' => $overAllGrandTotal,
            'funds' => $funds,
            'grandTotal' => $grandTotal,
            'grandTotals' => $grandTotals,
            'computedAllotmentClasses' => $computedAllotmentClasses,
        ]);
    }
}
