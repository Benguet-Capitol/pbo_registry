<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ObligationAdjustment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'obligation_id',
        'obligation_amounts_id',
        'adjustment_date',
        'adjustment_remarks',
        'adjustment_amount',
        'adjusted_by',
    ];

    public function obligation()
    {
        return $this->belongsTo(Obligation::class);
    }

    public function obligationAmount()
    {
        return $this->belongsTo(ObligationAmount::class, 'obligation_amounts_id', 'id');
    }

    public function appropriation()
    {
        return $this->obligationAmount->appropriation();
    }
}