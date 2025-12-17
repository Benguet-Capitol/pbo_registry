<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['employee_id', 'name', 'designation', 'office'];

    /**
     * Get the office that the user belongs to.
     */
    public function officeRelation()
    {
        return $this->belongsTo(Office::class, 'office', 'id');
    }

    /**
     * Get the office abbreviation attribute.
     * This creates a virtual attribute that can be accessed as $user->office_abbreviation
     */
    public function getOfficeAbbreviationAttribute()
    {
        return $this->officeRelation ? $this->officeRelation->office_abbreviation : 'N/A';
    }

    /**
     * Get the office name attribute.
     * This creates a virtual attribute that can be accessed as $user->office_name
     */
    public function getOfficeNameAttribute()
    {
        return $this->officeRelation ? $this->officeRelation->office_name : 'N/A';
    }
}
