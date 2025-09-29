<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllotmentClass extends Model
{
    use HasFactory;

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
