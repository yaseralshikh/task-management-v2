<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'national_id',
        'password',
        'avatar',
        'phone',
        'job_title',
        'bio',
        'timezone',
        'language',
        'date_format',
        'time_format',
        'week_starts_on',
        'theme',
        'is_owner',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_owner' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ============================================================================
    // العلاقات - الفرق
    // ============================================================================
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role', 'deleted_at')
            ->withTimestamps();
    }

    // العلاقات - المشاريع
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role', 'deleted_at')
            ->withTimestamps();
    }

    // العلاقات - المهام
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    // العلاقات - الصلاحيات
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['entity_type', 'entity_id'])
            ->withTimestamps();
    }

    // العلاقات - أخرى
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function activities()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'created_by');
    }

    public function sentInvitations()
    {
        return $this->hasMany(Invitation::class, 'inviter_id');
    }

    public function receivedInvitations()
    {
        return $this->hasMany(Invitation::class, 'invitee_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }

    // Methods - الصلاحيات
    public function hasRole(string $roleSlug, $entity = null): bool
    {
        $query = $this->roles()->where('slug', $roleSlug);

        if ($entity) {
            $query->wherePivot('entity_type', get_class($entity))
                  ->wherePivot('entity_id', $entity->id);
        }

        return $query->exists();
    }

    public function hasPermission(string $permissionSlug, $entity = null): bool
    {
        $roles = $this->roles;

        if ($entity) {
            $roles = $roles->filter(function ($role) use ($entity) {
                return $role->pivot->entity_type === get_class($entity)
                    && $role->pivot->entity_id === $entity->id;
            });
        }

        foreach ($roles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    public function assignRole(string $roleSlug, $entity = null): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        
        $this->roles()->attach($role->id, [
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity ? $entity->id : null,
        ]);
    }

    public function removeRole(string $roleSlug, $entity = null): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        
        $query = $this->roles()->where('role_id', $role->id);
        
        if ($entity) {
            $query->wherePivot('entity_type', get_class($entity))
                  ->wherePivot('entity_id', $entity->id);
        }
        
        $query->detach();
    }

    // Methods - الفرق
    public function isTeamOwner(Team $team): bool
    {
        return $this->id === $team->owner_id;
    }

    public function isTeamMember(Team $team): bool
    {
        return $this->teams()->where('team_id', $team->id)->exists();
    }

    public function isTeamAdmin(Team $team): bool
    {
        return $this->teams()
            ->where('team_id', $team->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    // Methods - المشاريع
    public function isProjectOwner(Project $project): bool
    {
        return $this->id === $project->owner_id;
    }

    public function isProjectMember(Project $project): bool
    {
        return $this->projects()->where('project_id', $project->id)->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOwners($query)
    {
        return $query->where('is_owner', true);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }    
}
