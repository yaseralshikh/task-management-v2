<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'commentable_type' => Task::class,
            'commentable_id' => Task::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => fake()->paragraph(),
            'is_edited' => false,
            'edited_at' => null,
        ];
    }

    public function onTask(Task $task): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
    }

    public function onProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
        ]);
    }

    public function reply(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
        ]);
    }

    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_edited' => true,
            'edited_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}
