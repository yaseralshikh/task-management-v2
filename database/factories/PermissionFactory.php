<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $groups = ['teams', 'projects', 'tasks', 'users', 'reports'];
        $actions = ['view', 'create', 'edit', 'delete'];
        
        $group = fake()->randomElement($groups);
        $action = fake()->randomElement($actions);
        $name = ucfirst($action) . ' ' . ucfirst($group);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'group' => $group,
        ];
    }
}
