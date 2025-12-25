# 📚 دليل الاستخدام السريع - نظام إدارة المهام

## 🎯 ما تم تسليمه

تم إنشاء ملفات كاملة ومتكاملة لنظام إدارة المهام باستخدام Laravel 12:

1. **migrations_complete.php** - 24 مهجرة كاملة مع العلاقات
2. **models_complete.php** - 18 Model كامل مع جميع العلاقات والدوال
3. **factories_complete.php** - Factories لكل Model
4. **seeders_complete.php** - Seeders شاملة لبيانات تجريبية

---

## ✨ التعديلات المطلوبة المنفذة

### ✅ تم إزالة:
- `budget` من جدول Projects
- `currency` من جدول Projects

### ✅ تم إضافة:
- `national_id` (رقم الهوية الوطنية) في جدول Users
- جميع العلاقات بين الجداول
- دوال مساعدة في كل Model

---

## 🚀 خطوات التطبيق

### 1️⃣ نسخ المهجرات (Migrations)

افتح ملف `migrations_complete.php` وانسخ كل Migration إلى ملف منفصل:

```bash
# في مجلد Laravel الخاص بك
cd database/migrations

# أنشئ ملفات المهجرات بالترتيب:
# 0001_01_01_000000_create_users_table.php
# 2025_12_25_000001_create_roles_table.php
# 2025_12_25_000002_create_permissions_table.php
# ... وهكذا
```

**مهم جداً:** 
- احذف الملف القديم: `2025_12_22_192500_add_team_id_to_tasks_table.php`
- تأكد من ترقيم الملفات بالترتيب الصحيح

### 2️⃣ نسخ Models

افتح ملف `models_complete.php` وانسخ كل Model إلى ملف منفصل:

```bash
# في مجلد Laravel الخاص بك
cd app/Models

# أنشئ الملفات:
# User.php
# Team.php
# Project.php
# Task.php
# ... إلخ
```

### 3️⃣ نسخ Factories

افتح ملف `factories_complete.php`:

```bash
cd database/factories

# أنشئ الملفات:
# UserFactory.php
# TeamFactory.php
# ProjectFactory.php
# ... إلخ
```

### 4️⃣ نسخ Seeders

افتح ملف `seeders_complete.php`:

```bash
cd database/seeders

# أنشئ الملفات:
# DatabaseSeeder.php
# RolesAndPermissionsSeeder.php
# UsersSeeder.php
# ... إلخ
```

---

## 🔧 تشغيل التطبيق

### الخطوة 1: تشغيل المهجرات

```bash
# امسح قاعدة البيانات (إذا كانت موجودة) وأعد إنشاءها
php artisan migrate:fresh

# أو تشغيل المهجرات فقط
php artisan migrate
```

### الخطوة 2: تشغيل Seeders

```bash
# تشغيل جميع الـ Seeders
php artisan db:seed

# أو تشغيل seeder محدد
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 📊 البيانات التجريبية المُنشأة

بعد تشغيل Seeders، ستجد:

### 👥 المستخدمين:
- **المدير الرئيسي:**
  - البريد: `admin@taskmanager.com`
  - كلمة المرور: `password`
  - رقم الهوية: `1234567890`

- **5 مستخدمين تجريبيين:**
  - `ahmed@example.com`
  - `sarah@example.com`
  - `mohammed@example.com`
  - `fatima@example.com`
  - `khaled@example.com`
  - كلمة المرور: `password`

- **15 مستخدم إضافي عشوائي**

### 🏢 الفرق:
- فريق التطوير
- فريق التصميم
- 3 فرق إضافية

### 📁 المشاريع:
- 2-4 مشاريع لكل فريق
- مع أعضاء ومعالم رئيسية (Milestones)

### ✅ المهام:
- 5-15 مهمة لكل مشروع
- بعضها مكتمل، في التنفيذ، أو قيد الانتظار
- مع Tags وChecklist Items وTime Entries

### 💬 التعليقات والمرفقات:
- تعليقات على المهام والمشاريع
- مرفقات على المهام

---

## 🔐 نظام الصلاحيات

### الأدوار (Roles):
1. **مدير النظام** (super-admin) - صلاحيات كاملة
2. **مالك الفريق** (team-owner)
3. **مدير الفريق** (team-admin)
4. **عضو فريق** (team-member)
5. **مدير مشروع** (project-manager)
6. **عضو مشروع** (project-member)
7. **مراقب** (viewer)

### الصلاحيات (Permissions):
- صلاحيات الفرق (view, create, edit, delete, manage-members)
- صلاحيات المشاريع (view, create, edit, delete, archive, manage-members)
- صلاحيات المهام (view, create, edit, delete, assign, change-status)
- صلاحيات التعليقات (view, create, edit, delete)
- صلاحيات المرفقات (upload, delete)
- صلاحيات تتبع الوقت (log, view, edit, delete)
- صلاحيات التقارير (view, create, export)
- صلاحيات المستخدمين (view, manage, manage-roles)

---

## 💡 أمثلة على الاستخدام

### استخدام Models:

```php
// الحصول على مهام المستخدم
$user = User::find(1);
$tasks = $user->assignedTasks;

// إنشاء مهمة جديدة
$project = Project::find(1);
$task = $project->tasks()->create([
    'title' => 'مهمة جديدة',
    'status' => 'todo',
    'priority' => 'medium',
    'assigned_to' => $user->id,
    'created_by' => auth()->id(),
]);

// إضافة تعليق
$task->comments()->create([
    'user_id' => auth()->id(),
    'content' => 'هذا تعليق تجريبي',
]);

// تعيين مستخدم للمهمة
$task->addAssignee($user);

// إكمال المهمة
$task->complete($user);

// حساب التقدم
$project->updateProgress();
```

### التحقق من الصلاحيات:

```php
// التحقق من دور المستخدم
if ($user->hasRole('team-admin', $team)) {
    // المستخدم مدير فريق
}

// التحقق من صلاحية معينة
if ($user->hasPermission('edit-tasks', $project)) {
    // المستخدم يملك صلاحية تعديل المهام
}

// إعطاء دور للمستخدم
$user->assignRole('project-manager', $project);

// إزالة دور
$user->removeRole('project-manager', $project);
```

### استخدام Scopes:

```php
// المهام المتأخرة
$overdueTasks = Task::overdue()->get();

// المهام المستحقة قريباً
$dueSoonTasks = Task::dueSoon(7)->get();

// المهام المعينة للمستخدم
$myTasks = Task::assignedTo($user)->get();

// المشاريع النشطة
$activeProjects = Project::active()->get();

// الفرق النشطة
$activeTeams = Team::active()->get();
```

---

## 📝 ملاحظات مهمة

### 1. التعديل على User Model الموجود:

إذا كان لديك User Model موجود، **لا تستبدله كاملاً**. بدلاً من ذلك:
- أضف العلاقات الجديدة فقط
- أضف الـ Methods الجديدة
- تأكد من وجود الـ fillable و casts الجديدة

### 2. الـ Observers:

لتفعيل تسجيل الأنشطة تلقائياً، أضف في `AppServiceProvider`:

```php
use App\Models\Task;
use App\Models\Project;
use App\Observers\TaskObserver;
use App\Observers\ProjectObserver;

public function boot(): void
{
    Task::observe(TaskObserver::class);
    Project::observe(ProjectObserver::class);
}
```

### 3. Sanctum للـ API:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 4. Storage Link:

```bash
php artisan storage:link
```

---

## 🧪 اختبار النظام

### تسجيل الدخول:

```
البريد الإلكتروني: admin@taskmanager.com
كلمة المرور: password
```

### اختبار الصلاحيات:

```php
$admin = User::where('email', 'admin@taskmanager.com')->first();
dd($admin->hasRole('super-admin')); // true
dd($admin->hasPermission('delete-teams')); // true
```

### اختبار العلاقات:

```php
$team = Team::first();
dd($team->members->count()); // عدد الأعضاء
dd($team->projects->count()); // عدد المشاريع

$project = Project::first();
dd($project->tasks->count()); // عدد المهام
dd($project->calculateProgress()); // نسبة التقدم
```

---

## 🎨 الخطوات التالية

### 1. إنشاء Controllers:
راجع ملف `controllers_resources_examples.php` من الملفات السابقة

### 2. إنشاء API Routes:
```php
Route::middleware(['auth:sanctum'])->prefix('api/v1')->group(function () {
    Route::apiResource('teams', TeamController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    // ... المزيد
});
```

### 3. إنشاء Policies:
```bash
php artisan make:policy TeamPolicy --model=Team
php artisan make:policy ProjectPolicy --model=Project
php artisan make:policy TaskPolicy --model=Task
```

### 4. إنشاء Tests:
```bash
php artisan make:test TaskApiTest
php artisan make:test ProjectApiTest
```

---

## 🐛 استكشاف الأخطاء

### خطأ: "Class not found"
```bash
composer dump-autoload
```

### خطأ في المهجرات:
```bash
php artisan migrate:rollback
php artisan migrate
```

### مشكلة في الصلاحيات:
تأكد من تشغيل `RolesAndPermissionsSeeder` أولاً

### مشكلة في العلاقات:
تأكد من أن الـ foreign keys موجودة في الجداول

---

## 📚 موارد إضافية

### الملفات السابقة المتوفرة:
1. `database_analysis_ar.md` - التحليل الشامل
2. `ERD_diagram.md` - مخطط العلاقات
3. `additional_migrations.php` - مهجرات إضافية متقدمة
4. `controllers_resources_examples.php` - أمثلة Controllers و Resources
5. `models_seeders_examples.php` - أمثلة إضافية

### الوثائق:
- Laravel 12: https://laravel.com/docs/12.x
- Laravel Sanctum: https://laravel.com/docs/12.x/sanctum
- Laravel Policies: https://laravel.com/docs/12.x/authorization

---

## ✅ Checklist التطبيق

- [ ] نسخ جميع المهجرات
- [ ] حذف المهجرة المكررة (team_id)
- [ ] نسخ جميع Models
- [ ] نسخ جميع Factories
- [ ] نسخ جميع Seeders
- [ ] تشغيل `php artisan migrate:fresh`
- [ ] تشغيل `php artisan db:seed`
- [ ] تسجيل الدخول بـ admin@taskmanager.com
- [ ] اختبار إنشاء فريق
- [ ] اختبار إنشاء مشروع
- [ ] اختبار إنشاء مهمة
- [ ] اختبار الصلاحيات

---

## 🎉 تهانينا!

الآن لديك نظام إدارة مهام متكامل مع:
- ✅ 24 جدول قاعدة بيانات
- ✅ 18 Model مع علاقات كاملة
- ✅ نظام صلاحيات شامل
- ✅ بيانات تجريبية غنية
- ✅ Factories جاهزة للاختبار
- ✅ دوال مساعدة في كل Model

**حظاً موفقاً في مشروعك! 🚀**

---

**تاريخ الإنشاء:** 25 ديسمبر 2025  
**الإصدار:** 1.0  
**Laravel:** 12.x  
**PHP:** 8.3+  
**MySQL:** 8.0+
