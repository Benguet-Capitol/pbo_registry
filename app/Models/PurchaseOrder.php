<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use HasFactory, LogsActivity;

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

    /**
     * Get files for this purchase order
     */
    public function files()
    {
        return PurchaseOrderFile::where('po_number', $this->po_number)->orderBy('created_at', 'desc');
    }
}