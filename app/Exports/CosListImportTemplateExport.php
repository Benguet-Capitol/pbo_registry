<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CosListImportTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithEvents
{
    public function headings(): array
    {
        return [
            'Employee ID Number', 'Employee Name', 'Position Title', 'Salary Grade',
            'From Date', 'To Date', 'Monthly Rate', 'Basis', 'Remarks',
        ];
    }

    public function array(): array
    {
        return [
            ['', 'Juan Dela Cruz', 'Administrative Aide II', '2', '2026-01-01', '2026-12-31', 17246, 'SP RES NO. 2026-0414 dated January 19, 2026', ''],
            ['VACANT', 'Vacant', 'Laboratory Aide I', '2', '2026-01-01', '2026-12-31', 17246, '', ''],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 24, 'C' => 24, 'D' => 14, 'E' => 14, 'F' => 14, 'G' => 14, 'H' => 36, 'I' => 24];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                $sheet->getStyle('A1:I1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');

                $sheet->getStyle('E2:F1000')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            },
        ];
    }
}