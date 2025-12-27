<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Models\TimeEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
