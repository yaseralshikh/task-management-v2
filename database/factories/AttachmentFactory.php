<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isImage = fake()->boolean(40);
        $fileName = fake()->uuid();
        $extension = $isImage ? fake()->randomElement(['jpg', 'png', 'gif']) : fake()->randomElement(['pdf', 'docx', 'xlsx']);

        return [
            'attachable_type' => Task::class,
            'attachable_id' => Task::factory(),
            'user_id' => User::factory(),
            'file_name' => $fileName . '.' . $extension,
            'file_original_name' => fake()->word() . '.' . $extension,
            'file_path' => 'attachments/' . $fileName . '.' . $extension,
            'file_size' => fake()->numberBetween(10000, 5000000),
            'file_type' => $isImage ? 'image/' . $extension : 'application/' . $extension,
            'file_extension' => $extension,
            'is_image' => $isImage,
            'thumbnail_path' => $isImage ? 'thumbnails/' . $fileName . '.jpg' : null,
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_extension' => 'jpg',
            'file_type' => 'image/jpeg',
            'is_image' => true,
            'thumbnail_path' => 'thumbnails/' . fake()->uuid() . '.jpg',
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_extension' => 'pdf',
            'file_type' => 'application/pdf',
            'is_image' => false,
            'thumbnail_path' => null,
        ]);
    }
}
