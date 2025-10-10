<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Program extends Model
{
    use LogsActivity;
    protected $fillable = [
        'program'
    ];
}
