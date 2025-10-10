<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class FundSource extends Model
{
    use LogsActivity;
    protected $fillable = [
        'category',
        'source',
    ];

    public function officeAllotmentClasses()
    {
        return $this->hasMany(OfficeAllotmentClass::class, 'fund_source', 'source');
    }

}
