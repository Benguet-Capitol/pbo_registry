<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class Disbursement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'obligation_amounts_id',
        'obligation_id',
        'purchase_order_id',
        'dv_no',
        'remarks',
        'disbursement_date',
        'disbursement_amount',
        'status',
    ];

    public function obligationAmount()
    {
        return $this->belongsTo(ObligationAmount::class, 'obligation_amounts_id');
    }

    public function obligation()
    {
        return $this->belongsTo(Obligation::class, 'obligation_id');
    }

    public function appropriation()
    {
        return $this->obligationAmount->appropriation();
    }
}
