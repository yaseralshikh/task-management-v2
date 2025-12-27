<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentsAndAttachmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
