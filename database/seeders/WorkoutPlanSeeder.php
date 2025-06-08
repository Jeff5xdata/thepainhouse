<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Illuminate\Database\Seeder;

class WorkoutPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        $workoutPlan = WorkoutPlan::create([
            'name' => 'Beginner Full Body Workout',
            'description' => 'A full body workout plan suitable for beginners.',
            'weeks_duration' => 4,
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        // Get all exercises
        $exercises = Exercise::all();

        // Create a schedule for week 1
        $schedule = [
            'monday' => [
                ['exercise' => 'Push-up', 'sets' => 3, 'reps' => 10],
                ['exercise' => 'Squat', 'sets' => 3, 'reps' => 10],
                ['exercise' => 'Plank', 'time_in_seconds' => 30, 'sets' => 3],
            ],
            'wednesday' => [
                ['exercise' => 'Pull-up', 'sets' => 3, 'reps' => 5],
                ['exercise' => 'Deadlift', 'sets' => 3, 'reps' => 8],
                ['exercise' => 'Plank', 'time_in_seconds' => 30, 'sets' => 3],
            ],
            'friday' => [
                ['exercise' => 'Bench Press', 'sets' => 3, 'reps' => 8],
                ['exercise' => 'Squat', 'sets' => 3, 'reps' => 10],
                ['exercise' => 'Plank', 'time_in_seconds' => 30, 'sets' => 3],
            ],
        ];

        foreach ($schedule as $day => $exercises_list) {
            foreach ($exercises_list as $index => $exercise_data) {
                $exercise = $exercises->firstWhere('name', $exercise_data['exercise']);
                
                if ($exercise) {
                    $is_time_based = isset($exercise_data['time_in_seconds']);
                    
                    WorkoutPlanSchedule::create([
                        'workout_plan_id' => $workoutPlan->id,
                        'exercise_id' => $exercise->id,
                        'week_number' => 1,
                        'day_of_week' => $day,
                        'order_in_day' => $index,
                        'is_time_based' => $is_time_based,
                        'sets' => $exercise_data['sets'],
                        'reps' => $is_time_based ? 1 : $exercise_data['reps'],
                        'time_in_seconds' => $exercise_data['time_in_seconds'] ?? null,
                    ]);
                }
            }
        }
    }
}
