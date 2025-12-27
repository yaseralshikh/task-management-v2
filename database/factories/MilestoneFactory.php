<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Milestone>
 */
class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'completed', 'missed']);

        return [
            'project_id' => Project::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'due_date' => fake()->dateTimeBetween('now', '+6 months'),
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
            'order' => fake()->numberBetween(0, 10),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
        ]);
    }
}
