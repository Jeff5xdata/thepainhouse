<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exercises = [
            [
                'name' => 'Bench Press',
                'description' => 'A compound exercise that targets the chest, shoulders, and triceps.',
                'category' => 'chest',
                'equipment' => 'barbell',
            ],
            [
                'name' => 'Squat',
                'description' => 'A compound exercise that targets the legs and core.',
                'category' => 'legs',
                'equipment' => 'barbell',
            ],
            [
                'name' => 'Deadlift',
                'description' => 'A compound exercise that targets the back, legs, and core.',
                'category' => 'back',
                'equipment' => 'barbell',
            ],
            [
                'name' => 'Pull-up',
                'description' => 'A compound exercise that targets the back and biceps.',
                'category' => 'back',
                'equipment' => 'bodyweight',
            ],
            [
                'name' => 'Push-up',
                'description' => 'A compound exercise that targets the chest, shoulders, and triceps.',
                'category' => 'chest',
                'equipment' => 'bodyweight',
            ],
            [
                'name' => 'Plank',
                'description' => 'An isometric exercise that targets the core.',
                'category' => 'core',
                'equipment' => 'bodyweight',
            ],
        ];

        foreach ($exercises as $exercise) {
            Exercise::create($exercise);
        }
    }
}
