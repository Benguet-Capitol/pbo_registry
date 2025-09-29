<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Realignment extends Model
{
    protected $fillable = [
        'office_allotment_classes_id',
        'appropriations_id',
        'realignment_no',
        'realignment_date',
        'amount',
        'type',
        'basis',
        'remarks'
    ];

    public function officeAllotmentClass()
    {
        return $this->belongsTo(OfficeAllotmentClass::class, 'office_allotment_classes_id');
    }

    public function appropriation()
    {
        return $this->belongsTo(Appropriation::class, 'appropriations_id');
    }
}
