<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@taskmanager.com')->first();

        $tags = [
            ['name' => 'عاجل', 'color' => '#EF4444'],
            ['name' => 'Bug', 'color' => '#DC2626'],
            ['name' => 'Feature', 'color' => '#10B981'],
            ['name' => 'Enhancement', 'color' => '#3B82F6'],
            ['name' => 'Documentation', 'color' => '#8B5CF6'],
            ['name' => 'Frontend', 'color' => '#F59E0B'],
            ['name' => 'Backend', 'color' => '#06B6D4'],
            ['name' => 'Database', 'color' => '#EC4899'],
            ['name' => 'Testing', 'color' => '#14B8A6'],
            ['name' => 'Design', 'color' => '#F97316'],
        ];

        foreach ($tags as $tagData) {
            Tag::create([
                ...$tagData,
                'slug' => Str::slug($tagData['name']),
                'created_by' => $admin->id,
            ]);
        }
    }
}
