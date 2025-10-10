<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['employee_id', 'name', 'designation', 'office'];
}
