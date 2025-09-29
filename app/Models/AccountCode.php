<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'description', 
        'class',
    ];

    public function allotmentClass()
    {
        return $this->belongsTo(AllotmentClass::class, 'class', 'class');
    }

}
