<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class AllotmentClass extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'class',
        'description',
        'category',
    ];

        public function officeAllotmentClasses()
    {
        return $this->hasMany(OfficeAllotmentClass::class, 'class', 'class');
    }
}
