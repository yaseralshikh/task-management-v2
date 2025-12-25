<?php

/**
 * ============================================================================
 * Laravel 12 Policies - نظام إدارة المهام
 * ============================================================================
 */

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

// ============================================================================
// TeamPolicy
// ============================================================================
namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * عرض الفريق
     */
    public function view(User $user, Team $team): bool
    {
        // المالك
        if ($team->owner_id === $user->id) {
            return true;
        }

        // عضو في الفريق
        if ($team->isMember($user)) {
            return true;
        }

        // لديه صلاحية view-teams
        if ($user->hasPermission('view-teams')) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء فريق جديد
     */
    public function create(User $user): bool
    {
        // أي مستخدم نشط يمكنه إنشاء فريق
        return $user->is_active;
    }

    /**
     * تعديل الفريق
     */
    public function update(User $user, Team $team): bool
    {
        // المالك
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Admin في الفريق
        if ($team->isTeamAdmin($user)) {
            return true;
        }

        // لديه صلاحية edit-teams
        if ($user->hasPermission('edit-teams', $team)) {
            return true;
        }

        return false;
    }

    /**
     * حذف الفريق
     */
    public function delete(User $user, Team $team): bool
    {
        // المالك فقط
        if ($team->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية delete-teams
        if ($user->hasPermission('delete-teams', $team)) {
            return true;
        }

        return false;
    }

    /**
     * إضافة عضو للفريق
     */
    public function addMember(User $user, Team $team): bool
    {
        // المالك
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Admin في الفريق
        if ($team->isTeamAdmin($user)) {
            return true;
        }

        // لديه صلاحية manage-team-members
        if ($user->hasPermission('manage-team-members', $team)) {
            return true;
        }

        return false;
    }

    /**
     * إزالة عضو من الفريق
     */
    public function removeMember(User $user, Team $team): bool
    {
        return $this->addMember($user, $team);
    }

    /**
     * تحديث دور عضو
     */
    public function updateMemberRole(User $user, Team $team): bool
    {
        return $this->addMember($user, $team);
    }
}

// ============================================================================
// ProjectPolicy
// ============================================================================
namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * عرض المشروع
     */
    public function view(User $user, Project $project): bool
    {
        // المالك
        if ($project->owner_id === $user->id) {
            return true;
        }

        // عضو في المشروع
        if ($project->isMember($user)) {
            return true;
        }

        // عضو في فريق المشروع
        if ($project->team && $project->team->isMember($user)) {
            return true;
        }

        // لديه صلاحية view-projects
        if ($user->hasPermission('view-projects', $project->team)) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء مشروع جديد
     */
    public function create(User $user): bool
    {
        // أي مستخدم نشط يمكنه إنشاء مشروع
        return $user->is_active;
    }

    /**
     * تعديل المشروع
     */
    public function update(User $user, Project $project): bool
    {
        // المالك
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Admin في المشروع
        if ($project->members()->where('user_id', $user->id)
            ->wherePivot('role', 'admin')->exists()) {
            return true;
        }

        // لديه صلاحية edit-projects
        if ($user->hasPermission('edit-projects', $project)) {
            return true;
        }

        return false;
    }

    /**
     * حذف المشروع
     */
    public function delete(User $user, Project $project): bool
    {
        // المالك فقط
        if ($project->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية delete-projects
        if ($user->hasPermission('delete-projects', $project)) {
            return true;
        }

        return false;
    }

    /**
     * أرشفة المشروع
     */
    public function archive(User $user, Project $project): bool
    {
        // المالك
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Admin في المشروع
        if ($project->members()->where('user_id', $user->id)
            ->wherePivot('role', 'admin')->exists()) {
            return true;
        }

        // لديه صلاحية archive-projects
        if ($user->hasPermission('archive-projects', $project)) {
            return true;
        }

        return false;
    }

    /**
     * إضافة عضو للمشروع
     */
    public function addMember(User $user, Project $project): bool
    {
        // المالك
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Admin في المشروع
        if ($project->members()->where('user_id', $user->id)
            ->wherePivot('role', 'admin')->exists()) {
            return true;
        }

        // لديه صلاحية manage-project-members
        if ($user->hasPermission('manage-project-members', $project)) {
            return true;
        }

        return false;
    }

    /**
     * إزالة عضو من المشروع
     */
    public function removeMember(User $user, Project $project): bool
    {
        return $this->addMember($user, $project);
    }
}

// ============================================================================
// TaskPolicy
// ============================================================================
namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * عرض المهمة
     */
    public function view(User $user, Task $task): bool
    {
        // منشئ المهمة
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

        // عضو في المشروع
        if ($task->project->isMember($user)) {
            return true;
        }

        // لديه صلاحية view-tasks
        if ($user->hasPermission('view-tasks', $task->project)) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء مهمة جديدة
     */
    public function create(User $user, $project = null): bool
    {
        // يجب أن يكون عضو في المشروع
        if ($project && !$project->isMember($user)) {
            return false;
        }

        // لديه صلاحية create-tasks
        if ($project && $user->hasPermission('create-tasks', $project)) {
            return true;
        }

        return true;
    }

    /**
     * تعديل المهمة
     */
    public function update(User $user, Task $task): bool
    {
        // منشئ المهمة
        if ($task->created_by === $user->id) {
            return true;
        }

        // المعين للمهمة
        if ($task->assigned_to === $user->id) {
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
        // منشئ المهمة
        if ($task->created_by === $user->id) {
            return true;
        }

        // مالك المشروع
        if ($task->project->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية delete-tasks
        if ($user->hasPermission('delete-tasks', $task->project)) {
            return true;
        }

        return false;
    }

    /**
     * تعيين مستخدمين للمهمة
     */
    public function assign(User $user, Task $task): bool
    {
        // منشئ المهمة
        if ($task->created_by === $user->id) {
            return true;
        }

        // مالك المشروع
        if ($task->project->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية assign-tasks
        if ($user->hasPermission('assign-tasks', $task->project)) {
            return true;
        }

        return false;
    }

    /**
     * تغيير حالة المهمة
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // منشئ المهمة
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

        // لديه صلاحية change-task-status
        if ($user->hasPermission('change-task-status', $task->project)) {
            return true;
        }

        return false;
    }
}

// ============================================================================
// CommentPolicy
// ============================================================================
namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * عرض التعليق
     */
    public function view(User $user, Comment $comment): bool
    {
        // يجب أن يملك صلاحية عرض الـ commentable
        return true;
    }

    /**
     * تعديل التعليق
     */
    public function update(User $user, Comment $comment): bool
    {
        // صاحب التعليق فقط
        if ($comment->user_id === $user->id) {
            return true;
        }

        // لديه صلاحية edit-comments
        if ($user->hasPermission('edit-comments')) {
            return true;
        }

        return false;
    }

    /**
     * حذف التعليق
     */
    public function delete(User $user, Comment $comment): bool
    {
        // صاحب التعليق
        if ($comment->user_id === $user->id) {
            return true;
        }

        // مالك الـ commentable
        $commentable = $comment->commentable;
        if ($commentable && isset($commentable->owner_id) && $commentable->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية delete-comments
        if ($user->hasPermission('delete-comments')) {
            return true;
        }

        return false;
    }
}

// ============================================================================
// AttachmentPolicy
// ============================================================================
namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    use HandlesAuthorization;

    /**
     * حذف المرفق
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        // صاحب المرفق
        if ($attachment->user_id === $user->id) {
            return true;
        }

        // مالك الـ attachable
        $attachable = $attachment->attachable;
        if ($attachable && isset($attachable->owner_id) && $attachable->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية delete-attachments
        if ($user->hasPermission('delete-attachments')) {
            return true;
        }

        return false;
    }
}

// ============================================================================
// TimeEntryPolicy
// ============================================================================
namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    use HandlesAuthorization;

    /**
     * عرض سجل الوقت
     */
    public function view(User $user, TimeEntry $timeEntry): bool
    {
        // صاحب السجل
        if ($timeEntry->user_id === $user->id) {
            return true;
        }

        // عضو في المشروع
        if ($timeEntry->task->project->isMember($user)) {
            return true;
        }

        // لديه صلاحية view-time-entries
        if ($user->hasPermission('view-time-entries', $timeEntry->task->project)) {
            return true;
        }

        return false;
    }

    /**
     * تعديل سجل الوقت
     */
    public function update(User $user, TimeEntry $timeEntry): bool
    {
        // صاحب السجل فقط
        if ($timeEntry->user_id === $user->id) {
            return true;
        }

        // لديه صلاحية edit-time-entries
        if ($user->hasPermission('edit-time-entries', $timeEntry->task->project)) {
            return true;
        }

        return false;
    }

    /**
     * حذف سجل الوقت
     */
    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        // صاحب السجل
        if ($timeEntry->user_id === $user->id) {
            return true;
        }

        // مالك المشروع
        if ($timeEntry->task->project->owner_id === $user->id) {
            return true;
        }

        // لديه صلاحية delete-time-entries
        if ($user->hasPermission('delete-time-entries', $timeEntry->task->project)) {
            return true;
        }

        return false;
    }
}

// ============================================================================
// TagPolicy
// ============================================================================
namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    use HandlesAuthorization;

    /**
     * تعديل الوسم
     */
    public function update(User $user, Tag $tag): bool
    {
        // منشئ الوسم
        if ($tag->created_by === $user->id) {
            return true;
        }

        // مدير النظام
        if ($user->is_owner) {
            return true;
        }

        return false;
    }

    /**
     * حذف الوسم
     */
    public function delete(User $user, Tag $tag): bool
    {
        // منشئ الوسم
        if ($tag->created_by === $user->id) {
            return true;
        }

        // مدير النظام
        if ($user->is_owner) {
            return true;
        }

        return false;
    }
}

// ============================================================================
// تسجيل Policies في AuthServiceProvider
// ============================================================================
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Team;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\TimeEntry;
use App\Models\Tag;
use App\Policies\TeamPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\CommentPolicy;
use App\Policies\AttachmentPolicy;
use App\Policies\TimeEntryPolicy;
use App\Policies\TagPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Comment::class => CommentPolicy::class,
        Attachment::class => AttachmentPolicy::class,
        TimeEntry::class => TimeEntryPolicy::class,
        Tag::class => TagPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
