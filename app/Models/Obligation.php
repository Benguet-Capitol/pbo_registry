<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Obligation extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_allotment_class_id',
        'appropriation_id',
        'obr_no',
        'obr_type',
        'obr_date',
        'particulars',
        'obr_amount',
        'remarks',
        'processed_by',
    ];

    public function officeAllotmentClass()
    {
        return $this->belongsTo(OfficeAllotmentClass::class, 'office_allotment_class_id');
    }

    public function obligationAmounts()
    {
        return $this->hasMany(ObligationAmount::class, 'obligation_id', 'id');
    }

    public function obligationAdjustments()
    {
        return $this->hasMany(ObligationAdjustment::class, 'obligation_id', 'id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'obligation_id', 'id');
    }

    public function disbursements()
    {
        return $this->hasMany(Disbursement::class, 'obligation_id', 'id');
    }
}
