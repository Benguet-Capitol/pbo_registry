<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CosListExport implements FromView, WithColumnWidths, WithEvents
{
    protected array $layout = [];

    public function __construct(
        protected $sections, // Collection of ['account_label','cos_list','total_annual_rate','total_appropriation']
        protected string $officeName,
        protected string $allotmentClassName
    ) {
        $this->layout = $this->buildLayout();
    }

    /**
     * Computes the exact Excel row for every dynamic element (account label,
     * header, data rows, totals) across all sections ONCE — the Blade view
     * renders rows in this same fixed order/count, and AfterSheet reads these
     * same numbers for formulas. One source of truth instead of two row
     * counters that could silently drift apart.
     */
    protected function buildLayout(): array
    {
        $row = 4; // 1 office name, 2 blank, 3 allotment class, 4 blank
        $layout = [];

        foreach ($this->sections as $i => $section) {
            $accountLabelRow = $row++;
            $headerRow = $row++;
            $hasData = $section['cos_list']->count() > 0;
            $firstDataRow = $lastDataRow = null;

            if ($hasData) {
                $firstDataRow = $row;
                $lastDataRow = $row + $section['cos_list']->count() - 1;
                $row = $lastDataRow + 1;
            } else {
                $row++; // the "no data" message row
            }

            $totalRow = $row++;
            $row++; // blank
            $appropriationRow = $row++;
            $lessPaymentRow = $row++;
            $balanceRow = $row++;
            $row++; // spacer before next section

            $layout[$i] = compact('hasData', 'firstDataRow', 'lastDataRow', 'totalRow', 'appropriationRow', 'lessPaymentRow', 'balanceRow');
        }

        return $layout;
    }

    public function view(): View
    {
        return view('cos_lists.export', [
            'sections' => $this->sections,
            'officeName' => $this->officeName,
            'allotmentClassName' => $this->allotmentClassName,
        ]);
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 30, 'C' => 30, 'D' => 20, 'E' => 36, 'F' => 40, 'G' => 40, 'H' => 20, 'I' => 20];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Tahoma')->setSize(10);
                $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('Tahoma');

                $numberFormat = '#,##0.00';
                $col = 'I';

                foreach ($this->layout as $meta) {
                    if ($meta['hasData']) {
                        $sheet->getStyle("H{$meta['firstDataRow']}:I{$meta['lastDataRow']}")
                            ->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->setCellValue("{$col}{$meta['totalRow']}",
                            "=SUM({$col}{$meta['firstDataRow']}:{$col}{$meta['lastDataRow']})");
                    } else {
                        $sheet->setCellValue("{$col}{$meta['totalRow']}", 0);
                    }

                    $sheet->setCellValue("{$col}{$meta['lessPaymentRow']}", "={$col}{$meta['totalRow']}");
                    $sheet->setCellValue("{$col}{$meta['balanceRow']}",
                        "={$col}{$meta['appropriationRow']}-{$col}{$meta['lessPaymentRow']}");

                    foreach (['totalRow', 'appropriationRow', 'lessPaymentRow', 'balanceRow'] as $key) {
                        $sheet->getStyle("{$col}{$meta[$key]}")->getNumberFormat()->setFormatCode($numberFormat);
                    }
                }
            },
        ];
    }
}