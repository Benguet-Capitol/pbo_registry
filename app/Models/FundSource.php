<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundSource extends Model
{
    protected $fillable = [
        'category',
        'source',
    ];

    public function officeAllotmentClasses()
    {
        return $this->hasMany(OfficeAllotmentClass::class, 'fund_source', 'source');
    }

}
