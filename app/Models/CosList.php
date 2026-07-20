<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class CosList extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'cos_lists';

    protected $fillable = [
        'office_allotment_class_id',
        'appropriation_id',
        'employee_id',
        'employee_name',
        'position_title',
        'salary_grade',
        'from_date',
        'to_date',
        'monthly_rate',
        'annual_rate',
        'remarks',
        'basis',
    ];

    protected $casts = [
        'monthly_rate' => 'decimal:2',
        'annual_rate' => 'decimal:2',
    ];

    // Add custom attributes to be included in activity log
    protected $logAttributes = [
        'office_allotment_class_id',
        'appropriation_id',
        'employee_id',
        'employee_name',
        'position_title',
        'salary_grade',
        'from_date',
        'to_date',
        'monthly_rate',
        'annual_rate',
        'remarks',
        'basis',
    ];

    /**
     * Get the office allotment class associated with this COS entry
     */
    public function officeAllotmentClass()
    {
        return $this->belongsTo(OfficeAllotmentClass::class, 'office_allotment_class_id');
    }

    /**
     * Get the appropriation associated with this COS entry
     */
    public function appropriation()
    {
        return $this->belongsTo(Appropriation::class, 'appropriation_id');
    }

    /**
     * Mutator to calculate total contract amount when monthly rate or dates are set
     */
    public function setMonthlyRateAttribute($value)
    {
        $this->attributes['monthly_rate'] = $value;
        $this->calculateAnnualRate();
    }

    /**
     * Mutator to calculate when from_date is set
     */
    public function setFromDateAttribute($value)
    {
        $this->attributes['from_date'] = $value;
        $this->calculateAnnualRate();
    }

    /**
     * Mutator to calculate when to_date is set
     */
    public function setToDateAttribute($value)
    {
        $this->attributes['to_date'] = $value;
        $this->calculateAnnualRate();
    }

    /**
     * Calculate total contract amount based on monthly rate and date range
     */
    protected function calculateAnnualRate()
    {
        $monthlyRate = $this->attributes['monthly_rate'] ?? 0;
        $fromDate = $this->attributes['from_date'] ?? null;
        $toDate = $this->attributes['to_date'] ?? null;

        if ($fromDate && $toDate && $monthlyRate > 0) {
            $from = \Carbon\Carbon::parse($fromDate)->startOfDay();
            $to = \Carbon\Carbon::parse($toDate)->startOfDay();

            if ($to->lt($from)) {
                $this->attributes['annual_rate'] = 0;
                return;
            }

            $dailyRate = $monthlyRate / 22;
            $total = 0;

            $cursor = $from->copy()->startOfMonth();

            while ($cursor->lte($to)) {
                $monthStart = $cursor->copy()->startOfMonth();
                $monthEnd = $cursor->copy()->endOfMonth()->startOfDay();

                $periodStart = $from->greaterThan($monthStart) ? $from->copy() : $monthStart->copy();
                $periodEnd = $to->lessThan($monthEnd) ? $to->copy() : $monthEnd->copy();

                $isFullMonth = $periodStart->isSameDay($monthStart) && $periodEnd->isSameDay($monthEnd);

                if ($isFullMonth) {
                    $total += $monthlyRate;
                } else {
                    $workingDays = 0;
                    $period = \Carbon\CarbonPeriod::create($periodStart, $periodEnd);
                    foreach ($period as $date) {
                        if (!$date->isWeekend()) {
                            $workingDays++;
                        }
                    }
                    $total += $dailyRate * $workingDays;
                }

                $cursor->addMonth();
            }

            $this->attributes['annual_rate'] = $total;
        } else {
            $this->attributes['annual_rate'] = 0;
        }
    }

    /**
     * Get formatted date range for display
     */
    public function getDateRangeAttribute()
    {
        if ($this->from_date && $this->to_date) {
            return \Carbon\Carbon::parse($this->from_date)->format('M d, Y') . ' - ' . 
                   \Carbon\Carbon::parse($this->to_date)->format('M d, Y');
        }
        return '-';
    }
}
