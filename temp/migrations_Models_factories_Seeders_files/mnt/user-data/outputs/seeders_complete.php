<?php

/**
 * ============================================================================
 * Laravel Seeders - نظام إدارة المهام
 * ============================================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Team;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\TimeEntry;
use App\Models\TaskChecklistItem;
use App\Models\Milestone;
use Illuminate\Support\Str;

// ============================================================================
// DatabaseSeeder - الملف الرئيسي
// ============================================================================
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // تشغيل جميع الـ Seeders بالترتيب
        $this->call([
            RolesAndPermissionsSeeder::class,
            UsersSeeder::class,
            TagsSeeder::class,
            TeamsSeeder::class,
            ProjectsSeeder::class,
            TasksSeeder::class,
            CommentsAndAttachmentsSeeder::class,
        ]);
    }
}

// ============================================================================
// RolesAndPermissionsSeeder
// ============================================================================
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الصلاحيات
        $permissions = $this->createPermissions();
        
        // إنشاء الأدوار
        $roles = $this->createRoles();
        
        // ربط الصلاحيات بالأدوار
        $this->assignPermissionsToRoles($roles, $permissions);
    }

    private function createPermissions(): array
    {
        $permissionsData = [
            // Teams
            ['name' => 'عرض الفرق', 'slug' => 'view-teams', 'group' => 'teams'],
            ['name' => 'إنشاء فريق', 'slug' => 'create-teams', 'group' => 'teams'],
            ['name' => 'تعديل الفرق', 'slug' => 'edit-teams', 'group' => 'teams'],
            ['name' => 'حذف الفرق', 'slug' => 'delete-teams', 'group' => 'teams'],
            ['name' => 'إدارة أعضاء الفريق', 'slug' => 'manage-team-members', 'group' => 'teams'],
            
            // Projects
            ['name' => 'عرض المشاريع', 'slug' => 'view-projects', 'group' => 'projects'],
            ['name' => 'إنشاء مشروع', 'slug' => 'create-projects', 'group' => 'projects'],
            ['name' => 'تعديل المشاريع', 'slug' => 'edit-projects', 'group' => 'projects'],
            ['name' => 'حذف المشاريع', 'slug' => 'delete-projects', 'group' => 'projects'],
            ['name' => 'أرشفة المشاريع', 'slug' => 'archive-projects', 'group' => 'projects'],
            ['name' => 'إدارة أعضاء المشروع', 'slug' => 'manage-project-members', 'group' => 'projects'],
            
            // Tasks
            ['name' => 'عرض المهام', 'slug' => 'view-tasks', 'group' => 'tasks'],
            ['name' => 'إنشاء مهمة', 'slug' => 'create-tasks', 'group' => 'tasks'],
            ['name' => 'تعديل المهام', 'slug' => 'edit-tasks', 'group' => 'tasks'],
            ['name' => 'حذف المهام', 'slug' => 'delete-tasks', 'group' => 'tasks'],
            ['name' => 'تعيين المهام', 'slug' => 'assign-tasks', 'group' => 'tasks'],
            ['name' => 'تغيير حالة المهام', 'slug' => 'change-task-status', 'group' => 'tasks'],
            
            // Comments
            ['name' => 'عرض التعليقات', 'slug' => 'view-comments', 'group' => 'comments'],
            ['name' => 'إضافة تعليق', 'slug' => 'create-comments', 'group' => 'comments'],
            ['name' => 'تعديل التعليقات', 'slug' => 'edit-comments', 'group' => 'comments'],
            ['name' => 'حذف التعليقات', 'slug' => 'delete-comments', 'group' => 'comments'],
            
            // Attachments
            ['name' => 'رفع المرفقات', 'slug' => 'upload-attachments', 'group' => 'attachments'],
            ['name' => 'حذف المرفقات', 'slug' => 'delete-attachments', 'group' => 'attachments'],
            
            // Time Tracking
            ['name' => 'تسجيل الوقت', 'slug' => 'log-time', 'group' => 'time'],
            ['name' => 'عرض سجلات الوقت', 'slug' => 'view-time-entries', 'group' => 'time'],
            ['name' => 'تعديل سجلات الوقت', 'slug' => 'edit-time-entries', 'group' => 'time'],
            ['name' => 'حذف سجلات الوقت', 'slug' => 'delete-time-entries', 'group' => 'time'],
            
            // Reports
            ['name' => 'عرض التقارير', 'slug' => 'view-reports', 'group' => 'reports'],
            ['name' => 'إنشاء تقارير', 'slug' => 'create-reports', 'group' => 'reports'],
            ['name' => 'تصدير البيانات', 'slug' => 'export-data', 'group' => 'reports'],
            
            // Users
            ['name' => 'عرض المستخدمين', 'slug' => 'view-users', 'group' => 'users'],
            ['name' => 'إدارة المستخدمين', 'slug' => 'manage-users', 'group' => 'users'],
            ['name' => 'إدارة الأدوار', 'slug' => 'manage-roles', 'group' => 'users'],
        ];

        $permissions = [];
        foreach ($permissionsData as $permissionData) {
            $permissions[$permissionData['slug']] = Permission::create($permissionData);
        }

        return $permissions;
    }

    private function createRoles(): array
    {
        $rolesData = [
            [
                'name' => 'مدير النظام',
                'slug' => 'super-admin',
                'description' => 'صلاحيات كاملة على النظام',
                'is_system' => true,
            ],
            [
                'name' => 'مالك الفريق',
                'slug' => 'team-owner',
                'description' => 'مالك الفريق - صلاحيات كاملة على الفريق',
                'is_system' => true,
            ],
            [
                'name' => 'مدير الفريق',
                'slug' => 'team-admin',
                'description' => 'مدير الفريق - صلاحيات إدارية',
                'is_system' => false,
            ],
            [
                'name' => 'عضو فريق',
                'slug' => 'team-member',
                'description' => 'عضو فريق - صلاحيات محدودة',
                'is_system' => false,
            ],
            [
                'name' => 'مدير مشروع',
                'slug' => 'project-manager',
                'description' => 'مدير مشروع - صلاحيات كاملة على المشروع',
                'is_system' => false,
            ],
            [
                'name' => 'عضو مشروع',
                'slug' => 'project-member',
                'description' => 'عضو مشروع - مشاركة في المهام',
                'is_system' => false,
            ],
            [
                'name' => 'مراقب',
                'slug' => 'viewer',
                'description' => 'مراقب - قراءة فقط',
                'is_system' => false,
            ],
        ];

        $roles = [];
        foreach ($rolesData as $roleData) {
            $roles[$roleData['slug']] = Role::create($roleData);
        }

        return $roles;
    }

    private function assignPermissionsToRoles(array $roles, array $permissions): void
    {
        // Super Admin - كل الصلاحيات
        $roles['super-admin']->permissions()->attach(array_column($permissions, 'id'));

        // Team Owner
        $teamOwnerPermissions = [
            'view-teams', 'edit-teams', 'manage-team-members',
            'view-projects', 'create-projects', 'edit-projects', 'delete-projects', 'archive-projects', 'manage-project-members',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'assign-tasks', 'change-task-status',
            'view-comments', 'create-comments', 'edit-comments', 'delete-comments',
            'upload-attachments', 'delete-attachments',
            'log-time', 'view-time-entries', 'edit-time-entries',
            'view-reports', 'create-reports', 'export-data',
        ];
        $roles['team-owner']->permissions()->attach(
            Permission::whereIn('slug', $teamOwnerPermissions)->pluck('id')
        );

        // Team Admin
        $teamAdminPermissions = [
            'view-teams', 'edit-teams', 'manage-team-members',
            'view-projects', 'create-projects', 'edit-projects', 'manage-project-members',
            'view-tasks', 'create-tasks', 'edit-tasks', 'assign-tasks', 'change-task-status',
            'view-comments', 'create-comments', 'edit-comments',
            'upload-attachments',
            'log-time', 'view-time-entries',
            'view-reports',
        ];
        $roles['team-admin']->permissions()->attach(
            Permission::whereIn('slug', $teamAdminPermissions)->pluck('id')
        );

        // Team Member
        $teamMemberPermissions = [
            'view-teams',
            'view-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'change-task-status',
            'view-comments', 'create-comments', 'edit-comments',
            'upload-attachments',
            'log-time', 'view-time-entries', 'edit-time-entries',
        ];
        $roles['team-member']->permissions()->attach(
            Permission::whereIn('slug', $teamMemberPermissions)->pluck('id')
        );

        // Project Manager
        $projectManagerPermissions = [
            'view-projects', 'edit-projects', 'manage-project-members',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'assign-tasks', 'change-task-status',
            'view-comments', 'create-comments', 'delete-comments',
            'upload-attachments', 'delete-attachments',
            'view-time-entries',
            'view-reports', 'create-reports',
        ];
        $roles['project-manager']->permissions()->attach(
            Permission::whereIn('slug', $projectManagerPermissions)->pluck('id')
        );

        // Project Member
        $projectMemberPermissions = [
            'view-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'change-task-status',
            'view-comments', 'create-comments', 'edit-comments',
            'upload-attachments',
            'log-time', 'view-time-entries', 'edit-time-entries',
        ];
        $roles['project-member']->permissions()->attach(
            Permission::whereIn('slug', $projectMemberPermissions)->pluck('id')
        );

        // Viewer
        $viewerPermissions = [
            'view-teams',
            'view-projects',
            'view-tasks',
            'view-comments',
            'view-time-entries',
            'view-reports',
        ];
        $roles['viewer']->permissions()->attach(
            Permission::whereIn('slug', $viewerPermissions)->pluck('id')
        );
    }
}

// ============================================================================
// UsersSeeder
// ============================================================================
class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // مستخدم المطور (أنت)
        $admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@taskmanager.com',
            'national_id' => '1234567890',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => null,
            'phone' => '+966501234567',
            'job_title' => 'مدير النظام',
            'bio' => 'مدير النظام الرئيسي',
            'is_owner' => true,
            'is_active' => true,
        ]);

        // إعطاء صلاحية Super Admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $admin->assignRole('super-admin');

        // مستخدمين تجريبيين
        $users = [
            [
                'name' => 'أحمد محمد',
                'email' => 'ahmed@example.com',
                'national_id' => '1111111111',
                'job_title' => 'مدير مشاريع',
            ],
            [
                'name' => 'سارة أحمد',
                'email' => 'sarah@example.com',
                'national_id' => '2222222222',
                'job_title' => 'مطورة واجهات',
            ],
            [
                'name' => 'محمد علي',
                'email' => 'mohammed@example.com',
                'national_id' => '3333333333',
                'job_title' => 'مطور Backend',
            ],
            [
                'name' => 'فاطمة حسن',
                'email' => 'fatima@example.com',
                'national_id' => '4444444444',
                'job_title' => 'مصممة UI/UX',
            ],
            [
                'name' => 'خالد سعيد',
                'email' => 'khaled@example.com',
                'national_id' => '5555555555',
                'job_title' => 'مختبر جودة',
            ],
        ];

        foreach ($users as $userData) {
            User::create([
                ...$userData,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
        }

        // مستخدمين عشوائيين إضافيين
        User::factory(15)->create();
    }
}

// ============================================================================
// TagsSeeder
// ============================================================================
class TagsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@taskmanager.com')->first();

        $tags = [
            ['name' => 'عاجل', 'color' => '#EF4444'],
            ['name' => 'Bug', 'color' => '#DC2626'],
            ['name' => 'Feature', 'color' => '#10B981'],
            ['name' => 'Enhancement', 'color' => '#3B82F6'],
            ['name' => 'Documentation', 'color' => '#8B5CF6'],
            ['name' => 'Frontend', 'color' => '#F59E0B'],
            ['name' => 'Backend', 'color' => '#06B6D4'],
            ['name' => 'Database', 'color' => '#EC4899'],
            ['name' => 'Testing', 'color' => '#14B8A6'],
            ['name' => 'Design', 'color' => '#F97316'],
        ];

        foreach ($tags as $tagData) {
            Tag::create([
                ...$tagData,
                'slug' => Str::slug($tagData['name']),
                'created_by' => $admin->id,
            ]);
        }
    }
}

// ============================================================================
// TeamsSeeder
// ============================================================================
class TeamsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@taskmanager.com')->first();
        $users = User::where('email', '!=', 'admin@taskmanager.com')->limit(5)->get();

        // فريق التطوير
        $devTeam = Team::create([
            'name' => 'فريق التطوير',
            'slug' => 'dev-team',
            'description' => 'فريق تطوير البرمجيات',
            'color' => '#3B82F6',
            'is_active' => true,
            'max_members' => 20,
            'owner_id' => $admin->id,
        ]);

        // إضافة أعضاء للفريق
        foreach ($users as $index => $user) {
            $devTeam->addMember($user, $index === 0 ? 'admin' : 'member');
        }

        // فريق التصميم
        $designTeam = Team::create([
            'name' => 'فريق التصميم',
            'slug' => 'design-team',
            'description' => 'فريق تصميم الواجهات',
            'color' => '#10B981',
            'is_active' => true,
            'max_members' => 10,
            'owner_id' => $users->first()->id,
        ]);

        // فرق إضافية
        Team::factory(3)->create();
    }
}

// ============================================================================
// ProjectsSeeder
// ============================================================================
class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();
        $users = User::limit(5)->get();

        foreach ($teams as $team) {
            // 2-4 مشاريع لكل فريق
            $projectsCount = rand(2, 4);
            
            for ($i = 0; $i < $projectsCount; $i++) {
                $project = Project::create([
                    'name' => fake()->unique()->catchPhrase(),
                    'slug' => Str::slug(fake()->unique()->catchPhrase()),
                    'description' => fake()->paragraphs(3, true),
                    'color' => fake()->hexColor(),
                    'team_id' => $team->id,
                    'owner_id' => $team->owner_id,
                    'start_date' => now()->subDays(rand(30, 180)),
                    'end_date' => now()->addDays(rand(30, 180)),
                    'status' => fake()->randomElement(['planning', 'active', 'active', 'active', 'on_hold']),
                ]);

                // إضافة أعضاء للمشروع
                foreach ($users->random(rand(2, 4)) as $index => $user) {
                    $project->addMember($user, $index === 0 ? 'admin' : 'member');
                }

                // إنشاء Milestones
                Milestone::factory(rand(2, 5))->create([
                    'project_id' => $project->id,
                ]);

                // تحديث التقدم
                $project->updateProgress();
            }
        }
    }
}

// ============================================================================
// TasksSeeder
// ============================================================================
class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $tags = Tag::all();

        foreach ($projects as $project) {
            $projectMembers = $project->members;
            
            // 5-15 مهمة لكل مشروع
            $tasksCount = rand(5, 15);
            
            for ($i = 0; $i < $tasksCount; $i++) {
                $assignedTo = $projectMembers->random();
                $createdBy = $projectMembers->random();
                
                $task = Task::create([
                    'project_id' => $project->id,
                    'team_id' => $project->team_id,
                    'assigned_to' => $assignedTo->id,
                    'title' => fake()->sentence(),
                    'description' => fake()->paragraphs(2, true),
                    'status' => fake()->randomElement(['todo', 'todo', 'in_progress', 'in_progress', 'done']),
                    'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high', 'urgent']),
                    'start_date' => now()->subDays(rand(0, 30)),
                    'due_date' => now()->addDays(rand(5, 60)),
                    'estimated_hours' => rand(1, 40),
                    'order' => $i,
                    'created_by' => $createdBy->id,
                ]);

                // إضافة Tags
                if (rand(0, 1)) {
                    $task->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
                }

                // إضافة Checklist Items
                if (rand(0, 1)) {
                    TaskChecklistItem::factory(rand(2, 6))->create([
                        'task_id' => $task->id,
                    ]);
                    $task->updateProgress();
                }

                // إضافة Time Entries
                if ($task->status !== 'todo') {
                    TimeEntry::factory(rand(1, 5))->create([
                        'task_id' => $task->id,
                        'user_id' => $assignedTo->id,
                    ]);
                    $task->updateActualHours();
                }

                // إكمال المهمة إذا كانت done
                if ($task->status === 'done') {
                    $task->complete($assignedTo);
                }
            }

            // إنشاء بعض المهام الفرعية
            $parentTasks = $project->tasks()->whereNull('parent_task_id')->limit(3)->get();
            foreach ($parentTasks as $parentTask) {
                $subtasksCount = rand(1, 3);
                for ($i = 0; $i < $subtasksCount; $i++) {
                    Task::factory()->create([
                        'project_id' => $project->id,
                        'team_id' => $project->team_id,
                        'parent_task_id' => $parentTask->id,
                        'assigned_to' => $projectMembers->random()->id,
                        'created_by' => $projectMembers->random()->id,
                    ]);
                }
            }

            // تحديث تقدم المشروع
            $project->updateProgress();
        }
    }
}

// ============================================================================
// CommentsAndAttachmentsSeeder
// ============================================================================
class CommentsAndAttachmentsSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = Task::all();
        $projects = Project::all();

        // تعليقات على المهام
        foreach ($tasks->random(min(30, $tasks->count())) as $task) {
            $members = $task->project->members;
            
            // 1-5 تعليقات
            $commentsCount = rand(1, 5);
            for ($i = 0; $i < $commentsCount; $i++) {
                $comment = Comment::factory()->create([
                    'commentable_type' => Task::class,
                    'commentable_id' => $task->id,
                    'user_id' => $members->random()->id,
                ]);

                // بعض الردود
                if (rand(0, 1)) {
                    Comment::factory()->create([
                        'commentable_type' => Task::class,
                        'commentable_id' => $task->id,
                        'parent_id' => $comment->id,
                        'user_id' => $members->random()->id,
                    ]);
                }
            }
        }

        // تعليقات على المشاريع
        foreach ($projects->random(min(10, $projects->count())) as $project) {
            Comment::factory(rand(1, 3))->create([
                'commentable_type' => Project::class,
                'commentable_id' => $project->id,
                'user_id' => $project->members->random()->id,
            ]);
        }

        // مرفقات على المهام
        foreach ($tasks->random(min(20, $tasks->count())) as $task) {
            Attachment::factory(rand(1, 3))->create([
                'attachable_type' => Task::class,
                'attachable_id' => $task->id,
                'user_id' => $task->project->members->random()->id,
            ]);
        }
    }
}
