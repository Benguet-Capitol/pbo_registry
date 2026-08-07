<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const EXEMPT_FROM_RESTRICTION = ['Administrator', 'Developer'];
    
    protected $fillable = ['name', 'is_login_restricted'];

    protected $casts = [
        'is_login_restricted' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}