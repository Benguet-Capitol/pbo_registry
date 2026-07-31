<?php

namespace App\Imports;

use App\Models\CosList;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CosListImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $importedCount = 0;

    public function __construct(
        private int $officeAllotmentClassId,
        private int $appropriationId
    ) {}

    /**
     * WithHeadingRow slugs header text into array keys. Since the template
     * now has an optional 2-row "Office:/Account:" info block above the real
     * header, Laravel Excel would treat whichever row IS the header as row 1
     * for slugging purposes — but if a user re-saves the file with those
     * info rows still present, WithHeadingRow reads the WRONG row as headers.
     * See note below this class about that risk.
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;

            $employeeIdNumber = trim((string) ($row['employee_id_number'] ?? ''));
            $employeeName = trim((string) ($row['employee_name'] ?? ''));
            $positionTitle = trim((string) ($row['position_title'] ?? ''));
            $salaryGrade = trim((string) ($row['salary_grade'] ?? ''));
            $fromDateRaw = trim((string) ($row['from_date'] ?? ''));
            $toDateRaw = trim((string) ($row['to_date'] ?? ''));
            $monthlyRate = $row['monthly_rate'] ?? null;
            $basis = trim((string) ($row['basis'] ?? ''));
            $remarks = trim((string) ($row['remarks'] ?? ''));

            if ($employeeName === '' && $fromDateRaw === '' && $monthlyRate === null) {
                continue; // blank trailing row
            }

            if ($employeeName === '') {
                $this->errors[] = "Row {$excelRow}: Employee Name is required (use 'Vacant' if unfilled).";
                continue;
            }
            if ($positionTitle === '') {
                $this->errors[] = "Row {$excelRow}: Position Title is required.";
                continue;
            }
            if ($fromDateRaw === '' || $toDateRaw === '') {
                $this->errors[] = "Row {$excelRow}: From Date and To Date are required.";
                continue;
            }
            if (!is_numeric($monthlyRate) || (float) $monthlyRate <= 0) {
                $this->errors[] = "Row {$excelRow}: Monthly Rate must be a number greater than 0.";
                continue;
            }

            try {
                $from = \Carbon\Carbon::parse($fromDateRaw)->startOfDay();
                $to = \Carbon\Carbon::parse($toDateRaw)->startOfDay();
            } catch (\Exception $e) {
                $this->errors[] = "Row {$excelRow}: From Date / To Date could not be read.";
                continue;
            }

            if ($to->lt($from)) {
                $this->errors[] = "Row {$excelRow}: To Date must be on or after From Date.";
                continue;
            }

            if (strcasecmp($employeeName, 'Vacant') === 0) {
                $employeeId = 'VACANT';
            } elseif ($employeeIdNumber !== '') {
                $employeeId = $employeeIdNumber;
            } else {
                $employeeId = 'MANUAL-' . now()->timestamp . '-' . $excelRow;
            }

            try {
                CosList::create([
                    'office_allotment_class_id' => $this->officeAllotmentClassId,
                    'appropriation_id' => $this->appropriationId,
                    'employee_id' => $employeeId,
                    'employee_name' => $employeeName,
                    'position_title' => $positionTitle,
                    'salary_grade' => $salaryGrade ?: null,
                    'from_date' => $from->toDateString(),
                    'to_date' => $to->toDateString(),
                    'monthly_rate' => (float) $monthlyRate,
                    'basis' => $basis ?: null,
                    'remarks' => $remarks ?: null,
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$excelRow}: Failed to save — " . $e->getMessage();
            }
        }
    }
}