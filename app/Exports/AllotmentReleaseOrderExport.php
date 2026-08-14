<?php

namespace App\Exports;

use App\Models\AllotmentReleaseOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllotmentReleaseOrderExport implements FromView, WithEvents, WithStyles
{
    /**
     * Hidden column H marker written by resources/views/exports/aro.blade.php
     * on the first row of every printed page (after the first) — read back
     * here to insert a real page break at the exact same spot the Preview/Print
     * view breaks the page, so the Excel print output matches it exactly.
     */
    private const PAGE_BREAK_MARKER = 'ARO_PAGE_BREAK';

    /**
     * Column A-G widths as percentages of the printable content width, matching
     * the <colgroup> in allotment_release_orders/preview.blade.php exactly
     * (PPA Code, PPA Description, Object Class/Account Code, then the four
     * peso-amount columns).
     */
    private const COLUMN_WIDTH_PERCENTS = [11, 34, 10, 11.25, 11.25, 11.25, 11.25];

    private const MARGIN_IN = 0.4;

    protected AllotmentReleaseOrder $aro;

    protected string $paperSize;

    public function __construct(AllotmentReleaseOrder $aro, string $paperSize = 'long')
    {
        $this->aro = $aro;
        $this->paperSize = $paperSize === 'legal' ? 'legal' : 'long';
    }

    public function view(): View
    {
        return view('exports.aro', ['aro' => $this->aro]);
    }

    public function styles(Worksheet $sheet)
    {
        // No blanket A1:G1 title override here — with per-page pagination, row 1 is
        // just the first page's hidden break-marker row, not a fixed title row; the
        // Allotment Release Order title's bold/uppercase/centered styling is applied
        // inline per page in exports/aro.blade.php instead.
        return [
            'A1:H'.$sheet->getHighestRow() => [
                'font' => [
                    'name' => 'Roboto',
                    'size' => 10,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $pageWidthIn = $this->paperSize === 'legal' ? 14 : 13;
                $contentWidthIn = $pageWidthIn - (self::MARGIN_IN * 2);

                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $pageSetup->setPaperSize($this->paperSize === 'legal' ? PageSetup::PAPERSIZE_LEGAL : PageSetup::PAPERSIZE_FOLIO);
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);
                $sheet->getPageMargins()
                    ->setTop(self::MARGIN_IN)->setBottom(self::MARGIN_IN)
                    ->setLeft(self::MARGIN_IN)->setRight(self::MARGIN_IN);

                // Column width units aren't inches (they're character-widths of the
                // default font), so this is an approximation (~13.7 units/inch for
                // Calibri 11) — Fit-to-Width above then scales the whole sheet to
                // exactly one page wide regardless, which is what actually guarantees
                // these columns fill the page in the same 11/34/10/11.25×4 proportions
                // the Preview/Print view uses, on either paper size.
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                foreach (self::COLUMN_WIDTH_PERCENTS as $index => $percent) {
                    $widthIn = $contentWidthIn * ($percent / 100);
                    $sheet->getColumnDimension($columns[$index])->setWidth(round($widthIn * 13.7, 1));
                }

                $highestRow = $sheet->getHighestRow();
                for ($row = 1; $row <= $highestRow; $row++) {
                    $marker = $sheet->getCell("H{$row}")->getValue();

                    if ($marker === self::PAGE_BREAK_MARKER) {
                        $sheet->setBreak("A{$row}", Worksheet::BREAK_ROW);
                    }
                }

                // The marker column is never meant to be seen — hide it rather than
                // rely on the view's inline display:none surviving HTML-to-Excel conversion.
                $sheet->getColumnDimension('H')->setVisible(false);
            },
        ];
    }
}
