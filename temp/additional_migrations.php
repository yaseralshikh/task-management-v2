<?php

/**
 * =============================================================================
 * المهجرات الإضافية المطلوبة لنظام إدارة المهام
 * =============================================================================
 * 
 * هذا الملف يحتوي على جميع المهجرات الإضافية المطلوبة
 * يمكن فصلها إلى ملفات منفصلة حسب الحاجة
 * 
 * الترتيب مهم! يجب تنفيذها بنفس الترتيب المذكور
 */

// =============================================================================
// 1. إصلاح جدول tasks (حذف التكرار)
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: 2025_12_24_000001_fix_tasks_table_team_id_duplication
 * 
 * ⚠️ هذه المهجرة يجب تنفيذها أولاً لحذف التكرار
 */
return new class extends Migration
{
    public function up(): void
    {
        // لا داعي لفعل شيء - team_id موجود بالفعل في create_tasks_table
        // فقط احذف ملف: 2025_12_22_192500_add_team_id_to_tasks_table.php
    }

    public function down(): void
    {
        // لا شيء
    }
};

// =============================================================================
// 2. تحديث جدول users
// =============================================================================

/**
 * Migration: 2025_12_24_000002_enhance_users_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->string('job_title')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('job_title');
            $table->string('timezone', 50)->default('Asia/Riyadh')->after('bio');
            $table->string('language', 5)->default('ar')->after('timezone');
            $table->string('date_format', 20)->default('Y-m-d')->after('language');
            $table->string('time_format', 20)->default('H:i')->after('date_format');
            $table->tinyInteger('week_starts_on')->default(6)->after('time_format'); // السبت = 6
            $table->enum('theme', ['light', 'dark', 'auto'])->default('auto')->after('week_starts_on');
            $table->boolean('is_active')->default(true)->after('is_owner');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'phone', 'job_title', 'bio', 'timezone', 
                'language', 'date_format', 'time_format', 'week_starts_on',
                'theme', 'is_active', 'last_login_at'
            ]);
        });
    }
};

// =============================================================================
// 3. تحديث جدول teams
// =============================================================================

/**
 * Migration: 2025_12_24_000003_enhance_teams_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('logo')->nullable()->after('description');
            $table->string('color', 7)->default('#3B82F6')->after('logo'); // hex color
            $table->boolean('is_active')->default(true)->after('color');
            $table->integer('max_members')->nullable()->after('is_active');
            $table->json('settings')->nullable()->after('max_members');
            
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['slug', 'logo', 'color', 'is_active', 'max_members', 'settings']);
        });
    }
};

// =============================================================================
// 4. تحديث جدول projects
// =============================================================================

/**
 * Migration: 2025_12_24_000004_enhance_projects_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('color', 7)->default('#10B981')->after('slug');
            $table->decimal('budget', 15, 2)->nullable()->after('end_date');
            $table->string('currency', 3)->default('SAR')->after('budget');
            $table->decimal('progress_percentage', 5, 2)->default(0)->after('currency');
            $table->boolean('is_archived')->default(false)->after('status');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete()->after('archived_at');
            
            $table->index('is_archived');
            $table->index(['status', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn([
                'slug', 'color', 'budget', 'currency', 'progress_percentage',
                'is_archived', 'archived_at', 'archived_by'
            ]);
        });
    }
};

// =============================================================================
// 5. تحديث جدول tasks
// =============================================================================

/**
 * Migration: 2025_12_24_000005_enhance_tasks_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->after('assigned_to')->constrained('tasks')->nullOnDelete();
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('due_date');
            $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
            $table->decimal('progress_percentage', 5, 2)->default(0)->after('actual_hours');
            $table->timestamp('completed_at')->nullable()->after('progress_percentage');
            $table->text('blocking_reason')->nullable()->after('completed_at');
            $table->boolean('is_recurring')->default(false)->after('blocking_reason');
            $table->string('recurrence_pattern')->nullable()->after('is_recurring'); // daily, weekly, monthly
            
            $table->index(['assigned_to', 'status']);
            $table->index('due_date');
            $table->index(['priority', 'status']);
            $table->index('parent_task_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['parent_task_id']);
            $table->dropColumn([
                'assigned_to', 'parent_task_id', 'estimated_hours', 'actual_hours',
                'progress_percentage', 'completed_at', 'blocking_reason',
                'is_recurring', 'recurrence_pattern'
            ]);
        });
    }
};

// =============================================================================
// 6. نظام الصلاحيات - Roles
// =============================================================================

/**
 * Migration: 2025_12_24_000006_create_roles_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // لا يمكن حذف الأدوار النظامية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

// =============================================================================
// 7. نظام الصلاحيات - Permissions
// =============================================================================

/**
 * Migration: 2025_12_24_000007_create_permissions_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('group', 50); // teams, projects, tasks, users, etc.
            $table->timestamps();
            
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

// =============================================================================
// 8. نظام الصلاحيات - Role Permission (Many-to-Many)
// =============================================================================

/**
 * Migration: 2025_12_24_000008_create_role_permission_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
    }
};

// =============================================================================
// 9. نظام الصلاحيات - Role User (Polymorphic)
// =============================================================================

/**
 * Migration: 2025_12_24_000009_create_role_user_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('entity_type')->nullable(); // App\Models\Team, App\Models\Project
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'entity_type', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};

// =============================================================================
// 10. نظام الإشعارات
// =============================================================================

/**
 * Migration: 2025_12_24_000010_create_notifications_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // user_id عادة
            $table->text('data'); // JSON data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

// =============================================================================
// 11. إعدادات الإشعارات
// =============================================================================

/**
 * Migration: 2025_12_24_000011_create_notification_settings_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->json('email_notifications')->nullable();
            $table->json('push_notifications')->nullable();
            $table->json('database_notifications')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};

// =============================================================================
// 12. نظام التعليقات
// =============================================================================

/**
 * Migration: 2025_12_24_000012_create_comments_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // Task, Project
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};

// =============================================================================
// 13. نظام المرفقات
// =============================================================================

/**
 * Migration: 2025_12_24_000013_create_attachments_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // Task, Project, Comment
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('file_type', 100); // mime_type
            $table->string('file_extension', 20);
            $table->boolean('is_image')->default(false);
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};

// =============================================================================
// 14. سجل النشاطات (Activity Log)
// =============================================================================

/**
 * Migration: 2025_12_24_000014_create_activity_logs_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('subject'); // Task, Project, Team, etc.
            $table->string('action', 50); // created, updated, deleted, assigned, etc.
            $table->text('description');
            $table->json('properties')->nullable(); // قبل وبعد التغيير
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

// =============================================================================
// 15. نظام الدعوات
// =============================================================================

/**
 * Migration: 2025_12_24_000015_create_invitations_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->morphs('invitable'); // Team, Project
            $table->foreignId('inviter_id')->constrained('users')->onDelete('cascade');
            $table->string('invitee_email');
            $table->foreignId('invitee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 50)->default('member');
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            $table->index(['invitable_type', 'invitable_id']);
            $table->index(['invitee_email', 'status']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};

// =============================================================================
// 16. نظام تتبع الوقت
// =============================================================================

/**
 * Migration: 2025_12_24_000016_create_time_entries_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // محسوب تلقائياً
            $table->boolean('is_billable')->default(false);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['task_id', 'user_id']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};

// =============================================================================
// 17. نظام الوسوم (Tags)
// =============================================================================

/**
 * Migration: 2025_12_24_000017_create_tags_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6B7280'); // hex color
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};

// =============================================================================
// 18. جدول الوسوم المتعدد (Taggables - Polymorphic)
// =============================================================================

/**
 * Migration: 2025_12_24_000018_create_taggables_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->morphs('taggable'); // Task, Project
            $table->timestamps();
            
            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
            $table->index(['taggable_type', 'taggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};

// =============================================================================
// 19. قائمة المهام الفرعية (Task Checklist)
// =============================================================================

/**
 * Migration: 2025_12_24_000019_create_task_checklist_items_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['task_id', 'is_completed']);
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklist_items');
    }
};

// =============================================================================
// 20. الحقول المخصصة (Custom Fields)
// =============================================================================

/**
 * Migration: 2025_12_24_000020_create_custom_fields_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50); // Task, Project
            $table->string('name');
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'multi_select', 'checkbox']);
            $table->json('options')->nullable(); // للقوائم المنسدلة
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('entity_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};

// =============================================================================
// 21. قيم الحقول المخصصة
// =============================================================================

/**
 * Migration: 2025_12_24_000021_create_custom_field_values_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->onDelete('cascade');
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->index(['custom_field_id', 'entity_type', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};

// =============================================================================
// 22. قوالب المشاريع
// =============================================================================

/**
 * Migration: 2025_12_24_000022_create_project_templates_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('structure'); // المهام والمراحل
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_public')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};

// =============================================================================
// 23. قوالب المهام
// =============================================================================

/**
 * Migration: 2025_12_24_000023_create_task_templates_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('checklist')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};

// =============================================================================
// 24. المعالم الرئيسية (Milestones)
// =============================================================================

/**
 * Migration: 2025_12_24_000024_create_milestones_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->enum('status', ['pending', 'completed', 'missed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};

// =============================================================================
// 25. التبعيات بين المهام
// =============================================================================

/**
 * Migration: 2025_12_24_000025_create_task_dependencies_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('depends_on_task_id')->constrained('tasks')->onDelete('cascade');
            $table->enum('dependency_type', ['finish_to_start', 'start_to_start', 'finish_to_finish'])->default('finish_to_start');
            $table->integer('lag_days')->default(0); // التأخير بالأيام
            $table->timestamps();
            
            $table->unique(['task_id', 'depends_on_task_id']);
            $table->index('depends_on_task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
    }
};

// =============================================================================
// 26. المفضلة
// =============================================================================

/**
 * Migration: 2025_12_24_000026_create_favorites_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable'); // Project, Task, Team
            $table->timestamps();
            
            $table->unique(['user_id', 'favoritable_type', 'favoritable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};

// =============================================================================
// 27. المشاهدات الأخيرة
// =============================================================================

/**
 * Migration: 2025_12_24_000027_create_recent_views_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recent_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('viewable'); // Project, Task, Team
            $table->timestamp('viewed_at');
            $table->integer('view_count')->default(1);
            
            $table->unique(['user_id', 'viewable_type', 'viewable_id']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recent_views');
    }
};

// =============================================================================
// 28. إعدادات المستخدم
// =============================================================================

/**
 * Migration: 2025_12_24_000028_create_user_settings_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->json('notifications')->nullable();
            $table->json('preferences')->nullable();
            $table->json('privacy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};

// =============================================================================
// 29. Dashboard Widgets
// =============================================================================

/**
 * Migration: 2025_12_24_000029_create_dashboard_widgets_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_type', 50);
            $table->integer('position')->default(0);
            $table->enum('size', ['small', 'medium', 'large'])->default('medium');
            $table->json('settings')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};

// =============================================================================
// 30. التقارير
// =============================================================================

/**
 * Migration: 2025_12_24_000030_create_reports_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('report_type', ['tasks', 'projects', 'time', 'team_performance']);
            $table->json('filters')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_scheduled')->default(false);
            $table->enum('schedule_frequency', ['daily', 'weekly', 'monthly'])->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

// =============================================================================
// 31. إضافة Soft Deletes للجداول الوسيطة
// =============================================================================

/**
 * Migration: 2025_12_24_000031_add_soft_deletes_to_pivot_tables
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('project_user', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('task_user', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('project_user', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('task_user', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

// =============================================================================
// ملاحظات مهمة للتطبيق:
// =============================================================================

/**
 * 1. تأكد من حذف الملف: 2025_12_22_192500_add_team_id_to_tasks_table.php
 * 
 * 2. قم بتشغيل المهجرات بالترتيب:
 *    php artisan migrate
 * 
 * 3. أنشئ Seeder للبيانات الأساسية:
 *    - RolesAndPermissionsSeeder
 *    - UsersSeeder
 *    - TeamsSeeder (للتجربة)
 * 
 * 4. أنشئ Models مع العلاقات:
 *    - Role, Permission
 *    - Comment, Attachment
 *    - ActivityLog, Invitation
 *    - TimeEntry, Tag
 *    - CustomField, Milestone
 * 
 * 5. أنشئ Policies للصلاحيات:
 *    - TeamPolicy
 *    - ProjectPolicy
 *    - TaskPolicy
 * 
 * 6. أنشئ Observers:
 *    - TaskObserver (لتسجيل الأنشطة)
 *    - ProjectObserver
 * 
 * 7. أنشئ Events & Listeners:
 *    - TaskAssigned -> SendTaskAssignedNotification
 *    - TaskCompleted -> SendTaskCompletedNotification
 * 
 * 8. أنشئ Jobs للمهام الثقيلة:
 *    - SendBulkNotifications
 *    - GenerateReportJob
 * 
 * 9. استخدم Laravel Sanctum للـ API:
 *    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 * 
 * 10. استخدم Spatie Laravel Permission (اختياري):
 *     composer require spatie/laravel-permission
 */
