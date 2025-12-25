<?php

/**
 * ============================================================================
 * Laravel 12 Middleware & Routes - نظام إدارة المهام
 * ============================================================================
 */

// ============================================================================
// MIDDLEWARE
// ============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// ============================================================================
// EnsureUserIsActive - التحقق من أن المستخدم نشط
// ============================================================================
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك معطل. يرجى التواصل مع الإدارة',
            ], 403);
        }

        return $next($request);
    }
}

// ============================================================================
// CheckTeamMembership - التحقق من عضوية الفريق
// ============================================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $team = $request->route('team');
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'الفريق غير موجود',
            ], 404);
        }

        $user = $request->user();

        // التحقق من الوصول
        if ($team->owner_id !== $user->id && !$team->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية الوصول لهذا الفريق',
            ], 403);
        }

        return $next($request);
    }
}

// ============================================================================
// CheckProjectMembership - التحقق من عضوية المشروع
// ============================================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');
        
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'المشروع غير موجود',
            ], 404);
        }

        $user = $request->user();

        // التحقق من الوصول
        if ($project->owner_id !== $user->id 
            && !$project->isMember($user)
            && ($project->team && !$project->team->isMember($user))) {
            
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية الوصول لهذا المشروع',
            ], 403);
        }

        return $next($request);
    }
}

// ============================================================================
// LogActivity - تسجيل النشاط
// ============================================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // تسجيل فقط إذا كان الطلب ناجح
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->logActivity($request);
        }

        return $response;
    }

    private function logActivity(Request $request): void
    {
        if (!$request->user()) {
            return;
        }

        // تسجيل الأنشطة المهمة فقط
        $method = $request->method();
        $path = $request->path();

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'subject_type' => $this->getSubjectType($path),
            'subject_id' => $this->getSubjectId($request),
            'action' => $this->getAction($method),
            'description' => $this->getDescription($method, $path),
            'properties' => [
                'url' => $request->fullUrl(),
                'method' => $method,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function getSubjectType(string $path): ?string
    {
        if (str_contains($path, 'teams')) return 'App\Models\Team';
        if (str_contains($path, 'projects')) return 'App\Models\Project';
        if (str_contains($path, 'tasks')) return 'App\Models\Task';
        return null;
    }

    private function getSubjectId(Request $request): ?int
    {
        $team = $request->route('team');
        $project = $request->route('project');
        $task = $request->route('task');

        return $team?->id ?? $project?->id ?? $task?->id;
    }

    private function getAction(string $method): string
    {
        return match($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'unknown',
        };
    }

    private function getDescription(string $method, string $path): string
    {
        $action = $this->getAction($method);
        
        if (str_contains($path, 'teams')) {
            return "قام بـ {$action} فريق";
        }
        if (str_contains($path, 'projects')) {
            return "قام بـ {$action} مشروع";
        }
        if (str_contains($path, 'tasks')) {
            return "قام بـ {$action} مهمة";
        }

        return "قام بـ {$action}";
    }
}

// ============================================================================
// CheckPermission - التحقق من الصلاحية
// ============================================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك الصلاحية للقيام بهذا الإجراء',
            ], 403);
        }

        return $next($request);
    }
}

// ============================================================================
// RateLimitApi - تحديد عدد الطلبات
// ============================================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitApi
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'عدد كبير جداً من الطلبات. يرجى المحاولة لاحقاً',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
        ]);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->user()?->id . '|' . $request->ip()
        );
    }
}

// ============================================================================
// ValidateJsonRequest - التحقق من JSON
// ============================================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateJsonRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if (!$request->isJson() && !$request->hasFile('file') && !$request->hasFile('avatar') && !$request->hasFile('logo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content-Type يجب أن يكون application/json',
                ], 400);
            }
        }

        return $next($request);
    }
}

// ============================================================================
// ROUTES
// ============================================================================

/**
 * ملف: routes/api.php
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\DashboardController;

// المسارات العامة (بدون مصادقة)
Route::prefix('v1')->group(function () {
    // المصادقة
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// المسارات المحمية (تتطلب مصادقة)
Route::prefix('v1')->middleware(['auth:sanctum', 'user.active'])->group(function () {
    
    // ==================== Auth ====================
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    // ==================== Dashboard ====================
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('recent-activity', [DashboardController::class, 'recentActivity']);
    });

    // ==================== Teams ====================
    Route::apiResource('teams', TeamController::class);
    Route::prefix('teams/{team}')->middleware('team.membership')->group(function () {
        Route::post('members', [TeamController::class, 'addMember']);
        Route::delete('members/{user}', [TeamController::class, 'removeMember']);
        Route::patch('members/{user}/role', [TeamController::class, 'updateMemberRole']);
        Route::get('members', [TeamController::class, 'members']);
    });

    // ==================== Projects ====================
    Route::apiResource('projects', ProjectController::class);
    Route::prefix('projects/{project}')->middleware('project.membership')->group(function () {
        Route::post('archive', [ProjectController::class, 'archive']);
        Route::post('unarchive', [ProjectController::class, 'unarchive']);
        Route::post('members', [ProjectController::class, 'addMember']);
        Route::delete('members/{user}', [ProjectController::class, 'removeMember']);
        Route::get('statistics', [ProjectController::class, 'statistics']);
        Route::post('update-progress', [ProjectController::class, 'updateProgress']);
    });

    // ==================== Tasks ====================
    Route::get('tasks/my-tasks', [TaskController::class, 'myTasks']);
    Route::get('tasks/overdue', [TaskController::class, 'overdueTasks']);
    
    Route::prefix('projects/{project}')->middleware('project.membership')->group(function () {
        Route::get('tasks', [TaskController::class, 'index']);
        Route::post('tasks', [TaskController::class, 'store']);
        Route::post('tasks/reorder', [TaskController::class, 'reorder']);
    });

    Route::prefix('tasks/{task}')->group(function () {
        Route::get('/', [TaskController::class, 'show']);
        Route::put('/', [TaskController::class, 'update']);
        Route::delete('/', [TaskController::class, 'destroy']);
        Route::patch('status', [TaskController::class, 'updateStatus']);
        Route::post('assign', [TaskController::class, 'assignUsers']);
        Route::delete('unassign/{user}', [TaskController::class, 'unassignUser']);
        Route::post('duplicate', [TaskController::class, 'duplicate']);
    });

    // ==================== Comments ====================
    Route::prefix('{type}/{id}/comments')->group(function () {
        Route::get('/', [CommentController::class, 'index']);
        Route::post('/', [CommentController::class, 'store']);
    });
    Route::prefix('comments/{comment}')->group(function () {
        Route::put('/', [CommentController::class, 'update']);
        Route::delete('/', [CommentController::class, 'destroy']);
    });

    // ==================== Attachments ====================
    Route::post('{type}/{id}/attachments', [AttachmentController::class, 'store']);
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

    // ==================== Time Entries ====================
    Route::prefix('tasks/{task}/time-entries')->group(function () {
        Route::get('/', [TimeEntryController::class, 'index']);
        Route::post('/', [TimeEntryController::class, 'store']);
    });
    Route::post('time-entries/{timeEntry}/stop', [TimeEntryController::class, 'stop']);
    Route::delete('time-entries/{timeEntry}', [TimeEntryController::class, 'destroy']);
    Route::get('time-entries/my-entries', [TimeEntryController::class, 'myEntries']);

    // ==================== Tags ====================
    Route::apiResource('tags', TagController::class);
});

// ============================================================================
// تسجيل Middleware في bootstrap/app.php (Laravel 12)
// ============================================================================

/**
 * ملف: bootstrap/app.php
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // تسجيل Middleware Aliases
        $middleware->alias([
            'user.active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'team.membership' => \App\Http\Middleware\CheckTeamMembership::class,
            'project.membership' => \App\Http\Middleware\CheckProjectMembership::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
            'api.rate.limit' => \App\Http\Middleware\RateLimitApi::class,
            'validate.json' => \App\Http\Middleware\ValidateJsonRequest::class,
        ]);

        // Global Middleware
        $middleware->append(\App\Http\Middleware\ValidateJsonRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// ============================================================================
// Exception Handler - معالجة الأخطاء
// ============================================================================

/**
 * ملف: app/Exceptions/Handler.php
 */

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // معالجة أخطاء API
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح. يرجى تسجيل الدخول',
                ], 401);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في البيانات المدخلة',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'العنصر المطلوب غير موجود',
                ], 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'المسار غير موجود',
                ], 404);
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطريقة غير مسموح بها',
                ], 405);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') && config('app.debug') === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في الخادم',
                ], 500);
            }
        });
    }
}

// ============================================================================
// نصائح مهمة
// ============================================================================

/**
 * 1. تأكد من تثبيت Laravel Sanctum:
 *    composer require laravel/sanctum
 *    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 *    php artisan migrate
 * 
 * 2. في .env تأكد من:
 *    SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
 * 
 * 3. في config/cors.php:
 *    'paths' => ['api/*'],
 *    'supports_credentials' => true,
 * 
 * 4. اختبار API:
 *    # تسجيل
 *    POST /api/v1/register
 *    
 *    # تسجيل الدخول
 *    POST /api/v1/login
 *    
 *    # استخدم الـ token في Headers:
 *    Authorization: Bearer {token}
 * 
 * 5. Rate Limiting:
 *    استخدم middleware('api.rate.limit:100') للحد من الطلبات
 * 
 * 6. Logging Activity:
 *    استخدم middleware('log.activity') لتسجيل الأنشطة المهمة
 */
