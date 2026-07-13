<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use App\Models\OfficeAllotmentClass;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class AccountsSummaryExport implements FromView, WithStyles, WithEvents
{
    protected $selectedYear;
    protected $asOfDate;
    protected $signatoryName;
    protected $signatoryDesignation;
    protected $selectedFund;

    public function __construct($selectedYear, $asOfDate, $signatoryName, $signatoryDesignation, $selectedFund = 'all')
    {
        $this->selectedYear = $selectedYear;
        $this->asOfDate = $asOfDate;
        $this->signatoryName = $signatoryName;
        $this->signatoryDesignation = $signatoryDesignation;
        $this->selectedFund = $selectedFund;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Freeze rows above row 12
                $sheet->freezePane('A12');

                // Set rows 6 to 9 to repeat on printed pages
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 11);

                // Hide specific columns (C, D, E, F)
                foreach (['C', 'D', 'E', 'F'] as $col) {
                    $sheet->getColumnDimension($col)->setVisible(false);
                }

                // Identify rows for calculations
                $contentRows = [];
                $totalRows = [];
                $grandTotalRows = [];

                for ($row = 13; $row <= $highestRow; $row++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
                    
                    if (str_contains($cellValue, 'GRAND TOTAL')) {
                        $grandTotalRows[] = $row;
                    } elseif (str_starts_with($cellValue, 'TOTAL')) {
                        $totalRows[] = $row;
                    } elseif (!empty($cellValue) && !str_contains($cellValue, 'CERTIFIED')) {
                        $contentRows[] = $row;
                    }
                }

                // Format number columns (C to N)
                foreach (range('C', 'N') as $column) {
                    $sheet->getStyle("{$column}11:{$column}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                }

                // Format percentage columns (L and N)
                foreach (['L', 'N'] as $column) {
                    $sheet->getStyle("{$column}11:{$column}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('0.00%');
                }

                // Utility function for percentage formulas
                $applyPercentageFormulas = function($sheet, $row) {
                    $sheet->setCellValue("L{$row}", "=IF(G{$row}>0,J{$row}/G{$row},0)");
                    $sheet->setCellValue("N{$row}", "=IF(H{$row}>0,J{$row}/H{$row},0)");
                };

                // Apply formulas to content rows
                foreach ($contentRows as $row) {
                    $sheet->setCellValue("G{$row}", "=C{$row}+D{$row}+E{$row}+F{$row}");
                    $sheet->setCellValue("H{$row}", "=G{$row}-I{$row}");
                    $sheet->setCellValue("K{$row}", "=G{$row}-J{$row}");
                    $sheet->setCellValue("M{$row}", "=H{$row}-J{$row}");
                    $applyPercentageFormulas($sheet, $row);
                }

               // Apply formulas to total rows - sum all content rows since last total or grand total
                foreach ($totalRows as $totalRow) {
                    // Find the previous total or grand total row, or start from content beginning
                    $startRow = 11; // Default to first content row
                    
                    // Look backwards to find the last total/grand total row before this one
                    for ($i = $totalRow - 1; $i >= 10; $i--) {
                        if (in_array($i, $totalRows) || in_array($i, $grandTotalRows)) {
                            $startRow = $i + 2; // Start 2 rows after the previous total (skip any blank rows)
                            break;
                        }
                    }

                    foreach (range('C', 'N') as $col) {
                        $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}{$startRow}:{$col}" . ($totalRow - 1) . ")");
                    }
                    $applyPercentageFormulas($sheet, $totalRow);
                }

                // Apply formulas to grand total rows
                foreach ($grandTotalRows as $row) {
                    $totalsAbove = [];
                    for ($i = $row - 1; $i >= 10; $i--) {
                        if (in_array($i, $totalRows)) {
                            $totalsAbove[] = $i;
                        } elseif (in_array($i, $grandTotalRows)) {
                            break;
                        }
                    }

                    if (!empty($totalsAbove)) {
                        foreach (range('C', 'N') as $col) {
                            $refs = implode(',', array_map(fn($r) => "{$col}{$r}", array_reverse($totalsAbove)));
                            $sheet->setCellValue("{$col}{$row}", "=SUM({$refs})");
                        }
                    }
                    $applyPercentageFormulas($sheet, $row);
                }

                // Format bold rows
                foreach ($totalRows as $row) {
                    $sheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);
                }
                foreach ($grandTotalRows as $row) {
                    $sheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1:N10000' => [
                'font' => [
                    'name' => 'Arial Narrow',
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
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function view(): View
    {
        $selectedYear = $this->selectedYear;
        $asOfDate = $this->asOfDate;
        $selectedFund = $this->selectedFund;
        
        // Determine display fund text
        $fundLabels = [
            'all' => 'All Funds',
            'General Fund' => 'General Fund',
            'Benguet General Hospital Economic Enterprise' => 'Benguet General Hospital Economic Enterprise',
            'Special Education Fund' => 'Special Education Fund'
        ];
        $displayFund = $fundLabels[$selectedFund] ?? $selectedFund;

        // Get current quarter based on as_of_date
        $currentMonth = Carbon::parse($asOfDate)->month;
        $currentQuarter = ceil($currentMonth / 3);

        // Fetch all account codes for description lookup
        $accountCodes = \App\Models\AccountCode::all()->keyBy('code');

        // Fetch allotment classes with their appropriations
        $query = OfficeAllotmentClass::with([
            'allotmentClass',
            'fundSourceRelation',
            'appropriations' => function($q) use ($asOfDate) {
                $q->with([
                    'supplementals',
                    'realignments',
                    'obligationAmounts' => function($q2) use ($asOfDate) {
                        $q2->with(['obligation' => function($q3) use ($asOfDate) {
                            $q3->with('obligationAdjustments');
                        }]);
                    }
                ]);
            }
        ])
            ->where('year', $selectedYear);
        
        // Apply allotment class category filter for 'Current'
        $query->whereHas('allotmentClass', function($q) {
            $q->where('category', 'Current');
        });
        
        // Apply fund_source filter for 'Current'
        $query->whereHas('fundSourceRelation', function($q) {
            $q->where('category', 'Current');
        });
        
        // Apply fund filter
        if ($selectedFund !== 'all') {
            if ($selectedFund === 'General Fund') {
                $query->whereIn('fund', ['General Fund', 'Provincial Development Fund']);
            } else {
                $query->where('fund', $selectedFund);
            }
        }
        
        $allotmentClasses = $query->orderBy('id')
            ->get()
            ->groupBy(function($item) {
                return $item->allotmentClass->description;
            });

        // Build grouped structure: class => [accounts with their appropriations]
        $allotmentClassTotals = $allotmentClasses->map(function($classes) use ($asOfDate, $currentQuarter, $accountCodes) {
            $classAccounts = [];
            $classSubtotals = [
                'appropriation' => 0,
                'sb_appropriation' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'for_later_release' => 0,
                'obligation' => 0,
                'appropriation_balance' => 0,
                'allotment_balance' => 0,
                'utilization_percent' => 0,
                'allotment_utilization_percent' => 0,
            ];

            // Get all appropriations for this allotment class group
            $classAppropriations = $classes->flatMap(fn($oac) => $oac->appropriations);

            // Group appropriations by base account code (without extension)
            $groupedByAccountCode = $classAppropriations->groupBy(function($app) {
                // Remove extension (everything after space)
                return trim(explode(' ', trim($app->account_code))[0]);
            });

            // Sort by account code
            $groupedByAccountCode = $groupedByAccountCode->sortKeys();

            // Iterate through grouped appropriations
            foreach ($groupedByAccountCode as $baseAccountCode => $appsByCode) {
                $matchingAppropriations = $appsByCode;
                
                if ($matchingAppropriations->isEmpty()) {
                    continue;
                }

                $appropriation = $matchingAppropriations->sum('appropriation');

                $sb = $matchingAppropriations->flatMap(fn($app) => 
                    $app->supplementals
                        ->where('type', 'Supplemental')
                        ->where('supplemental_date', '<=', $asOfDate)
                )->sum('amount');

                $rev = $matchingAppropriations->flatMap(fn($app) => 
                    $app->supplementals
                        ->where('type', 'Reversion')
                        ->where('supplemental_date', '<=', $asOfDate)
                )->sum('amount') * -1;

                $realignment = $matchingAppropriations->flatMap(fn($app) => 
                    $app->realignments->where('realignment_date', '<=', $asOfDate)
                )->reduce(fn ($carry, $r) =>
                    $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                $obligationBase = $matchingAppropriations->flatMap(fn($app) => 
                    $app->obligationAmounts
                        ->filter(fn ($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                )->sum('obr_amount');

                $obligationAdjustments = $matchingAppropriations->flatMap(fn($app) =>
                    $app->obligationAmounts->flatMap(fn($oa) =>
                        $oa->obligation
                            ? $oa->obligation->obligationAdjustments
                                ->where('adjustment_date', '<=', $asOfDate)
                                ->where('obligation_amounts_id', $oa->id)
                            : collect()
                    )
                )->sum('adjustment_amount');

                $obligation = $obligationBase + $obligationAdjustments;

                $authorized = $appropriation + $sb + $rev + $realignment;

                // --- Allotment & For Later Release ---
                $allotment = $matchingAppropriations->reduce(function($carry, $app) {
                    return $carry + ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4);
                }, 0) + $sb + $rev + $realignment;

                $forLater = 0;
                if ($currentQuarter < 2) {
                    $forLater += $matchingAppropriations->sum('quarter2');
                }
                if ($currentQuarter < 3) {
                    $forLater += $matchingAppropriations->sum('quarter3');
                }
                if ($currentQuarter < 4) {
                    $forLater += $matchingAppropriations->sum('quarter4');
                }

                // Supplemental amounts not yet released, checked against each supplemental's
                // own quarter columns — same "not released yet" logic as appropriations
                $sbForLater = $matchingAppropriations->flatMap(fn($app) =>
                    $app->supplementals
                        ->where('type', 'Supplemental')
                        ->where('supplemental_date', '<=', $asOfDate)
                )->sum(function ($supp) use ($currentQuarter) {
                    $fl = 0;
                    if ($currentQuarter < 2) $fl += $supp->quarter2 ?? 0;
                    if ($currentQuarter < 3) $fl += $supp->quarter3 ?? 0;
                    if ($currentQuarter < 4) $fl += $supp->quarter4 ?? 0;
                    return $fl;
                });

                $forLater += $sbForLater;

                $allotment -= $forLater;

                $appropriation_balance = $authorized - $obligation;
                $appropriation_accomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                $allotment_balance = $allotment - $obligation;
                $allotment_accomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

                $firstApp = $matchingAppropriations->first();
                
                // Get description from AccountCode model
                $accountCodeDescription = $accountCodes->get($baseAccountCode)?->description ?? $firstApp->description ?? '';
                
                $classAccounts[] = [
                    'id' => $firstApp->id ?? null,
                    'description' => $accountCodeDescription,
                    'account_code' => $baseAccountCode,
                    'fpp' => $firstApp->fpp ?? null,
                    'appropriation' => $appropriation,
                    'sb_appropriation' => $sb,
                    'reversion' => $rev,
                    'realignment' => $realignment,
                    'authorized_appropriation' => $authorized,
                    'allotment' => $allotment,
                    'for_later_release' => $forLater,
                    'obligation' => $obligation,
                    'appropriation_balance' => $appropriation_balance,
                    'appropriation_accomplishment' => $appropriation_accomplishment,
                    'allotment_balance' => $allotment_balance,
                    'allotment_accomplishment' => $allotment_accomplishment,
                ];

                $classSubtotals['appropriation'] += $appropriation;
                $classSubtotals['sb_appropriation'] += $sb;
                $classSubtotals['reversion'] += $rev;
                $classSubtotals['realignment'] += $realignment;
                $classSubtotals['authorized_appropriation'] += $authorized;
                $classSubtotals['allotment'] += $allotment;
                $classSubtotals['for_later_release'] += $forLater;
                $classSubtotals['obligation'] += $obligation;
                $classSubtotals['appropriation_balance'] += $appropriation_balance;
                $classSubtotals['allotment_balance'] += $allotment_balance;
            }

            $classSubtotals['utilization_percent'] = $classSubtotals['authorized_appropriation'] > 0 
                ? ($classSubtotals['obligation'] / $classSubtotals['authorized_appropriation']) * 100 
                : 0;
            
            $classSubtotals['allotment_utilization_percent'] = $classSubtotals['allotment'] > 0 
                ? ($classSubtotals['obligation'] / $classSubtotals['allotment']) * 100 
                : 0;

            return [
                'accounts' => $classAccounts,
                'subtotals' => $classSubtotals,
            ];
        });

        return view('exports.summaryAccounts', [
            'selectedYear' => $selectedYear,
            'asOfDate' => $asOfDate,
            'signatoryName' => $this->signatoryName,
            'signatoryDesignation' => $this->signatoryDesignation,
            'allotmentClassTotals' => $allotmentClassTotals,
            'displayFund' => $displayFund,
        ]);
    }
}