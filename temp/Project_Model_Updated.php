<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Project Model - Updated
 * 
 * بدون budget و currency
 */
class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'progress_percentage',
        'team_id',
        'owner_id',
        'start_date',
        'end_date',
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

    // ============================================
    // العلاقات
    // ============================================
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    // Polymorphic Relations
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    // ============================================
    // Scopes
    // ============================================
    
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

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    // ============================================
    // Accessors
    // ============================================
    
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && !$this->is_archived;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getTotalTasksAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getCompletedTasksAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    // ============================================
    // Methods
    // ============================================
    
    /**
     * حساب نسبة التقدم بناءً على المهام
     */
    public function calculateProgress(): float
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * تحديث نسبة التقدم تلقائياً
     */
    public function updateProgress(): void
    {
        $this->update([
            'progress_percentage' => $this->calculateProgress()
        ]);
    }

    /**
     * أرشفة المشروع
     */
    public function archive(User $user): void
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => $user->id,
        ]);
    }

    /**
     * إلغاء الأرشفة
     */
    public function unarchive(): void
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
        ]);
    }

    /**
     * التحقق من ملكية المشروع
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * التحقق من العضوية في المشروع
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * إضافة عضو للمشروع
     */
    public function addMember(User $user, string $role = 'member'): void
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id, ['role' => $role]);
        }
    }

    /**
     * إزالة عضو من المشروع
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * الحصول على المهام المتأخرة
     */
    public function getOverdueTasks()
    {
        return $this->tasks()
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['done'])
            ->get();
    }

    /**
     * الحصول على المهام القادمة
     */
    public function getUpcomingTasks(int $days = 7)
    {
        return $this->tasks()
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->whereNotIn('status', ['done'])
            ->get();
    }

    /**
     * إحصائيات المشروع
     */
    public function getStats(): array
    {
        return [
            'total_tasks' => $this->total_tasks,
            'completed_tasks' => $this->completed_tasks,
            'in_progress_tasks' => $this->tasks()->where('status', 'in_progress')->count(),
            'todo_tasks' => $this->tasks()->where('status', 'todo')->count(),
            'overdue_tasks' => $this->getOverdueTasks()->count(),
            'progress_percentage' => $this->progress_percentage,
            'total_members' => $this->members()->count(),
        ];
    }
}
