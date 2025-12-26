<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'team_id',
        'owner_id',
        'start_date',
        'end_date',
        'progress_percentage',
        'status',
        'is_archived',
        'archived_at',
        'archived_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role', 'deleted_at')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function invitations()
    {
        return $this->morphMany(Invitation::class, 'invitable');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
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

    public function archive(User $user): void
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => $user->id,
        ]);
    }

    public function unarchive(): void
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
        ]);
    }

    public function calculateProgress(): float
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public function updateProgress(): void
    {
        $this->update(['progress_percentage' => $this->calculateProgress()]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('owner_id', $user->id);
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->end_date || in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }
        return $this->end_date->isPast();
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }    
}
