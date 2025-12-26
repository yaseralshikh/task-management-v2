<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // ============================================================================
    // العلاقات
    // ============================================================================

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['entity_type', 'entity_id'])
            ->withTimestamps();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()
            ->where('slug', $permissionSlug)
            ->exists();
    }

    public function givePermissionTo(string $permissionSlug): void
    {
        $permission = Permission::where('slug', $permissionSlug)->firstOrFail();
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function revokePermissionTo(string $permissionSlug): void
    {
        $permission = Permission::where('slug', $permissionSlug)->firstOrFail();
        $this->permissions()->detach($permission->id);
    }    
}
