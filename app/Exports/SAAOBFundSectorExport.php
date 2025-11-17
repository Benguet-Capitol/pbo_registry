<?php

namespace App\Exports;

use App\Models\AllotmentClass;
use App\Models\Employee;
use App\Models\Fund;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\ObligationAdjustment;
use App\Models\Sector;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SAAOBFundSectorExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $selectedFund;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;

    public function __construct($selectedYear, $selectedFund, $asOfDate, $signatoryName, $signatoryDesignation)
    {
        $this->selectedYear = $selectedYear;
        $this->selectedFund = $selectedFund;
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
                foreach (['C', 'D', 'E', 'F', 'L', 'K'] as $col) {
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
                foreach (range('C', 'N') as $column) {
                    if (!in_array($column, ['L', 'N'])) {
                        $sheet->getStyle("{$column}14:{$column}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    }
                }

                // Format percentage columns
                foreach (['L', 'N'] as $column) {
                    $sheet->getStyle("{$column}14:{$column}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('0.00%');
                }

                // Classify rows based on Z column
                $contentRows = [];
                $subtotalRows = [];
                $totalRows = [];
                $grandTotalRow = null;

                for ($row = 14; $row <= $lastDataRow; $row++) {
                    $firstCellValue = trim((string) $sheet->getCell("A{$row}")->getValue());

                    if (
                        str_starts_with(strtolower($firstCellValue), 'subtotal') ||
                        str_starts_with(strtolower($firstCellValue), 'total') ||
                        str_contains(strtolower($firstCellValue), 'grand total') ||
                        str_contains(strtolower($firstCellValue), 'certified correct')
                    ) {
                        continue; // Skip subtotal/total/certified rows
                    }

                    // Apply formulas to content rows
                    $sheet->setCellValue("G{$row}", "=D{$row}+E{$row}+F{$row}+C{$row}");
                    $sheet->setCellValue("K{$row}", "=G{$row}-J{$row}");
                    $sheet->setCellValue("H{$row}", "=G{$row}-I{$row}");
                    $sheet->setCellValue("L{$row}", "=IF(G{$row}>0,J{$row}/G{$row},0.00)");
                    $sheet->setCellValue("M{$row}", "=H{$row}-J{$row}");
                    $sheet->setCellValue("N{$row}", "=IF(H{$row}>0,J{$row}/H{$row},0.00)");
                }

                // Utility: Apply percentage formulas for columns M and O
                function applyPercentageFormulas($sheet, $row)
                {
                    $g = "G{$row}";
                    $h = "H{$row}";
                    $j = "J{$row}";
                    $sheet->setCellValue("L{$row}", "=IF($g>0,$j/$g,0)");
                    $sheet->setCellValue("N{$row}", "=IF($h>0,$j/$h,0)");
                }

                for ($row = 14; $row <= $lastDataRow; $row++) {
                    $marker = strtolower(trim((string) $sheet->getCell("O{$row}")->getValue()));

                    // === SUBTOTAL ROW ===
                    if ($marker === 'subtotal') {
                        $startRow = $row - 1;
                        while ($startRow > 14) {
                            $prevMarker = strtolower(trim((string) $sheet->getCell("O{$startRow}")->getValue()));
                            if (in_array($prevMarker, ['subtotal', 'total-subtotal', 'total-by-class', 'grand-total', 'certified'])) {
                                $startRow++;
                                break;
                            }
                            $startRow--;
                        }
                        if ($startRow < 14) $startRow = 14;

                        foreach (range('C', 'N') as $col) {
                            $sheet->setCellValue("{$col}{$row}", "=SUM({$col}{$startRow}:{$col}" . ($row - 1) . ")");
                        }
                        applyPercentageFormulas($sheet, $row);
                    }

                    // === TOTAL FOR SUBTOTALS ===
                    elseif ($marker === 'total-subtotal') {
                        $subtotalRows = [];
                        for ($i = $row - 1; $i >= 14; $i--) {
                            $checkMarker = strtolower(trim((string) $sheet->getCell("O{$i}")->getValue()));
                            if (in_array($checkMarker, ['total-subtotal', 'total-by-class', 'grand-total', 'certified'])) break;
                            if ($checkMarker === 'subtotal') $subtotalRows[] = $i;
                        }
                        if (!empty($subtotalRows)) {
                            foreach (range('C', 'N') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($subtotalRows)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
                    }

                    // === TOTAL BY ALLOTMENT CLASS ===
                    elseif ($marker === 'total-by-class') {
                        $classRows = [];

                        for ($i = $row - 1; $i >= 14; $i--) {
                            $checkMarker = strtolower(trim((string) $sheet->getCell("O{$i}")->getValue()));

                            // Stop if we hit any subtotal/total/grand-total markers
                            if (in_array($checkMarker, ['subtotal', 'total-subtotal', 'total-by-class', 'grand-total', 'certified'])) {
                                break;
                            }

                            // Only include if not a labeled total/subtotal/grand-total row
                            $classRows[] = $i;
                        }

                        if (!empty($classRows)) {
                            foreach (range('C', 'N') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($classRows)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
                    }
                    // === GRAND TOTAL ===
                    elseif ($marker === 'grand-total') {
                        $totalSubtotals = [];
                        for ($i = $row - 1; $i >= 14; $i--) {
                            $checkMarker = strtolower(trim((string) $sheet->getCell("O{$i}")->getValue()));
                            if (in_array($checkMarker, ['grand-total', 'certified'])) break;
                            if ($checkMarker === 'total-by-class') $totalSubtotals[] = $i;
                        }
                        if (!empty($totalSubtotals)) {
                            foreach (range('C', 'N') as $col) {
                                $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($totalSubtotals)));
                                $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                            }
                            applyPercentageFormulas($sheet, $row);
                        }
                    }
                }
                // Hide marker column O
                $sheet->getColumnDimension('O')->setVisible(false);
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
            'A11:N11' => [
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
        $selectedFund = $this->selectedFund;
        $asOfDate = $this->asOfDate;

        $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();
        $sectors = Sector::all();
        $allFunds = Fund::select('fund_type')->distinct()->orderBy('id')->pluck('fund_type');
        $fundsQuery = Fund::query();
        $allotmentClasses = AllotmentClass::all()->keyBy('class');

        $month = Carbon::parse($asOfDate)->month;
        $currentQuarter = match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };

        if ($selectedFund === 'others') {
            $fundsQuery->whereIn('fund_type', [
                'Benguet General Hospital Economic Enterprise',
                'Special Education Fund'
            ]);
        } elseif ($selectedFund) {
            $fundsQuery->where('fund_type', $selectedFund);
        }

        $funds = $fundsQuery->with([
            'officeAllotmentClasses' => fn($query) => $query->where('year', $selectedYear),
            'officeAllotmentClasses.fundSourceRelation',
            'officeAllotmentClasses.appropriations',
            'officeAllotmentClasses.appropriations.supplementals',
            'officeAllotmentClasses.appropriations.realignments',
            'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
        ])->get();

        $groupedFunds = $funds->groupBy('fund_type')->map(function ($fundGroup, $type) use ($sectors, $allotmentClasses, $currentQuarter, $asOfDate) {
            $mergedOACs = $fundGroup->flatMap->officeAllotmentClasses;
            $baseFund = $fundGroup->first()->replicate();
            $baseFund->fund_type = $type;
            $baseFund->setRelation('officeAllotmentClasses', $mergedOACs);

            $groupedByCategory = $mergedOACs->groupBy(
                fn($oac) =>
                optional($oac->fundSourceRelation)->category ?? 'Uncategorized'
            );

            $baseFund->setRelation('groupedOACs', $groupedByCategory);

            $categoryClassStats = collect();

            foreach ($groupedByCategory as $category => $oacs) {
                $classMap = collect();

                foreach ($oacs as $oac) {
                    $oacClass = $allotmentClasses->get($oac->class);
                    if (!$oacClass) continue;

                    foreach ($oac->appropriations as $appropriation) {
                        $classKey = $oac->class;

                        // Pre-calculate values
                        $approved = $appropriation->appropriation;
                        // Supplemental & Reversion (filter by supplemental_date)
                        $supplemental = $appropriation->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $reversion = $appropriation->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        // Realignments (filter by realignment_date)
                        $realignment = $appropriation->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

                        // Authorized Appropriation
                        $authorized = $approved + $supplemental + $realignment + $reversion;

                        // Allotment (respect date-filtered adjustments too)
                        $allotment = ($appropriation->quarter1 + $appropriation->quarter2 + $appropriation->quarter3 + $appropriation->quarter4)
                            + $supplemental + $reversion + $realignment;

                        // For Later Release (unchanged, but still dynamic by quarter)
                        $forLaterRelease = $appropriation->for_later_release ?? (
                            ($currentQuarter < 2 ? ($appropriation->quarter2 ?? 0) : 0) +
                            ($currentQuarter < 3 ? ($appropriation->quarter3 ?? 0) : 0) +
                            ($currentQuarter < 4 ? ($appropriation->quarter4 ?? 0) : 0)
                        );

                        $allotment -= $forLaterRelease;

                        // Obligations (filter by obr_date)
                        $obligationBase = $appropriation->obligationAmounts
                            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        // Obligation Adjustments (filter by adjustment_date)
                        $obligationAdjustments = $appropriation->obligationAmounts
                            ->flatMap(
                                fn($oa) => $oa->obligation
                                    ? $oa->obligation->obligationAdjustments->where('adjustment_date', '<=', $asOfDate)
                                    : collect()
                            )
                            ->sum('adjustment_amount');
                        $obligation = $obligationBase + $obligationAdjustments;

                        $appropriationBalance = $authorized - $obligation;
                        $appropriationAccomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                        $allotmentBalance = $allotment - $obligation;
                        $allotmentAccomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

                        if (!$classMap->has($classKey)) {
                            $classMap[$classKey] = (object) [
                                'description' => $oacClass->description,
                                'approved_appropriation' => 0,
                                'supplemental' => 0,
                                'reversion' => 0,
                                'realignment' => 0,
                                'authorized_appropriation' => 0,
                                'allotment' => 0,
                                'for_later_release' => 0,
                                'obligations' => 0,
                                'appropriation_balance' => 0,
                                'appropriation_accomplishment' => 0,
                                'allotment_balance' => 0,
                                'allotment_accomplishment' => 0,
                                'count' => 0,
                            ];
                        }

                        $row = $classMap[$classKey];
                        $row->approved_appropriation += $approved;
                        $row->supplemental += $supplemental;
                        $row->reversion += $reversion;
                        $row->realignment += $realignment;
                        $row->authorized_appropriation += $authorized;
                        $row->allotment += $allotment;
                        $row->for_later_release += $forLaterRelease;
                        $row->obligations += $obligation;
                        $row->appropriation_balance += $appropriationBalance;
                        $row->allotment_balance += $allotmentBalance;
                        $row->appropriation_accomplishment += $appropriationAccomplishment;
                        $row->allotment_accomplishment += $allotmentAccomplishment;
                        $row->count += 1;
                    }
                }

                // Average percentages
                foreach ($classMap as $classKey => $row) {
                    if ($row->count > 0) {
                        $row->appropriation_accomplishment /= $row->count;
                        $row->allotment_accomplishment /= $row->count;
                    }
                }

                $categoryClassStats[$category] = $classMap
                    ->sortBy(fn($row, $classCode) => $allotmentClasses[$classCode]->id ?? PHP_INT_MAX);
            }

            $baseFund->setAttribute('categoryClassStats', $categoryClassStats);

            $matched = [];

            foreach ($groupedByCategory as $category => $oacs) {
                $sectorsMap = collect();

                foreach ($oacs as $oac) {
                    $oacClass = $allotmentClasses->get($oac->class);

                    foreach ($oac->appropriations as $appropriation) {
                        $sector = $sectors->first(
                            fn($s) =>
                            Str::startsWith($appropriation->fpp_code, $s->sector_code)
                        );

                        // If no sector is matched and the fund is "Special Education Fund", group by class only
                        if (!$sector) {
                            if ($type === 'Special Education Fund') {
                                $sectorCode = '';
                                $sector = (object) [
                                    'sector_code' => $sectorCode,
                                    'sector' => '',
                                    'present_allotment_classes' => collect(),
                                ];
                            } else {
                                continue; // skip if it's not SEF and sector not found
                            }
                        }

                        // Still skip if class is not found
                        if (!$oacClass) continue;

                        $sectorCode = $sector->sector_code;

                        if (!$sectorsMap->has($sectorCode)) {
                            $sectorClone = clone $sector;
                            $sectorClone->present_allotment_classes = collect();
                            $sectorsMap->put($sectorCode, $sectorClone);
                        }

                        $allotmentKey = $oacClass->class;

                        // Compute fields
                        $approved = $appropriation->appropriation;
                        // Supplemental & Reversion (filter by supplemental_date)
                        $supplemental = $appropriation->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $reversion = $appropriation->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        // Realignments (filter by realignment_date)
                        $realignment = $appropriation->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

                        // Authorized Appropriation
                        $authorized = $approved + $supplemental + $realignment + $reversion;

                        // Allotment (respect date-filtered adjustments too)
                        $allotment = ($appropriation->quarter1 + $appropriation->quarter2 + $appropriation->quarter3 + $appropriation->quarter4)
                            + $supplemental + $reversion + $realignment;

                        // For Later Release (unchanged, but still dynamic by quarter)
                        $forLaterRelease = $appropriation->for_later_release ?? (
                            ($currentQuarter < 2 ? ($appropriation->quarter2 ?? 0) : 0) +
                            ($currentQuarter < 3 ? ($appropriation->quarter3 ?? 0) : 0) +
                            ($currentQuarter < 4 ? ($appropriation->quarter4 ?? 0) : 0)
                        );

                        $allotment -= $forLaterRelease;

                        // Obligations (filter by obr_date)
                        $obligationBase = $appropriation->obligationAmounts
                            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        // Obligation Adjustments (filter by adjustment_date)
                        $obligationAdjustments = $appropriation->obligationAmounts
                            ->flatMap(
                                fn($oa) => $oa->obligation
                                    ? $oa->obligation->obligationAdjustments->where('adjustment_date', '<=', $asOfDate)
                                    : collect()
                            )
                            ->sum('adjustment_amount');

                        $obligation = $obligationBase + $obligationAdjustments;

                        $appropriationBalance = $authorized - $obligation;
                        $appropriationAccomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                        $allotmentBalance = $allotment - $obligation;
                        $allotmentAccomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

                        // Add or update
                        $existingClass = $sectorsMap[$sectorCode]->present_allotment_classes->get($allotmentKey);

                        if ($existingClass) {
                            $existingClass->approved_appropriation += $approved;
                            $existingClass->supplemental += $supplemental;
                            $existingClass->reversion += $reversion;
                            $existingClass->realignment += $realignment;
                            $existingClass->authorized_appropriation += $authorized;
                            $existingClass->allotment += $allotment;
                            $existingClass->for_later_release += $forLaterRelease;
                            $existingClass->obligations += $obligation;
                            $existingClass->appropriation_balance += $appropriationBalance;
                            $existingClass->appropriation_accomplishment = $existingClass->authorized_appropriation > 0
                                ? ($existingClass->obligations / $existingClass->authorized_appropriation) * 100 : 0;
                            $existingClass->allotment_balance += $allotmentBalance;
                            $existingClass->allotment_accomplishment = $existingClass->allotment > 0
                                ? ($existingClass->obligations / $existingClass->allotment) * 100 : 0;
                        } else {
                            $classClone = clone $oacClass;
                            $classClone->approved_appropriation = $approved;
                            $classClone->supplemental = $supplemental;
                            $classClone->reversion = $reversion;
                            $classClone->realignment = $realignment;
                            $classClone->authorized_appropriation = $authorized;
                            $classClone->allotment = $allotment;
                            $classClone->for_later_release = $forLaterRelease;
                            $classClone->obligations = $obligation;
                            $classClone->appropriation_balance = $appropriationBalance;
                            $classClone->appropriation_accomplishment = $appropriationAccomplishment;
                            $classClone->allotment_balance = $allotmentBalance;
                            $classClone->allotment_accomplishment = $allotmentAccomplishment;

                            $sectorsMap[$sectorCode]->present_allotment_classes->put($allotmentKey, $classClone);
                        }
                    }
                }

                $matched[$category] = $sectorsMap->sortBy('sector_code')->map(function ($sector) {
                    $sector->present_allotment_classes = $sector->present_allotment_classes
                        ->sortBy('id')
                        ->values();

                    // Compute totals per sector
                    $totals = [
                        'approved_appropriation' => 0,
                        'supplemental' => 0,
                        'reversion' => 0,
                        'realignment' => 0,
                        'authorized_appropriation' => 0,
                        'allotment' => 0,
                        'for_later_release' => 0,
                        'obligations' => 0,
                        'appropriation_balance' => 0,
                        'appropriation_accomplishment' => 0,
                        'allotment_balance' => 0,
                        'allotment_accomplishment' => 0,
                        'count' => 0,
                    ];

                    foreach ($sector->present_allotment_classes as $ac) {
                        $totals['approved_appropriation'] += $ac->approved_appropriation ?? 0;
                        $totals['supplemental'] += $ac->supplemental ?? 0;
                        $totals['reversion'] += $ac->reversion ?? 0;
                        $totals['realignment'] += $ac->realignment ?? 0;
                        $totals['authorized_appropriation'] += $ac->authorized_appropriation ?? 0;
                        $totals['allotment'] += $ac->allotment ?? 0;
                        $totals['for_later_release'] += $ac->for_later_release ?? 0;
                        $totals['obligations'] += $ac->obligations ?? 0;
                        $totals['appropriation_balance'] += $ac->appropriation_balance ?? 0;
                        $totals['appropriation_accomplishment'] += $ac->appropriation_accomplishment ?? 0;
                        $totals['allotment_balance'] += $ac->allotment_balance ?? 0;
                        $totals['allotment_accomplishment'] += $ac->allotment_accomplishment ?? 0;
                        $totals['count']++;
                    }

                    // Average accomplishment
                    $totals['appropriation_accomplishment'] = $totals['count'] > 0
                        ? $totals['appropriation_accomplishment'] / $totals['count']
                        : 0;

                    $totals['allotment_accomplishment'] = $totals['count'] > 0
                        ? $totals['allotment_accomplishment'] / $totals['count']
                        : 0;

                    $sector->totals = (object) $totals;
                    return $sector;
                })->values();
            }

            $baseFund->setAttribute('matchedSectorsByCategory', collect($matched));

            // Compute category-level totals
            $categoryTotals = collect();

            foreach ($matched as $category => $sectors) {
                $totals = [
                    'approved_appropriation' => 0,
                    'supplemental' => 0,
                    'reversion' => 0,
                    'realignment' => 0,
                    'authorized_appropriation' => 0,
                    'allotment' => 0,
                    'for_later_release' => 0,
                    'obligations' => 0,
                    'appropriation_balance' => 0,
                    'appropriation_accomplishment' => 0,
                    'allotment_balance' => 0,
                    'allotment_accomplishment' => 0,
                    'count' => 0,
                ];

                foreach ($sectors as $sector) {
                    foreach ($totals as $key => $val) {
                        if ($key !== 'count') $totals[$key] += $sector->totals->$key ?? 0;
                    }
                    $totals['count']++;
                }

                $totals['appropriation_accomplishment'] = $totals['count'] > 0
                    ? $totals['appropriation_accomplishment'] / $totals['count']
                    : 0;

                $totals['allotment_accomplishment'] = $totals['count'] > 0
                    ? $totals['allotment_accomplishment'] / $totals['count']
                    : 0;

                $categoryTotals[$category] = (object) $totals;
            }

            $baseFund->setAttribute('categoryTotals', $categoryTotals);

            // Compute grand total across all categories
            $grandTotal = [
                'approved_appropriation' => 0,
                'supplemental' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'for_later_release' => 0,
                'obligations' => 0,
                'appropriation_balance' => 0,
                'appropriation_accomplishment' => 0,
                'allotment_balance' => 0,
                'allotment_accomplishment' => 0,
                'count' => 0,
            ];

            foreach ($categoryTotals as $totals) {
                foreach ($grandTotal as $key => $val) {
                    if ($key !== 'count') $grandTotal[$key] += $totals->$key ?? 0;
                }
                $grandTotal['count']++;
            }

            $grandTotal['appropriation_accomplishment'] = $grandTotal['count'] > 0
                ? $grandTotal['appropriation_accomplishment'] / $grandTotal['count']
                : 0;

            $grandTotal['allotment_accomplishment'] = $grandTotal['count'] > 0
                ? $grandTotal['allotment_accomplishment'] / $grandTotal['count']
                : 0;

            $baseFund->setAttribute('grandTotal', (object) $grandTotal);

            return $baseFund;
        })->values();


        return view('exports.saaobFundSector', [
            'groupedFunds' => $groupedFunds,
            'funds' => $funds,
            'selectedYear' => $selectedYear,
            'selectedFund' => $selectedFund,
            'asOfDate' => $asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
        ]);
    }
}
