<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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

        // إضافة أعضاء للفريق
        foreach ($users as $index => $user) {
            $designTeam->addMember($user, $index === 0 ? 'admin' : 'member');
        }        

        // فرق إضافية
        Team::factory(3)->create();
    }
}
