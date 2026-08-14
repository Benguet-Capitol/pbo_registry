<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllotmentReleaseOrder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'aro_no',
        'date_of_issue',
        'year',
        'office_allotment_classes_id',
        'fund_source',
        'supplemental_no',
        'ppa_code',
        'provincial_budget_officer_id',
        'provincial_budget_officer_title',
        'provincial_governor_id',
        'provincial_governor_name',
        'provincial_governor_title',
        'created_by',
    ];

    protected $casts = [
        'date_of_issue' => 'date',
    ];

    /**
     * CO is displayed as "Capital Expenditures" everywhere on the ARO report
     * (title line, print/export headers, card badges) instead of the
     * allotment_classes.description value ("Capital Outlay"); PS/MOOE keep
     * their existing description/label unchanged. Falls back to the raw
     * class code when no description is available (e.g. filter chips).
     */
    public static function displayClassLabel(?string $classCode, ?string $description = null): string
    {
        return $classCode === 'CO' ? 'Capital Expenditures' : (string) ($description ?? $classCode);
    }

    public function officeAllotmentClass()
    {
        return $this->belongsTo(OfficeAllotmentClass::class, 'office_allotment_classes_id');
    }

    public function items()
    {
        return $this->hasMany(AllotmentReleaseOrderItem::class);
    }

    public function provincialBudgetOfficer()
    {
        return $this->belongsTo(Employee::class, 'provincial_budget_officer_id');
    }

    public function provincialGovernor()
    {
        return $this->belongsTo(Employee::class, 'provincial_governor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalThisReleaseAttribute()
    {
        return $this->items->sum('this_release');
    }

    /**
     * Groups this ARO's items by their (per-row, editable) PPA Code, in the
     * order each code first appears among the items — matching the order the
     * account codes were checked in when the ARO was built. Each group
     * carries a Subtotal across its rows, used by both the Preview page and
     * the Excel export to reproduce the LBE Form No. 1 layout, where a
     * single ARO can have multiple PPA Codes, each rolling up its own set of
     * account codes.
     *
     * @return array<int, array{ppa_code: string, rows: \Illuminate\Support\Collection, subtotal: array<string, float>}>
     */
    public function groupedItems(): array
    {
        $groups = [];

        foreach ($this->items as $item) {
            $ppaCode = $item->ppa_code ?: '';

            if (! isset($groups[$ppaCode])) {
                $groups[$ppaCode] = [
                    'ppa_code' => $ppaCode,
                    'rows' => collect(),
                    'subtotal' => [
                        'authorized_appropriation' => 0.0,
                        'for_later_release' => 0.0,
                        'previously_released_amount' => 0.0,
                        'this_release' => 0.0,
                    ],
                ];
            }

            $groups[$ppaCode]['rows']->push($item);
            $groups[$ppaCode]['subtotal']['authorized_appropriation'] += (float) $item->authorized_appropriation;
            $groups[$ppaCode]['subtotal']['for_later_release'] += (float) $item->for_later_release;
            $groups[$ppaCode]['subtotal']['previously_released_amount'] += (float) $item->previously_released_amount;
            $groups[$ppaCode]['subtotal']['this_release'] += (float) $item->this_release;
        }

        return array_values($groups);
    }

    /**
     * Row-level pagination for the printable Preview: flattens groupedItems()
     * into "print units" (a program header, an item row, or a group's
     * Subtotal row) and slices that flat sequence into pages of at most
     * $lineBudget estimated print lines — unlike groupedItems() alone, this
     * DOES split a single oversized PPA Code group across multiple pages
     * (e.g. 192 rows all sharing one PPA Code), which is exactly the case a
     * whole-group pagination scheme can't handle.
     *
     * Budgeting by estimated wrapped-line count (not a flat row count) matters
     * because the PPA Description column has a fixed width in the printed
     * layout: a long description wraps to 2-3 lines and takes proportionally
     * more vertical space than a short one. Treating every row as "1 unit"
     * regardless of wrapping under-budgets pages full of long descriptions,
     * which overflows the physical page and forces the browser to insert its
     * own unplanned page break mid-table — losing the repeating header/footer
     * on that spillover page. $lineBudget deliberately undershoots the naive
     * "rows that fit" estimate: every page also gets one more physical row
     * appended by the caller after this budget (a PAGE SUBTOTAL/TOTAL row)
     * that this method has no visibility into, and each row's real printed
     * height (borders + cell padding + line-height) runs closer to ~0.33in
     * than a bare line-height figure would suggest.
     *
     * @return array<int, array<int, array<string, mixed>>> pages of units
     */
    public function paginatedPrintUnits(int $lineBudget = 13): array
    {
        // Rough characters-per-line for a text-xs cell at the printed column's fixed
        // width (see preview.blade.php's <colgroup>) — used only to estimate how many
        // lines a cell's text will wrap to, not to actually lay out the print.
        $estimateLines = function (?string $text, int $charsPerLine): int {
            $text = trim((string) $text);

            return $text === '' ? 1 : max(1, (int) ceil(mb_strlen($text) / $charsPerLine));
        };

        $units = [];

        foreach ($this->groupedItems() as $group) {
            $lastProgram = null;

            foreach ($group['rows'] as $item) {
                if ($item->programs && $item->programs !== $lastProgram) {
                    $units[] = ['type' => 'program', 'text' => $item->programs, 'lines' => $estimateLines($item->programs, 120)];
                    $lastProgram = $item->programs;
                }

                $units[] = [
                    'type' => 'item',
                    'item' => $item,
                    'ppa_code' => $group['ppa_code'],
                    'lines' => $estimateLines($item->ppa_description, 55), // PPA Description column, ~3.4in wide
                ];
            }

            $units[] = ['type' => 'subtotal', 'ppa_code' => $group['ppa_code'], 'subtotal' => $group['subtotal'], 'lines' => 1];
        }

        $pages = [];
        $current = [];
        $currentLines = 0;
        foreach ($units as $unit) {
            if ($currentLines > 0 && $currentLines + $unit['lines'] > $lineBudget) {
                $pages[] = $current;
                $current = [];
                $currentLines = 0;
            }
            $current[] = $unit;
            $currentLines += $unit['lines'];
        }
        if (! empty($current) || empty($pages)) {
            $pages[] = $current;
        }

        foreach ($pages as &$pageUnits) {
            $count = count($pageUnits);
            for ($i = 0; $i < $count; $i++) {
                if ($pageUnits[$i]['type'] !== 'item') {
                    continue;
                }

                $prev = $i > 0 ? $pageUnits[$i - 1] : null;
                $isRunStart = ! $prev || $prev['type'] !== 'item' || $prev['ppa_code'] !== $pageUnits[$i]['ppa_code'];

                if (! $isRunStart) {
                    $pageUnits[$i]['rowspan'] = 0;

                    continue;
                }

                $span = 1;
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($pageUnits[$j]['type'] === 'item' && $pageUnits[$j]['ppa_code'] === $pageUnits[$i]['ppa_code']) {
                        $span++;
                    } else {
                        break;
                    }
                }
                $pageUnits[$i]['rowspan'] = $span;
            }
        }
        unset($pageUnits);

        return $pages;
    }
}
