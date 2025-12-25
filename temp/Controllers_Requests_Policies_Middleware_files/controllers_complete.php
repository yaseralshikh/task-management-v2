<?php

/**
 * ============================================================================
 * Laravel 12 Controllers - نظام إدارة المهام
 * ============================================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ============================================================================
// BaseApiController - الكلاس الأساسي
// ============================================================================
class BaseApiController extends Controller
{
    protected function success($data = null, string $message = 'تمت العملية بنجاح', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(string $message = 'حدث خطأ', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function paginated($data, string $message = 'تمت العملية بنجاح'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ]);
    }
}

// ============================================================================
// AuthController - المصادقة
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'national_id' => $request->national_id,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'تم التسجيل بنجاح', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user->is_active) {
            return $this->error('حسابك معطل. يرجى التواصل مع الإدارة', 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'تم تسجيل الخروج بنجاح');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['ownedTeams', 'teams', 'ownedProjects', 'projects']);

        return $this->success($user);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'job_title' => 'sometimes|nullable|string|max:255',
            'bio' => 'sometimes|nullable|string',
            'avatar' => 'sometimes|nullable|image|max:2048',
        ]);

        $user = $request->user();
        $data = $request->except(['email', 'national_id', 'password', 'is_owner']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return $this->success($user, 'تم تحديث الملف الشخصي بنجاح');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('كلمة المرور الحالية غير صحيحة', 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->success(null, 'تم تغيير كلمة المرور بنجاح');
    }
}

// ============================================================================
// TeamController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\User;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;

class TeamController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Team::with(['owner', 'members'])
            ->where(function ($q) use ($request) {
                $q->where('owner_id', $request->user()->id)
                  ->orWhereHas('members', function ($q) use ($request) {
                      $q->where('user_id', $request->user()->id);
                  });
            });

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $teams = $query->paginate($request->get('per_page', 15));

        return $this->paginated($teams);
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = Team::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        // إضافة المالك كعضو admin
        $team->addMember($request->user(), 'admin');

        // إعطاء دور team-owner
        $request->user()->assignRole('team-owner', $team);

        return $this->success($team->load('owner', 'members'), 'تم إنشاء الفريق بنجاح', 201);
    }

    public function show(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $team->load(['owner', 'members', 'projects' => function ($query) {
            $query->latest()->limit(5);
        }]);

        return $this->success($team);
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $team->update($request->validated());

        return $this->success($team->load('owner', 'members'), 'تم تحديث الفريق بنجاح');
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return $this->success(null, 'تم حذف الفريق بنجاح');
    }

    public function addMember(Request $request, Team $team): JsonResponse
    {
        $this->authorize('addMember', $team);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,member',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($team->isMember($user)) {
            return $this->error('المستخدم عضو بالفعل في الفريق', 400);
        }

        if (!$team->canAddMoreMembers()) {
            return $this->error('تم الوصول للحد الأقصى من الأعضاء', 400);
        }

        $team->addMember($user, $request->role);

        return $this->success($team->load('members'), 'تم إضافة العضو بنجاح');
    }

    public function removeMember(Team $team, User $user): JsonResponse
    {
        $this->authorize('removeMember', $team);

        if ($team->owner_id === $user->id) {
            return $this->error('لا يمكن إزالة مالك الفريق', 400);
        }

        if (!$team->isMember($user)) {
            return $this->error('المستخدم ليس عضواً في الفريق', 400);
        }

        $team->removeMember($user);

        return $this->success(null, 'تم إزالة العضو بنجاح');
    }

    public function updateMemberRole(Request $request, Team $team, User $user): JsonResponse
    {
        $this->authorize('updateMemberRole', $team);

        $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        if (!$team->isMember($user)) {
            return $this->error('المستخدم ليس عضواً في الفريق', 400);
        }

        $team->updateMemberRole($user, $request->role);

        return $this->success($team->load('members'), 'تم تحديث دور العضو بنجاح');
    }

    public function members(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $members = $team->members()->withPivot('role', 'created_at')->get();

        return $this->success($members);
    }
}

// ============================================================================
// ProjectController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\User;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;

class ProjectController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['owner', 'team', 'members'])
            ->where(function ($q) use ($request) {
                $q->where('owner_id', $request->user()->id)
                  ->orWhereHas('members', function ($q) use ($request) {
                      $q->where('user_id', $request->user()->id);
                  });
            });

        // الفلاتر
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_archived')) {
            $query->where('is_archived', $request->boolean('is_archived'));
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $projects = $query->paginate($request->get('per_page', 15));

        return $this->paginated($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = Project::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        // إضافة المالك كعضو admin
        $project->addMember($request->user(), 'admin');

        return $this->success($project->load('owner', 'team', 'members'), 'تم إنشاء المشروع بنجاح', 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load([
            'owner',
            'team',
            'members',
            'tasks' => function ($query) {
                $query->with(['assignedUser', 'tags'])->latest()->limit(10);
            },
            'milestones' => function ($query) {
                $query->orderBy('due_date');
            },
        ]);

        return $this->success($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return $this->success($project->load('owner', 'team', 'members'), 'تم تحديث المشروع بنجاح');
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return $this->success(null, 'تم حذف المشروع بنجاح');
    }

    public function archive(Request $request, Project $project): JsonResponse
    {
        $this->authorize('archive', $project);

        $project->archive($request->user());

        return $this->success($project, 'تم أرشفة المشروع بنجاح');
    }

    public function unarchive(Project $project): JsonResponse
    {
        $this->authorize('archive', $project);

        $project->unarchive();

        return $this->success($project, 'تم إلغاء أرشفة المشروع بنجاح');
    }

    public function addMember(Request $request, Project $project): JsonResponse
    {
        $this->authorize('addMember', $project);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,member',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($project->isMember($user)) {
            return $this->error('المستخدم عضو بالفعل في المشروع', 400);
        }

        $project->addMember($user, $request->role);

        return $this->success($project->load('members'), 'تم إضافة العضو بنجاح');
    }

    public function removeMember(Project $project, User $user): JsonResponse
    {
        $this->authorize('removeMember', $project);

        if ($project->owner_id === $user->id) {
            return $this->error('لا يمكن إزالة مالك المشروع', 400);
        }

        if (!$project->isMember($user)) {
            return $this->error('المستخدم ليس عضواً في المشروع', 400);
        }

        $project->removeMember($user);

        return $this->success(null, 'تم إزالة العضو بنجاح');
    }

    public function updateProgress(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->updateProgress();

        return $this->success([
            'progress_percentage' => $project->progress_percentage,
        ], 'تم تحديث التقدم بنجاح');
    }

    public function statistics(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('status', 'done')->count(),
            'in_progress_tasks' => $project->tasks()->where('status', 'in_progress')->count(),
            'todo_tasks' => $project->tasks()->where('status', 'todo')->count(),
            'overdue_tasks' => $project->tasks()->overdue()->count(),
            'total_members' => $project->members()->count(),
            'progress_percentage' => $project->progress_percentage,
            'status' => $project->status,
            'is_overdue' => $project->is_overdue,
        ];

        return $this->success($stats);
    }
}

// ============================================================================
// TaskController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use Illuminate\Support\Facades\DB;

class TaskController extends BaseApiController
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $query = $project->tasks()
            ->with(['assignedUser', 'creator', 'assignedUsers', 'tags', 'checklistItems']);

        // الفلاتر
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->boolean('parent_only')) {
            $query->parentTasks();
        }

        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $tasks = $query->paginate($request->get('per_page', 15));

        return $this->paginated($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        DB::beginTransaction();
        try {
            $task = $project->tasks()->create([
                ...$request->validated(),
                'created_by' => $request->user()->id,
                'team_id' => $project->team_id,
            ]);

            // إضافة المستخدمين المعينين
            if ($request->has('assigned_user_ids')) {
                $task->assignedUsers()->attach($request->assigned_user_ids);
            }

            // إضافة الوسوم
            if ($request->has('tag_ids')) {
                $task->tags()->attach($request->tag_ids);
            }

            // إضافة checklist items
            if ($request->has('checklist_items')) {
                foreach ($request->checklist_items as $index => $item) {
                    $task->checklistItems()->create([
                        'title' => $item['title'],
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return $this->success(
                $task->load(['assignedUser', 'creator', 'assignedUsers', 'tags', 'checklistItems']),
                'تم إنشاء المهمة بنجاح',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء إنشاء المهمة', 500);
        }
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load([
            'assignedUser',
            'creator',
            'project',
            'team',
            'assignedUsers',
            'parent',
            'subtasks',
            'checklistItems',
            'tags',
            'comments.user',
            'attachments',
            'timeEntries.user',
            'dependencies.dependsOnTask',
            'dependents.task',
        ]);

        return $this->success($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        DB::beginTransaction();
        try {
            $task->update($request->validated());

            // تحديث المستخدمين المعينين
            if ($request->has('assigned_user_ids')) {
                $task->assignedUsers()->sync($request->assigned_user_ids);
            }

            // تحديث الوسوم
            if ($request->has('tag_ids')) {
                $task->tags()->sync($request->tag_ids);
            }

            DB::commit();

            return $this->success(
                $task->load(['assignedUser', 'creator', 'assignedUsers', 'tags']),
                'تم تحديث المهمة بنجاح'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء تحديث المهمة', 500);
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return $this->success(null, 'تم حذف المهمة بنجاح');
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorize('updateStatus', $task);

        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task->update(['status' => $request->status]);

        if ($request->status === 'done') {
            $task->complete($request->user());
        }

        // تحديث تقدم المشروع
        $task->project->updateProgress();

        return $this->success($task, 'تم تحديث حالة المهمة بنجاح');
    }

    public function assignUsers(Request $request, Task $task): JsonResponse
    {
        $this->authorize('assign', $task);

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $task->assignedUsers()->syncWithoutDetaching($request->user_ids);

        return $this->success($task->load('assignedUsers'), 'تم تعيين المستخدمين للمهمة بنجاح');
    }

    public function unassignUser(Task $task, User $user): JsonResponse
    {
        $this->authorize('assign', $task);

        $task->removeAssignee($user);

        return $this->success(null, 'تم إلغاء تعيين المستخدم من المهمة بنجاح');
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->tasks as $taskData) {
                Task::where('id', $taskData['id'])
                    ->where('project_id', $project->id)
                    ->update(['order' => $taskData['order']]);
            }

            DB::commit();

            return $this->success(null, 'تم إعادة ترتيب المهام بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء إعادة الترتيب', 500);
        }
    }

    public function duplicate(Task $task): JsonResponse
    {
        $this->authorize('create', [Task::class, $task->project]);

        DB::beginTransaction();
        try {
            $newTask = $task->replicate();
            $newTask->title = $task->title . ' (نسخة)';
            $newTask->status = 'todo';
            $newTask->progress_percentage = 0;
            $newTask->completed_at = null;
            $newTask->created_by = auth()->id();
            $newTask->save();

            // نسخ checklist items
            foreach ($task->checklistItems as $item) {
                $newTask->checklistItems()->create([
                    'title' => $item->title,
                    'order' => $item->order,
                ]);
            }

            // نسخ الوسوم
            $newTask->tags()->attach($task->tags->pluck('id'));

            DB::commit();

            return $this->success(
                $newTask->load(['tags', 'checklistItems']),
                'تم نسخ المهمة بنجاح',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء نسخ المهمة', 500);
        }
    }

    public function myTasks(Request $request): JsonResponse
    {
        $query = Task::with(['project', 'assignedUser', 'tags'])
            ->assignedTo($request->user());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->orderBy('due_date')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($tasks);
    }

    public function overdueTasks(Request $request): JsonResponse
    {
        $query = Task::with(['project', 'assignedUser'])
            ->assignedTo($request->user())
            ->overdue();

        $tasks = $query->orderBy('due_date')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($tasks);
    }
}

// ============================================================================
// CommentController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Task;
use App\Models\Project;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;

class CommentController extends BaseApiController
{
    public function index(Request $request, string $type, int $id): JsonResponse
    {
        $commentable = $this->getCommentable($type, $id);
        $this->authorize('view', $commentable);

        $comments = $commentable->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginated($comments);
    }

    public function store(StoreCommentRequest $request, string $type, int $id): JsonResponse
    {
        $commentable = $this->getCommentable($type, $id);
        $this->authorize('view', $commentable);

        $comment = $commentable->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return $this->success($comment->load('user'), 'تم إضافة التعليق بنجاح', 201);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update(['content' => $request->content]);
        $comment->markAsEdited();

        return $this->success($comment->load('user'), 'تم تحديث التعليق بنجاح');
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return $this->success(null, 'تم حذف التعليق بنجاح');
    }

    private function getCommentable(string $type, int $id)
    {
        return match ($type) {
            'task' => Task::findOrFail($id),
            'project' => Project::findOrFail($id),
            default => abort(400, 'نوع غير صحيح'),
        };
    }
}

// ============================================================================
// AttachmentController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends BaseApiController
{
    public function store(Request $request, string $type, int $id): JsonResponse
    {
        $attachable = $this->getAttachable($type, $id);
        $this->authorize('view', $attachable);

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('attachments', $fileName, 'public');

        $attachment = $attachable->attachments()->create([
            'user_id' => $request->user()->id,
            'file_name' => $fileName,
            'file_original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'is_image' => str_starts_with($file->getMimeType(), 'image/'),
        ]);

        return $this->success($attachment, 'تم رفع الملف بنجاح', 201);
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        Storage::disk('public')->delete($attachment->file_path);
        
        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        $attachment->delete();

        return $this->success(null, 'تم حذف الملف بنجاح');
    }

    private function getAttachable(string $type, int $id)
    {
        return match ($type) {
            'task' => Task::findOrFail($id),
            'project' => Project::findOrFail($id),
            default => abort(400, 'نوع غير صحيح'),
        };
    }
}

// ============================================================================
// TimeEntryController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\TimeEntry;
use App\Models\Task;
use App\Http\Requests\TimeEntry\StoreTimeEntryRequest;

class TimeEntryController extends BaseApiController
{
    public function index(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $entries = $task->timeEntries()
            ->with('user')
            ->latest('started_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($entries);
    }

    public function store(StoreTimeEntryRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        // التحقق من عدم وجود سجل نشط
        $activeEntry = $task->timeEntries()
            ->where('user_id', $request->user()->id)
            ->whereNull('ended_at')
            ->first();

        if ($activeEntry) {
            return $this->error('لديك سجل وقت نشط بالفعل', 400);
        }

        $entry = $task->timeEntries()->create([
            'user_id' => $request->user()->id,
            'description' => $request->description,
            'started_at' => $request->started_at ?? now(),
        ]);

        return $this->success($entry, 'تم بدء تسجيل الوقت بنجاح', 201);
    }

    public function stop(TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('update', $timeEntry);

        if ($timeEntry->ended_at) {
            return $this->error('سجل الوقت متوقف بالفعل', 400);
        }

        $timeEntry->stop();
        
        // تحديث actual_hours في المهمة
        $timeEntry->task->updateActualHours();

        return $this->success($timeEntry, 'تم إيقاف تسجيل الوقت بنجاح');
    }

    public function destroy(TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('delete', $timeEntry);

        $task = $timeEntry->task;
        $timeEntry->delete();
        
        // تحديث actual_hours
        $task->updateActualHours();

        return $this->success(null, 'تم حذف سجل الوقت بنجاح');
    }

    public function myEntries(Request $request): JsonResponse
    {
        $query = TimeEntry::with(['task.project', 'user'])
            ->where('user_id', $request->user()->id);

        if ($request->has('from_date')) {
            $query->where('started_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('started_at', '<=', $request->to_date);
        }

        $entries = $query->latest('started_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($entries);
    }
}

// ============================================================================
// TagController
// ============================================================================
namespace App\Http\Controllers\Api;

use App\Models\Tag;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;

class TagController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Tag::with('creator');

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $tags = $query->latest()->get();

        return $this->success($tags);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = Tag::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return $this->success($tag, 'تم إنشاء الوسم بنجاح', 201);
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        $tag->update($request->validated());

        return $this->success($tag, 'تم تحديث الوسم بنجاح');
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return $this->success(null, 'تم حذف الوسم بنجاح');
    }
}

// ============================================================================
// DashboardController
// ============================================================================
namespace App\Http\Controllers\Api;

class DashboardController extends BaseApiController
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'my_tasks' => [
                'total' => $user->assignedTasks()->count(),
                'todo' => $user->assignedTasks()->where('status', 'todo')->count(),
                'in_progress' => $user->assignedTasks()->where('status', 'in_progress')->count(),
                'done' => $user->assignedTasks()->where('status', 'done')->count(),
                'overdue' => Task::assignedTo($user)->overdue()->count(),
            ],
            'my_projects' => [
                'total' => $user->projects()->count(),
                'active' => $user->projects()->where('status', 'active')->count(),
            ],
            'my_teams' => [
                'total' => $user->teams()->count(),
            ],
        ];

        return $this->success($stats);
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $activities = \App\Models\ActivityLog::with(['user', 'subject'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(20)
            ->get();

        return $this->success($activities);
    }
}
