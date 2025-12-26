<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
    ];

    // ============================================================================
    // العلاقات
    // ============================================================================

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
