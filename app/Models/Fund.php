<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    protected $fillable = [
        'fund',
        'fund_type',
        'fund_code',
    ];

    public function officeAllotmentClasses()
    {
        return $this->hasMany(OfficeAllotmentClass::class, 'fund', 'fund');
    }
}
