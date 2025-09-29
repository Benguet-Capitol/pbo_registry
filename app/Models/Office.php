<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'office_name',
        'office_abbreviation',
        'sub_office',
        'fund',
        'fpp_code',
        'responsibility_code',
        'mfo_services',
        'branch',
    ];

    public function officeAllotmentClasses()
    {
        return $this->hasMany(OfficeAllotmentClass::class, 'office', 'id');
    }
}
