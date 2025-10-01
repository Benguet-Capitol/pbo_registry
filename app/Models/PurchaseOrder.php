<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'obligation_amounts_id',
        'obligation_id',
        'po_number',
        'pr_no',
        'po_date',
        'po_type',
        'status',
        'po_remarks',
        'supplier',
        'delivery_period',
        'delivery_date',
        'po_amount',
        'delivery_amount',
        'delivery_remarks',
    ];

    /**
     * Get the obligation that owns the purchase order.
     */
    public function obligationAmount()
    {
        return $this->belongsTo(ObligationAmount::class, 'obligation_amounts_id');
    }

    public function obligation()
    {
        return $this->belongsTo(Obligation::class, 'obligation_id');
    }
}