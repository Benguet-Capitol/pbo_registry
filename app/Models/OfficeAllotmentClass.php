<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OfficeAllotmentClass extends Model
{
    use HasFactory;

    // Define the fillable attributes
    protected $fillable = [
        'id',
        'year',
        'office',
        'sub_office',
        'fund',
        'fund_source',
        'class',
        'date_approved',
        'fpp_code',
        'responsibility_code',
        'office_abbreviation',
        'date_approved',
        'mfo_services',
    ];

    public function allotmentClass()
    {
        return $this->belongsTo(AllotmentClass::class, 'class', 'class');
    }

    public function offices()
    {
        return $this->belongsTo(Office::class, 'office', 'id');
    }

    public function appropriations()
    {
        return $this->hasMany(Appropriation::class, 'office_allotment_class_id');
    }
    
    public function obligationAmounts()
    {
        return $this->hasManyThrough(
            ObligationAmount::class,
            Appropriation::class,
            'office_allotment_class_id', // Foreign key on appropriations table
            'appropriation_id', // Foreign key on obligation_amounts table
            'id', // Local key on office_allotment_classes table
            'id' // Local key on appropriations table
        );
    }

    public function fundSourceRelation()
    {
        return $this->belongsTo(FundSource::class, 'fund_source', 'source');
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class, 'fund', 'fund');
    }

    public function realignments()
    {
        return $this->hasMany(Realignment::class, 'office_allotment_classes_id');
    }

    public function supplementals()
    {
        return $this->hasMany(Supplemental::class, 'office_allotment_classes_id');
    }

}
