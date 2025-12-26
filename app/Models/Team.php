<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'color',
        'is_active',
        'max_members',
        'settings',
        'owner_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role', 'deleted_at')
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function invitations()
    {
        return $this->morphMany(Invitation::class, 'invitable');
    }

    // Methods
    public function addMember(User $user, string $role = 'member'): void
    {
        $this->members()->attach($user->id, ['role' => $role]);
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function updateMemberRole(User $user, string $role): void
    {
        $this->members()->updateExistingPivot($user->id, ['role' => $role]);
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function getMemberCount(): int
    {
        return $this->members()->count();
    }

    public function canAddMoreMembers(): bool
    {
        if (is_null($this->max_members)) {
            return true;
        }
        return $this->getMemberCount() < $this->max_members;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('owner_id', $user->id);
    }    
}
