<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'national_id' => fake()->unique()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => fake()->imageUrl(200, 200, 'people'),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'timezone' => 'Asia/Riyadh',
            'language' => 'ar',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'week_starts_on' => 6,
            'theme' => fake()->randomElement(['light', 'dark', 'auto']),
            'is_owner' => false,
            'is_active' => true,
            'last_login_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_owner' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }    

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
