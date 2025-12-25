# 📚 دليل التنصيب الشامل - Controllers, Requests, Policies & Middleware

## 🎯 ما تم تسليمه

تم إنشاء ملفات متكاملة لـ:

1. **controllers_complete.php** - 10 Controllers كاملة
2. **requests_complete.php** - 20+ Form Request
3. **policies_complete.php** - 7 Policies
4. **middleware_routes_complete.php** - 7 Middleware + Routes كاملة

---

## 📦 المحتويات التفصيلية

### 1️⃣ Controllers (10 Controllers)

| Controller | الوصف |
|-----------|-------|
| **BaseApiController** | الكلاس الأساسي مع دوال مساعدة |
| **AuthController** | التسجيل، تسجيل الدخول، الملف الشخصي |
| **TeamController** | إدارة الفرق والأعضاء |
| **ProjectController** | إدارة المشاريع |
| **TaskController** | إدارة المهام |
| **CommentController** | التعليقات |
| **AttachmentController** | المرفقات |
| **TimeEntryController** | تتبع الوقت |
| **TagController** | الوسوم |
| **DashboardController** | الإحصائيات |

### 2️⃣ Form Requests (20+ Request)

#### Auth Requests:
- `RegisterRequest`
- `LoginRequest`

#### Team Requests:
- `StoreTeamRequest`
- `UpdateTeamRequest`

#### Project Requests:
- `StoreProjectRequest`
- `UpdateProjectRequest`

#### Task Requests:
- `StoreTaskRequest`
- `UpdateTaskRequest`

#### Comment Requests:
- `StoreCommentRequest`
- `UpdateCommentRequest`

#### TimeEntry Requests:
- `StoreTimeEntryRequest`

#### Tag Requests:
- `StoreTagRequest`
- `UpdateTagRequest`

#### Milestone Requests:
- `StoreMilestoneRequest`
- `UpdateMilestoneRequest`

#### TaskChecklistItem Requests:
- `StoreTaskChecklistItemRequest`
- `UpdateTaskChecklistItemRequest`

### 3️⃣ Policies (7 Policies)

- `TeamPolicy` - صلاحيات الفرق
- `ProjectPolicy` - صلاحيات المشاريع
- `TaskPolicy` - صلاحيات المهام
- `CommentPolicy` - صلاحيات التعليقات
- `AttachmentPolicy` - صلاحيات المرفقات
- `TimeEntryPolicy` - صلاحيات تتبع الوقت
- `TagPolicy` - صلاحيات الوسوم

### 4️⃣ Middleware (7 Middleware)

- `EnsureUserIsActive` - التحقق من المستخدم النشط
- `CheckTeamMembership` - التحقق من عضوية الفريق
- `CheckProjectMembership` - التحقق من عضوية المشروع
- `LogActivity` - تسجيل الأنشطة
- `CheckPermission` - التحقق من الصلاحيات
- `RateLimitApi` - تحديد عدد الطلبات
- `ValidateJsonRequest` - التحقق من JSON

---

## 🚀 خطوات التنصيب

### الخطوة 1: نسخ Controllers

```bash
cd app/Http/Controllers/Api
```

أنشئ الملفات التالية من `controllers_complete.php`:

```
BaseApiController.php
AuthController.php
TeamController.php
ProjectController.php
TaskController.php
CommentController.php
AttachmentController.php
TimeEntryController.php
TagController.php
DashboardController.php
```

### الخطوة 2: نسخ Form Requests

```bash
# إنشاء المجلدات
mkdir -p app/Http/Requests/Auth
mkdir -p app/Http/Requests/Team
mkdir -p app/Http/Requests/Project
mkdir -p app/Http/Requests/Task
mkdir -p app/Http/Requests/Comment
mkdir -p app/Http/Requests/TimeEntry
mkdir -p app/Http/Requests/Tag
mkdir -p app/Http/Requests/Milestone
mkdir -p app/Http/Requests/TaskChecklistItem
```

انسخ كل Request من `requests_complete.php` إلى المجلد المناسب.

### الخطوة 3: نسخ Policies

```bash
cd app/Policies
```

أنشئ الملفات من `policies_complete.php`:

```
TeamPolicy.php
ProjectPolicy.php
TaskPolicy.php
CommentPolicy.php
AttachmentPolicy.php
TimeEntryPolicy.php
TagPolicy.php
```

ثم سجّل الـ Policies في `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Team::class => TeamPolicy::class,
    Project::class => ProjectPolicy::class,
    Task::class => TaskPolicy::class,
    Comment::class => CommentPolicy::class,
    Attachment::class => AttachmentPolicy::class,
    TimeEntry::class => TimeEntryPolicy::class,
    Tag::class => TagPolicy::class,
];
```

### الخطوة 4: نسخ Middleware

```bash
cd app/Http/Middleware
```

أنشئ الملفات من `middleware_routes_complete.php`:

```
EnsureUserIsActive.php
CheckTeamMembership.php
CheckProjectMembership.php
LogActivity.php
CheckPermission.php
RateLimitApi.php
ValidateJsonRequest.php
```

### الخطوة 5: تسجيل Middleware في Laravel 12

في ملف `bootstrap/app.php`:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
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
    ->create();
```

### الخطوة 6: نسخ Routes

استبدل محتوى `routes/api.php` بالمحتوى من `middleware_routes_complete.php`.

### الخطوة 7: نسخ Exception Handler

استبدل محتوى `app/Exceptions/Handler.php` بالمحتوى من `middleware_routes_complete.php`.

### الخطوة 8: تثبيت Laravel Sanctum

```bash
composer require laravel/sanctum

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

php artisan migrate
```

### الخطوة 9: إعداد Sanctum

في `.env`:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:3000
```

في `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,
```

### الخطوة 10: Storage Link

```bash
php artisan storage:link
```

---

## 🧪 اختبار API

### 1. التسجيل

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "national_id": "1234567890",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 2. تسجيل الدخول

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@taskmanager.com",
    "password": "password"
  }'
```

الرد سيحتوي على:
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

### 3. استخدام الـ Token

احفظ الـ token واستخدمه في جميع الطلبات:

```bash
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx" \
  -H "Content-Type: application/json"
```

### 4. إنشاء فريق

```bash
curl -X POST http://localhost:8000/api/v1/teams \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "فريق التطوير",
    "description": "فريق تطوير التطبيقات",
    "color": "#3B82F6"
  }'
```

### 5. إنشاء مشروع

```bash
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "تطبيق إدارة المهام",
    "description": "مشروع نظام إدارة المهام",
    "team_id": 1,
    "status": "active",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31"
  }'
```

### 6. إنشاء مهمة

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/tasks \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "تصميم واجهة المستخدم",
    "description": "تصميم واجهة المستخدم للصفحة الرئيسية",
    "priority": "high",
    "status": "todo",
    "due_date": "2025-02-01"
  }'
```

---

## 📝 أمثلة على استخدام API

### إدارة الفرق

```bash
# عرض جميع الفرق
GET /api/v1/teams

# عرض فريق محدد
GET /api/v1/teams/1

# تحديث فريق
PUT /api/v1/teams/1

# حذف فريق
DELETE /api/v1/teams/1

# إضافة عضو
POST /api/v1/teams/1/members
Body: {"user_id": 2, "role": "admin"}

# إزالة عضو
DELETE /api/v1/teams/1/members/2

# تحديث دور عضو
PATCH /api/v1/teams/1/members/2/role
Body: {"role": "member"}

# عرض الأعضاء
GET /api/v1/teams/1/members
```

### إدارة المشاريع

```bash
# عرض جميع المشاريع
GET /api/v1/projects
# مع فلاتر: ?team_id=1&status=active&search=تطبيق

# عرض مشروع محدد
GET /api/v1/projects/1

# تحديث مشروع
PUT /api/v1/projects/1

# حذف مشروع
DELETE /api/v1/projects/1

# أرشفة مشروع
POST /api/v1/projects/1/archive

# إلغاء الأرشفة
POST /api/v1/projects/1/unarchive

# إحصائيات المشروع
GET /api/v1/projects/1/statistics

# تحديث التقدم
POST /api/v1/projects/1/update-progress
```

### إدارة المهام

```bash
# عرض مهام مشروع
GET /api/v1/projects/1/tasks
# مع فلاتر: ?status=todo&priority=high&search=تصميم

# مهامي
GET /api/v1/tasks/my-tasks

# المهام المتأخرة
GET /api/v1/tasks/overdue

# عرض مهمة محددة
GET /api/v1/tasks/1

# تحديث مهمة
PUT /api/v1/tasks/1

# حذف مهمة
DELETE /api/v1/tasks/1

# تحديث حالة المهمة
PATCH /api/v1/tasks/1/status
Body: {"status": "done"}

# تعيين مستخدمين
POST /api/v1/tasks/1/assign
Body: {"user_ids": [2, 3, 4]}

# إلغاء تعيين مستخدم
DELETE /api/v1/tasks/1/unassign/2

# نسخ مهمة
POST /api/v1/tasks/1/duplicate

# إعادة ترتيب المهام
POST /api/v1/projects/1/tasks/reorder
Body: {
  "tasks": [
    {"id": 1, "order": 0},
    {"id": 2, "order": 1},
    {"id": 3, "order": 2}
  ]
}
```

### التعليقات

```bash
# عرض تعليقات مهمة
GET /api/v1/task/1/comments

# إضافة تعليق
POST /api/v1/task/1/comments
Body: {"content": "تعليق جديد", "parent_id": null}

# تحديث تعليق
PUT /api/v1/comments/1
Body: {"content": "تعليق محدث"}

# حذف تعليق
DELETE /api/v1/comments/1
```

### المرفقات

```bash
# رفع ملف
POST /api/v1/task/1/attachments
Content-Type: multipart/form-data
Body: file={FILE}

# حذف مرفق
DELETE /api/v1/attachments/1
```

### تتبع الوقت

```bash
# بدء تسجيل الوقت
POST /api/v1/tasks/1/time-entries
Body: {"description": "العمل على التصميم"}

# إيقاف تسجيل الوقت
POST /api/v1/time-entries/1/stop

# حذف سجل
DELETE /api/v1/time-entries/1

# سجلاتي
GET /api/v1/time-entries/my-entries
# مع فلاتر: ?from_date=2025-01-01&to_date=2025-01-31
```

### الوسوم

```bash
# عرض جميع الوسوم
GET /api/v1/tags

# إنشاء وسم
POST /api/v1/tags
Body: {"name": "عاجل", "color": "#EF4444"}

# تحديث وسم
PUT /api/v1/tags/1

# حذف وسم
DELETE /api/v1/tags/1
```

### Dashboard

```bash
# الإحصائيات
GET /api/v1/dashboard/stats

# الأنشطة الأخيرة
GET /api/v1/dashboard/recent-activity
```

---

## 🔒 الصلاحيات والأمان

### كيفية عمل Policies

كل عملية محمية بـ Policy:

```php
// في Controller
$this->authorize('view', $team);
$this->authorize('update', $project);
$this->authorize('delete', $task);
```

### كيفية عمل Middleware

```php
// التحقق من المستخدم النشط
->middleware('user.active')

// التحقق من عضوية الفريق
->middleware('team.membership')

// التحقق من عضوية المشروع
->middleware('project.membership')

// تحديد عدد الطلبات
->middleware('api.rate.limit:100')

// التحقق من صلاحية محددة
->middleware('check.permission:edit-tasks')

// تسجيل الأنشطة
->middleware('log.activity')
```

---

## 🎨 هيكل الردود (Response Structure)

### نجاح (Success)

```json
{
  "success": true,
  "message": "تمت العملية بنجاح",
  "data": {
    // البيانات
  }
}
```

### نجاح مع Pagination

```json
{
  "success": true,
  "message": "تمت العملية بنجاح",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150,
    "from": 1,
    "to": 15
  }
}
```

### خطأ (Error)

```json
{
  "success": false,
  "message": "حدث خطأ",
  "errors": {
    "field": ["رسالة الخطأ"]
  }
}
```

---

## ⚠️ أكواد الحالة (Status Codes)

| الكود | المعنى |
|------|--------|
| 200 | نجاح (GET, PUT, PATCH) |
| 201 | تم الإنشاء (POST) |
| 204 | نجاح بدون محتوى (DELETE) |
| 400 | طلب خاطئ |
| 401 | غير مصرح |
| 403 | ممنوع |
| 404 | غير موجود |
| 422 | خطأ في التحقق |
| 429 | عدد كبير من الطلبات |
| 500 | خطأ في الخادم |

---

## 🔧 استكشاف الأخطاء

### خطأ: "Unauthenticated"

```bash
# تأكد من إرسال الـ token
Authorization: Bearer {token}
```

### خطأ: "CSRF token mismatch"

```bash
# تأكد من إعدادات CORS في config/cors.php
'supports_credentials' => true
```

### خطأ: "Route not found"

```bash
# تأكد من:
1. نسخ routes/api.php بشكل صحيح
2. تنظيف الـ cache: php artisan route:clear
```

### خطأ: "Class not found"

```bash
# قم بتحديث autoload
composer dump-autoload
```

### خطأ: "Policy not found"

```bash
# تأكد من تسجيل Policies في AuthServiceProvider
```

---

## 💡 نصائح مهمة

### 1. استخدام Postman

أنشئ Collection في Postman:
- احفظ الـ token في Environment
- أضف جميع Endpoints
- استخدم Tests للتحقق من الردود

### 2. Rate Limiting

```php
// تحديد 60 طلب في الدقيقة
Route::middleware('api.rate.limit:60')->group(function () {
    // Routes
});
```

### 3. Caching

```php
// في Controller
$teams = Cache::remember('user-teams-' . $user->id, 3600, function () use ($user) {
    return $user->teams()->with('owner')->get();
});
```

### 4. Validation Rules

استخدم Custom Rules عند الحاجة:

```php
php artisan make:rule ValidNationalId
```

### 5. API Versioning

```php
// api/v1/...
// api/v2/...
```

---

## 📚 ملفات إضافية

لا تنسى الملفات السابقة:
- `migrations_complete.php`
- `models_complete.php`
- `factories_complete.php`
- `seeders_complete.php`
- `QUICK_START_GUIDE.md`

---

## ✅ Checklist التنصيب

- [ ] نسخ جميع Controllers
- [ ] نسخ جميع Requests
- [ ] نسخ جميع Policies
- [ ] نسخ جميع Middleware
- [ ] تسجيل Middleware في bootstrap/app.php
- [ ] تسجيل Policies في AuthServiceProvider
- [ ] نسخ Routes في routes/api.php
- [ ] نسخ Exception Handler
- [ ] تثبيت Laravel Sanctum
- [ ] إعداد Sanctum في .env
- [ ] تشغيل php artisan storage:link
- [ ] اختبار التسجيل
- [ ] اختبار تسجيل الدخول
- [ ] اختبار إنشاء فريق
- [ ] اختبار إنشاء مشروع
- [ ] اختبار إنشاء مهمة

---

## 🎉 النتيجة النهائية

الآن لديك:
- ✅ 10 Controllers كاملة
- ✅ 20+ Form Requests
- ✅ 7 Policies شاملة
- ✅ 7 Middleware للحماية
- ✅ Routes API كاملة
- ✅ Exception Handling احترافي
- ✅ نظام مصادقة بـ Sanctum
- ✅ Rate Limiting
- ✅ Activity Logging

**كل شيء جاهز للعمل! 🚀**

---

**تاريخ الإنشاء:** 25 ديسمبر 2025  
**الإصدار:** 2.0  
**Laravel:** 12.x  
**PHP:** 8.3+
