<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskChecklistItem>
 */
class TaskChecklistItemFactory extends Factory
{
    protected $model = TaskChecklistItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'title' => fake()->sentence(3),
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
            'order' => fake()->numberBetween(0, 20),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_by' => User::factory(),
            'completed_at' => now(),
        ]);
    }
}
