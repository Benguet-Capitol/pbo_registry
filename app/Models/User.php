<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'usertype',
        'office',
        'is_restricted',
    ];
    
    protected $casts = [
        'is_restricted' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'username_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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

    protected static function booted()
    {
        static::saved(function ($user) {
            if ($user->usertype) {
                $user->syncRoles([$user->usertype]);
            }
        });
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
