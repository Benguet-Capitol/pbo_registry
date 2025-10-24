<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class ObligationAmount extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'appropriation_id',
        'obligation_id',
        'account_code',
        'obr_amount',
    ];

    public function appropriation()
    {
        return $this->belongsTo(Appropriation::class, 'appropriation_id');
    }

    public function obligation()
    {
        return $this->belongsTo(Obligation::class, 'obligation_id');
    }
    

    public function accountCode()
    {
        return $this->belongsTo(AccountCode::class, 'account_code', 'code');
    }

    public function obligationAdjustments()
    {
        return $this->hasMany(ObligationAdjustment::class, 'obligation_amounts_id', 'id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'obligation_amounts_id');
    }

    public function disbursements()
    {
        return $this->hasMany(Disbursement::class, 'obligation_amounts_id');
    }
}
