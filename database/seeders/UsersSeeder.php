<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
