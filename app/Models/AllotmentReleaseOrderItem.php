<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllotmentReleaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'allotment_release_order_id',
        'appropriation_id',
        'ppa_code',
        'account_code',
        'ppa_description',
        'programs',
        'authorized_appropriation',
        'for_later_release',
        'previously_released_amount',
        'this_release',
    ];

    protected $casts = [
        'authorized_appropriation' => 'decimal:2',
        'for_later_release' => 'decimal:2',
        'previously_released_amount' => 'decimal:2',
        'this_release' => 'decimal:2',
    ];

    public function allotmentReleaseOrder()
    {
        return $this->belongsTo(AllotmentReleaseOrder::class);
    }

    public function appropriation()
    {
        return $this->belongsTo(Appropriation::class);
    }
}
