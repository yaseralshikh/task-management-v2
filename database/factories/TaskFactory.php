<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 months', '+1 month');
        $dueDate = fake()->dateTimeBetween($startDate, '+2 months');
        $status = fake()->randomElement(['todo', 'in_progress', 'done']);

        return [
            'project_id' => Project::factory(),
            'team_id' => null,
            'assigned_to' => User::factory(),
            'parent_task_id' => null,
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'estimated_hours' => fake()->randomFloat(2, 1, 40),
            'actual_hours' => $status === 'done' ? fake()->randomFloat(2, 1, 40) : null,
            'progress_percentage' => $status === 'done' ? 100 : fake()->randomFloat(2, 0, 90),
            'completed_at' => $status === 'done' ? now() : null,
            'blocking_reason' => null,
            'is_recurring' => fake()->boolean(10),
            'recurrence_pattern' => null,
            'order' => fake()->numberBetween(0, 100),
            'created_by' => User::factory(),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'todo',
            'progress_percentage' => 0,
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percentage' => fake()->randomFloat(2, 10, 90),
            'completed_at' => null,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'progress_percentage' => 100,
            'completed_at' => now(),
            'actual_hours' => fake()->randomFloat(2, 1, 40),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => 'todo',
        ]);
    }

    public function subtask(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_task_id' => Task::factory(),
        ]);
    }

    public function withProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => $project->id,
            'team_id' => $project->team_id,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
