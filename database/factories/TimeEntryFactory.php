<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TimeEntryFactory extends Factory
{
     protected $model = TimeEntry::class;
     
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = Carbon::instance(fake()->dateTimeBetween('-30 days', 'now'));
        $durationMinutes = fake()->numberBetween(5, 8 * 60);
        $endedAt = (clone $startedAt)->addMinutes($durationMinutes);

        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'description' => fake()->sentence(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
            'duration_minutes' => null,
        ]);
    }

    public function forTask(Task $task): static
    {
        return $this->state(fn (array $attributes) => [
            'task_id' => $task->id,
        ]);
    }
}
