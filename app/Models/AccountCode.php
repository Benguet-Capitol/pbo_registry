<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class AccountCode extends Model
{
    use HasFactory, LogsActivity;

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
