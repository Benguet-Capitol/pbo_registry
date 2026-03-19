<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use App\Traits\LogsActivity;

class Obligation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'office_allotment_class_id',
        'appropriation_id',
        'obr_no',
        'obr_type',
        'obr_date',
        'particulars',
        'remarks',
        'processed_by',
        'payment_remarks'
    ];

    // Add custom attributes to be included in activity log
    protected $logAttributes = [
        'office_allotment_class_id',
        'appropriation_id',
        'obr_no',
        'obr_type',
        'obr_date',
        'particulars',
        'remarks',
        'processed_by',
        'payment_remarks'
    ];

    /**
     * Get the total OBR amount from all obligation amounts
     *
     * @return float
     */
    public function getTotalObrAmount()
    {
        return $this->obligationAmounts()->sum('obr_amount');
    }

    /**
     * Get the total adjustments amount
     *
     * @return float
     */
    public function getTotalAdjustments()
    {
        return $this->obligationAdjustments()->sum('adjustment_amount');
    }

    /**
     * Get the total amount including adjustments
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->getTotalObrAmount() + $this->getTotalAdjustments();
    }

    /**
     * Get total Purchase Orders amount
     *
     * @return float
     */
    public function getTotalPurchaseOrders()
    {
        return $this->purchaseOrders()->sum('po_amount');
    }

    /**
     * Get the remaining balance after Purchase Orders
     *
     * @return float
     */
    public function getRemainingBalance()
    {
        return $this->getTotalAmount() - $this->getTotalPurchaseOrders();
    }

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

    public function files()
    {
        return $this->hasMany(ObligationFile::class, 'obligation_id', 'id');
    }
}
