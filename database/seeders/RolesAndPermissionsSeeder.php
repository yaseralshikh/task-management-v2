<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
