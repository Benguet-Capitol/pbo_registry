<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Supplemental extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'office_allotment_classes_id',
        'appropriations_id',
        'basis_no',
        'supplemental_no',
        'supplemental_date',
        'basis',
        'type',
        'amount',
        'quarter1',
        'quarter2',
        'quarter3',
        'quarter4',
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
