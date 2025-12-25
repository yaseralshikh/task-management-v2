<?php

/**
 * =============================================================================
 * أمثلة على Controllers & API Resources & Best Practices
 * =============================================================================
 */

// =============================================================================
// 1. CONTROLLERS
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    /**
     * عرض قائمة المهام
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        // التحقق من الصلاحية
        $this->authorize('view', $project);

        $query = $project->tasks()
            ->with(['assignedUser', 'creator', 'tags', 'assignedUsers']);

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

        // البحث
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tasks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($tasks),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * عرض مهمة محددة
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load([
            'assignedUser',
            'creator',
            'project',
            'team',
            'assignedUsers',
            'checklistItems',
            'tags',
            'comments.user',
            'attachments',
            'timeEntries.user',
            'subtasks',
            'dependencies',
        ]);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * إنشاء مهمة جديدة
     */
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $task = $project->tasks()->create([
            ...$request->validated(),
            'created_by' => auth()->id(),
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

        $task->load(['assignedUser', 'creator', 'tags', 'checklistItems']);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المهمة بنجاح',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * تحديث مهمة
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        // تحديث المستخدمين المعينين
        if ($request->has('assigned_user_ids')) {
            $task->assignedUsers()->sync($request->assigned_user_ids);
        }

        // تحديث الوسوم
        if ($request->has('tag_ids')) {
            $task->tags()->sync($request->tag_ids);
        }

        $task->load(['assignedUser', 'creator', 'tags', 'assignedUsers']);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المهمة بنجاح',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * حذف مهمة
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المهمة بنجاح',
        ]);
    }

    /**
     * تغيير حالة المهمة
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorize('changeStatus', $task);

        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task->update(['status' => $request->status]);

        if ($request->status === 'done') {
            $task->complete(auth()->user());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة المهمة بنجاح',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * تعيين مستخدمين للمهمة
     */
    public function assignUsers(Request $request, Task $task): JsonResponse
    {
        $this->authorize('assign', $task);

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $task->assignedUsers()->syncWithoutDetaching($request->user_ids);

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين المستخدمين للمهمة بنجاح',
            'data' => new TaskResource($task->load('assignedUsers')),
        ]);
    }

    /**
     * إلغاء تعيين مستخدم من المهمة
     */
    public function unassignUser(Task $task, User $user): JsonResponse
    {
        $this->authorize('assign', $task);

        $task->assignedUsers()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تعيين المستخدم من المهمة بنجاح',
        ]);
    }

    /**
     * إعادة ترتيب المهام
     */
    public function reorder(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->tasks as $taskData) {
            Task::where('id', $taskData['id'])
                ->update(['order' => $taskData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة ترتيب المهام بنجاح',
        ]);
    }

    /**
     * نسخ مهمة
     */
    public function duplicate(Task $task): JsonResponse
    {
        $this->authorize('create', [Task::class, $task->project]);

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

        return response()->json([
            'success' => true,
            'message' => 'تم نسخ المهمة بنجاح',
            'data' => new TaskResource($newTask->load(['tags', 'checklistItems'])),
        ], 201);
    }
}

// =============================================================================
// 2. FORM REQUESTS
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // التحقق في الـ Controller
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'checklist_items' => 'nullable|array',
            'checklist_items.*.title' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان المهمة مطلوب',
            'title.max' => 'عنوان المهمة يجب ألا يتجاوز 255 حرف',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ البداية',
            'assigned_to.exists' => 'المستخدم المعين غير موجود',
            'parent_task_id.exists' => 'المهمة الأب غير موجودة',
        ];
    }
}

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,done',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'blocking_reason' => 'nullable|string',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }
}

// =============================================================================
// 3. API RESOURCES
// =============================================================================

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'progress_percentage' => $this->progress_percentage,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'is_overdue' => $this->is_overdue,
            'is_completed' => $this->is_completed,
            'blocking_reason' => $this->blocking_reason,
            'is_recurring' => $this->is_recurring,
            'recurrence_pattern' => $this->recurrence_pattern,
            'order' => $this->order,
            
            // العلاقات
            'project' => new ProjectResource($this->whenLoaded('project')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'assigned_users' => UserResource::collection($this->whenLoaded('assignedUsers')),
            'parent_task' => new TaskResource($this->whenLoaded('parent')),
            'subtasks' => TaskResource::collection($this->whenLoaded('subtasks')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'checklist_items' => ChecklistItemResource::collection($this->whenLoaded('checklistItems')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'time_entries' => TimeEntryResource::collection($this->whenLoaded('timeEntries')),
            
            // التواريخ
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function getStatusLabel(): string
    {
        return match($this->status) {
            'todo' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'done' => 'مكتملة',
            default => $this->status,
        };
    }

    private function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية',
            'urgent' => 'عاجلة',
            default => $this->priority,
        };
    }
}

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'job_title' => $this->job_title,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}

// =============================================================================
// 4. MIDDLEWARE
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckProjectAccess
{
    public function handle(Request $request, Closure $next)
    {
        $project = $request->route('project');
        
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'المشروع غير موجود',
            ], 404);
        }

        $user = auth()->user();

        // التحقق من الوصول
        if (!$project->members()->where('user_id', $user->id)->exists() 
            && $project->owner_id !== $user->id
            && !$user->is_owner) {
            
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية الوصول لهذا المشروع',
            ], 403);
        }

        return $next($request);
    }
}

// =============================================================================
// 5. ROUTES
// =============================================================================

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('api/v1')->group(function () {
    
    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);
    
    // Projects
    Route::apiResource('teams.projects', ProjectController::class)->shallow();
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive']);
    Route::post('projects/{project}/unarchive', [ProjectController::class, 'unarchive']);
    Route::post('projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);
    
    // Tasks
    Route::apiResource('projects.tasks', TaskController::class)->shallow();
    Route::post('tasks/{task}/duplicate', [TaskController::class, 'duplicate']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::post('tasks/{task}/assign', [TaskController::class, 'assignUsers']);
    Route::delete('tasks/{task}/unassign/{user}', [TaskController::class, 'unassignUser']);
    Route::post('projects/{project}/tasks/reorder', [TaskController::class, 'reorder']);
    
    // Comments
    Route::apiResource('tasks.comments', CommentController::class)->shallow();
    Route::apiResource('projects.comments', CommentController::class)->shallow();
    
    // Attachments
    Route::post('tasks/{task}/attachments', [AttachmentController::class, 'store']);
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);
    
    // Time Entries
    Route::apiResource('tasks.time-entries', TimeEntryController::class)->shallow();
    Route::post('time-entries/{timeEntry}/stop', [TimeEntryController::class, 'stop']);
    
    // Tags
    Route::apiResource('tags', TagController::class);
    
    // Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
    Route::get('dashboard/my-tasks', [DashboardController::class, 'myTasks']);
    
    // Search
    Route::get('search', [SearchController::class, 'search']);
    
    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// =============================================================================
// 6. TESTS
// =============================================================================

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    }

    /** @test */
    public function user_can_create_task()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => 'todo',
                'priority' => 'medium',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'status', 'priority'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
        ]);
    }

    /** @test */
    public function user_can_update_task()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Updated Task',
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function user_cannot_update_task_without_permission()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Updated Task',
            ]);

        $response->assertStatus(403);
    }
}

// =============================================================================
// 7. BEST PRACTICES & TIPS
// =============================================================================

/**
 * نصائح مهمة للتطوير:
 * 
 * 1. استخدم Laravel Sanctum للـ API Authentication
 *    composer require laravel/sanctum
 *    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 * 
 * 2. استخدم API Resources بدلاً من الإرجاع المباشر للـ Models
 *    هذا يمنحك تحكم كامل في البيانات المرسلة
 * 
 * 3. استخدم Form Requests للـ Validation
 *    php artisan make:request StoreTaskRequest
 * 
 * 4. استخدم Policies للصلاحيات
 *    php artisan make:policy TaskPolicy --model=Task
 * 
 * 5. استخدم Observers للأحداث
 *    php artisan make:observer TaskObserver --model=Task
 * 
 * 6. استخدم Events & Listeners للإشعارات
 *    php artisan make:event TaskAssigned
 *    php artisan make:listener SendTaskAssignedNotification
 * 
 * 7. استخدم Jobs للمهام الثقيلة
 *    php artisan make:job ProcessProjectReport
 * 
 * 8. استخدم Queues
 *    في .env ضع QUEUE_CONNECTION=redis
 * 
 * 9. استخدم Caching بذكاء
 *    Cache::remember('user-stats-' . $user->id, 3600, function() { ... });
 * 
 * 10. اكتب Tests شاملة
 *     php artisan make:test TaskApiTest
 * 
 * 11. استخدم Laravel Telescope للـ Debugging
 *     composer require laravel/telescope --dev
 * 
 * 12. استخدم Laravel Pint لتنسيق الكود
 *     ./vendor/bin/pint
 * 
 * 13. راجع N+1 queries باستمرار
 *     استخدم Debugbar أو Telescope لاكتشافها
 * 
 * 14. استخدم Database Transactions
 *     DB::transaction(function () { ... });
 * 
 * 15. استخدم Rate Limiting
 *     في routes/api.php: ->middleware('throttle:60,1')
 */
