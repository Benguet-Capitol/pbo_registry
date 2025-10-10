<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Appropriation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'office_allotment_class_id',
        'program_no',
        'programs',
        'project_no',
        'account_code',
        'id2',
        'description',
        'fpp_code',
        'project_location',
        'cco_year',
        'appropriation',
        'quarter1',
        'quarter2',
        'quarter3',
        'quarter4',
        'buffer_fund',
        'remarks',
        'col',
    ];

    /**
     * Get the office allotment class that owns the appropriation.
     */
    public function officeAllotmentClass()
    {
        return $this->belongsTo(OfficeAllotmentClass::class, 'office_allotment_class_id');
    }

    /**
     * Get the obligation amounts for the appropriation.
     */
    public function obligationAmounts()
    {
        return $this->hasMany(ObligationAmount::class, 'appropriation_id');
    }

    public function realignments()
    {
        return $this->hasMany(Realignment::class, 'appropriations_id');
    }

    public function supplementals()
    {
        return $this->hasMany(Supplemental::class, 'appropriations_id');
    }

    public function purchaseOrders()
    {
        return $this->hasManyThrough(PurchaseOrder::class, ObligationAmount::class, 'appropriation_id', 'obligation_amounts_id');
    }

    public function obligationAdjustments()
    {
        return $this->hasManyThrough(ObligationAdjustment::class, ObligationAmount::class, 'appropriation_id', 'obligation_amounts_id');
    }

    public function disbursements()
    {
        return $this->hasManyThrough(Disbursement::class, ObligationAmount::class, 'appropriation_id', 'obligation_amounts_id');
    }


}
