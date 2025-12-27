<?php

namespace Database\Seeders;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
