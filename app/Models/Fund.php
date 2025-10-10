<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Fund extends Model
{
    use LogsActivity;
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
