<?php

/**
 * =============================================================================
 * أمثلة على Seeders & Models للنظام
 * =============================================================================
 * 
 * هذا الملف يحتوي على أمثلة عملية لـ:
 * 1. Database Seeders
 * 2. Models مع العلاقات
 * 3. Policies
 * 4. Observers
 */

// =============================================================================
// 1. SEEDERS
// =============================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

/**
 * RolesAndPermissionsSeeder
 * 
 * تشغيل: php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الأدوار الأساسية
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'صلاحيات كاملة على النظام',
                'is_system' => true,
            ],
            [
                'name' => 'Team Owner',
                'slug' => 'team-owner',
                'description' => 'مالك الفريق - صلاحيات كاملة على الفريق',
                'is_system' => true,
            ],
            [
                'name' => 'Team Admin',
                'slug' => 'team-admin',
                'description' => 'مدير الفريق - صلاحيات إدارية',
                'is_system' => false,
            ],
            [
                'name' => 'Team Member',
                'slug' => 'team-member',
                'description' => 'عضو فريق - صلاحيات محدودة',
                'is_system' => false,
            ],
            [
                'name' => 'Project Manager',
                'slug' => 'project-manager',
                'description' => 'مدير مشروع - صلاحيات كاملة على المشروع',
                'is_system' => false,
            ],
            [
                'name' => 'Project Member',
                'slug' => 'project-member',
                'description' => 'عضو مشروع - مشاركة في المهام',
                'is_system' => false,
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'مراقب - قراءة فقط',
                'is_system' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // إنشاء الصلاحيات
        $permissions = [
            // Teams
            ['name' => 'View Teams', 'slug' => 'view-teams', 'group' => 'teams'],
            ['name' => 'Create Teams', 'slug' => 'create-teams', 'group' => 'teams'],
            ['name' => 'Edit Teams', 'slug' => 'edit-teams', 'group' => 'teams'],
            ['name' => 'Delete Teams', 'slug' => 'delete-teams', 'group' => 'teams'],
            ['name' => 'Manage Team Members', 'slug' => 'manage-team-members', 'group' => 'teams'],
            
            // Projects
            ['name' => 'View Projects', 'slug' => 'view-projects', 'group' => 'projects'],
            ['name' => 'Create Projects', 'slug' => 'create-projects', 'group' => 'projects'],
            ['name' => 'Edit Projects', 'slug' => 'edit-projects', 'group' => 'projects'],
            ['name' => 'Delete Projects', 'slug' => 'delete-projects', 'group' => 'projects'],
            ['name' => 'Archive Projects', 'slug' => 'archive-projects', 'group' => 'projects'],
            ['name' => 'Manage Project Members', 'slug' => 'manage-project-members', 'group' => 'projects'],
            
            // Tasks
            ['name' => 'View Tasks', 'slug' => 'view-tasks', 'group' => 'tasks'],
            ['name' => 'Create Tasks', 'slug' => 'create-tasks', 'group' => 'tasks'],
            ['name' => 'Edit Tasks', 'slug' => 'edit-tasks', 'group' => 'tasks'],
            ['name' => 'Delete Tasks', 'slug' => 'delete-tasks', 'group' => 'tasks'],
            ['name' => 'Assign Tasks', 'slug' => 'assign-tasks', 'group' => 'tasks'],
            ['name' => 'Change Task Status', 'slug' => 'change-task-status', 'group' => 'tasks'],
            
            // Comments
            ['name' => 'View Comments', 'slug' => 'view-comments', 'group' => 'comments'],
            ['name' => 'Create Comments', 'slug' => 'create-comments', 'group' => 'comments'],
            ['name' => 'Edit Comments', 'slug' => 'edit-comments', 'group' => 'comments'],
            ['name' => 'Delete Comments', 'slug' => 'delete-comments', 'group' => 'comments'],
            
            // Attachments
            ['name' => 'Upload Attachments', 'slug' => 'upload-attachments', 'group' => 'attachments'],
            ['name' => 'Delete Attachments', 'slug' => 'delete-attachments', 'group' => 'attachments'],
            
            // Time Tracking
            ['name' => 'Log Time', 'slug' => 'log-time', 'group' => 'time'],
            ['name' => 'View Time Entries', 'slug' => 'view-time-entries', 'group' => 'time'],
            ['name' => 'Edit Time Entries', 'slug' => 'edit-time-entries', 'group' => 'time'],
            ['name' => 'Delete Time Entries', 'slug' => 'delete-time-entries', 'group' => 'time'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'view-reports', 'group' => 'reports'],
            ['name' => 'Create Reports', 'slug' => 'create-reports', 'group' => 'reports'],
            ['name' => 'Export Data', 'slug' => 'export-data', 'group' => 'reports'],
            
            // Users
            ['name' => 'View Users', 'slug' => 'view-users', 'group' => 'users'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'group' => 'users'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'group' => 'users'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // ربط الصلاحيات بالأدوار
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Super Admin - كل الصلاحيات
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $superAdmin->permissions()->attach(Permission::all());

        // Team Owner - صلاحيات الفريق كاملة
        $teamOwner = Role::where('slug', 'team-owner')->first();
        $teamOwner->permissions()->attach(
            Permission::whereIn('group', ['teams', 'projects', 'tasks', 'comments', 'attachments', 'time', 'reports'])->get()
        );

        // Team Admin
        $teamAdmin = Role::where('slug', 'team-admin')->first();
        $teamAdmin->permissions()->attach(
            Permission::whereIn('slug', [
                'view-teams', 'edit-teams', 'manage-team-members',
                'view-projects', 'create-projects', 'edit-projects', 'manage-project-members',
                'view-tasks', 'create-tasks', 'edit-tasks', 'assign-tasks', 'change-task-status',
                'view-comments', 'create-comments',
                'upload-attachments',
                'log-time', 'view-time-entries',
                'view-reports',
            ])->get()
        );

        // Team Member
        $teamMember = Role::where('slug', 'team-member')->first();
        $teamMember->permissions()->attach(
            Permission::whereIn('slug', [
                'view-teams',
                'view-projects',
                'view-tasks', 'create-tasks', 'edit-tasks', 'change-task-status',
                'view-comments', 'create-comments', 'edit-comments',
                'upload-attachments',
                'log-time', 'view-time-entries', 'edit-time-entries',
            ])->get()
        );

        // Project Manager
        $projectManager = Role::where('slug', 'project-manager')->first();
        $projectManager->permissions()->attach(
            Permission::whereIn('slug', [
                'view-projects', 'edit-projects', 'manage-project-members',
                'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'assign-tasks', 'change-task-status',
                'view-comments', 'create-comments', 'delete-comments',
                'upload-attachments', 'delete-attachments',
                'view-time-entries',
                'view-reports', 'create-reports',
            ])->get()
        );

        // Project Member
        $projectMember = Role::where('slug', 'project-member')->first();
        $projectMember->permissions()->attach(
            Permission::whereIn('slug', [
                'view-projects',
                'view-tasks', 'create-tasks', 'edit-tasks', 'change-task-status',
                'view-comments', 'create-comments', 'edit-comments',
                'upload-attachments',
                'log-time', 'view-time-entries', 'edit-time-entries',
            ])->get()
        );

        // Viewer
        $viewer = Role::where('slug', 'viewer')->first();
        $viewer->permissions()->attach(
            Permission::whereIn('slug', [
                'view-teams',
                'view-projects',
                'view-tasks',
                'view-comments',
                'view-time-entries',
                'view-reports',
            ])->get()
        );
    }
}

// =============================================================================
// 2. MODELS مع العلاقات
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Task Model
 */
class Task extends Model
{
    use SoftDeletes;

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

    // العلاقات الأساسية
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // المهمة الأب
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    // المهام الفرعية
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    // المستخدمون المعينون
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    // Checklist Items
    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class);
    }

    // Time Entries
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    // Dependencies
    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
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

    // Accessors & Mutators
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

    // Methods
    public function complete(User $user): void
    {
        $this->update([
            'status' => 'done',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function calculateActualHours(): float
    {
        return $this->timeEntries()->sum('duration_minutes') / 60;
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
}

/**
 * Project Model
 */
class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'budget',
        'currency',
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
        'budget' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    // العلاقات
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

    // Polymorphic
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

    // Methods
    public function calculateProgress(): float
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        
        return round(($completedTasks / $totalTasks) * 100, 2);
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
}

/**
 * Team Model
 */
class Team extends Model
{
    use SoftDeletes;

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

    // العلاقات
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
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

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}

/**
 * Role Model
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['entity_type', 'entity_id'])
            ->withTimestamps();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()
            ->where('slug', $permission)
            ->exists();
    }
}

/**
 * User Model Extensions
 */
class User extends Authenticatable
{
    // ... existing code ...

    // الصلاحيات
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['entity_type', 'entity_id'])
            ->withTimestamps();
    }

    public function hasRole(string $role, $entity = null): bool
    {
        $query = $this->roles()->where('slug', $role);

        if ($entity) {
            $query->where('entity_type', get_class($entity))
                  ->where('entity_id', $entity->id);
        }

        return $query->exists();
    }

    public function hasPermission(string $permission, $entity = null): bool
    {
        $roles = $this->roles;

        if ($entity) {
            $roles = $roles->filter(function ($role) use ($entity) {
                return $role->pivot->entity_type === get_class($entity)
                    && $role->pivot->entity_id === $entity->id;
            });
        }

        foreach ($roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function assignRole(string $roleSlug, $entity = null): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        
        $pivot = ['role_id' => $role->id, 'user_id' => $this->id];
        
        if ($entity) {
            $pivot['entity_type'] = get_class($entity);
            $pivot['entity_id'] = $entity->id;
        }
        
        $this->roles()->attach($role->id, [
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity ? $entity->id : null,
        ]);
    }

    // العلاقات
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}

// =============================================================================
// 3. POLICIES
// =============================================================================

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    /**
     * مشاهدة المهمة
     */
    public function view(User $user, Task $task): bool
    {
        // المنشئ
        if ($task->created_by === $user->id) {
            return true;
        }

        // المعين للمهمة
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // عضو في المهمة
        if ($task->assignedUsers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // لديه صلاحية view-tasks على المشروع
        if ($user->hasPermission('view-tasks', $task->project)) {
            return true;
        }

        return false;
    }

    /**
     * تعديل المهمة
     */
    public function update(User $user, Task $task): bool
    {
        // المنشئ
        if ($task->created_by === $user->id) {
            return true;
        }

        // لديه صلاحية edit-tasks
        if ($user->hasPermission('edit-tasks', $task->project)) {
            return true;
        }

        return false;
    }

    /**
     * حذف المهمة
     */
    public function delete(User $user, Task $task): bool
    {
        // المنشئ
        if ($task->created_by === $user->id) {
            return true;
        }

        // لديه صلاحية delete-tasks
        if ($user->hasPermission('delete-tasks', $task->project)) {
            return true;
        }

        return false;
    }

    /**
     * تعيين المهمة
     */
    public function assign(User $user, Task $task): bool
    {
        return $user->hasPermission('assign-tasks', $task->project);
    }

    /**
     * تغيير حالة المهمة
     */
    public function changeStatus(User $user, Task $task): bool
    {
        // المعين للمهمة
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // عضو في المهمة
        if ($task->assignedUsers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // لديه صلاحية change-task-status
        if ($user->hasPermission('change-task-status', $task->project)) {
            return true;
        }

        return false;
    }
}

// =============================================================================
// 4. OBSERVERS
// =============================================================================

namespace App\Observers;

use App\Models\Task;
use App\Models\ActivityLog;
use App\Notifications\TaskAssignedNotification;

class TaskObserver
{
    /**
     * عند إنشاء مهمة جديدة
     */
    public function created(Task $task): void
    {
        // تسجيل في Activity Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'created',
            'description' => "قام بإنشاء مهمة جديدة: {$task->title}",
            'properties' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // إرسال إشعار للمعين
        if ($task->assigned_to) {
            $task->assignedUser->notify(new TaskAssignedNotification($task));
        }
    }

    /**
     * عند تحديث مهمة
     */
    public function updated(Task $task): void
    {
        $changes = $task->getChanges();
        
        // تسجيل التغييرات
        ActivityLog::create([
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'updated',
            'description' => "قام بتحديث المهمة: {$task->title}",
            'properties' => [
                'changes' => $changes,
                'old' => $task->getOriginal(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // إذا تم تغيير assigned_to
        if (isset($changes['assigned_to']) && $changes['assigned_to']) {
            $task->assignedUser->notify(new TaskAssignedNotification($task));
        }

        // إذا تم تغيير الحالة إلى مكتملة
        if (isset($changes['status']) && $changes['status'] === 'done') {
            $task->update(['completed_at' => now()]);
            
            // إشعار المنشئ
            $task->creator->notify(new TaskCompletedNotification($task));
        }
    }

    /**
     * عند حذف مهمة
     */
    public function deleted(Task $task): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'deleted',
            'description' => "قام بحذف المهمة: {$task->title}",
            'properties' => [
                'task' => $task->toArray(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

/**
 * =============================================================================
 * تسجيل الـ Observers في AppServiceProvider
 * =============================================================================
 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Task;
use App\Models\Project;
use App\Observers\TaskObserver;
use App\Observers\ProjectObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);
    }
}
