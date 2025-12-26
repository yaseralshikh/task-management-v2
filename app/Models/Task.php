<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'team_id',
        'assigned_to',
        'parent_task_id',
        'title',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'completed_at',
        'blocking_reason',
        'is_recurring',
        'recurrence_pattern',
        'order',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'completed_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    public function checklistItems()
    {
        return $this->hasMany(TaskChecklistItem::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependents()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
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

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    // Methods
    public function complete(User $user): void
    {
        $this->update([
            'status' => 'done',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function assignTo(User $user): void
    {
        $this->update(['assigned_to' => $user->id]);
    }

    public function addAssignee(User $user): void
    {
        $this->assignedUsers()->syncWithoutDetaching([$user->id]);
    }

    public function removeAssignee(User $user): void
    {
        $this->assignedUsers()->detach($user->id);
    }

    public function calculateActualHours(): float
    {
        return round($this->timeEntries()->sum('duration_minutes') / 60, 2);
    }

    public function updateActualHours(): void
    {
        $this->update(['actual_hours' => $this->calculateActualHours()]);
    }

    public function calculateProgress(): float
    {
        $total = $this->checklistItems()->count();
        
        if ($total === 0) {
            return $this->progress_percentage ?? 0;
        }

        $completed = $this->checklistItems()->where('is_completed', true)->count();
        
        return round(($completed / $total) * 100, 2);
    }

    public function updateProgress(): void
    {
        $this->update(['progress_percentage' => $this->calculateProgress()]);
    }

    // Scopes
    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to', $user->id)
            ->orWhereHas('assignedUsers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['done']);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
            ->whereNotIn('status', ['done']);
    }

    public function scopeParentTasks($query)
    {
        return $query->whereNull('parent_task_id');
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || in_array($this->status, ['done'])) {
            return false;
        }
        return $this->due_date->isPast();
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'done';
    }

    public function getHasSubtasksAttribute(): bool
    {
        return $this->subtasks()->exists();
    }    
}
