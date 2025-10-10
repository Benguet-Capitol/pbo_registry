<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Sector extends Model
{
    use LogsActivity;
    protected $fillable = [
        'sector',
        'sector_code',
        'code',
    ];
}
