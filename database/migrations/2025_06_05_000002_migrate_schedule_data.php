<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all workout plans
        $workoutPlans = WorkoutPlan::all();

        foreach ($workoutPlans as $plan) {
            if (empty($plan->schedule)) {
                continue;
            }

            foreach ($plan->schedule as $week => $days) {
                foreach ($days as $day => $exercises) {
                    foreach ($exercises as $index => $exercise) {
                        if (empty($exercise['exercise_id'])) {
                            continue;
                        }

                        WorkoutPlanSchedule::create([
                            'workout_plan_id' => $plan->id,
                            'exercise_id' => $exercise['exercise_id'],
                            'week_number' => $week,
                            'day_of_week' => $day,
                            'order_in_day' => $index,
                            'is_time_based' => $exercise['is_time_based'] ?? false,
                            'sets' => $exercise['sets'] ?? 3,
                            'reps' => $exercise['reps'] ?? 10,
                            'time_in_seconds' => $exercise['time'] ?? null,
                            'has_warmup' => $exercise['has_warmup'] ?? false,
                            'warmup_sets' => $exercise['warmup_sets'] ?? null,
                            'warmup_reps' => $exercise['warmup_reps'] ?? null,
                            'warmup_time_in_seconds' => $exercise['warmup_time'] ?? null,
                            'warmup_weight_percentage' => $exercise['warmup_weight_percentage'] ?? null,
                        ]);
                    }
                }
            }
        }

        // Remove the schedule column from workout_plans table
        Schema::table('workout_plans', function($table) {
            $table->dropColumn('schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the schedule column
        Schema::table('workout_plans', function($table) {
            $table->json('schedule')->nullable();
        });

        // Get all workout plans
        $workoutPlans = WorkoutPlan::all();

        foreach ($workoutPlans as $plan) {
            $schedule = [];
            
            $scheduleItems = WorkoutPlanSchedule::where('workout_plan_id', $plan->id)
                ->orderBy('week_number')
                ->orderBy('day_of_week')
                ->orderBy('order_in_day')
                ->get();

            foreach ($scheduleItems as $item) {
                if (!isset($schedule[$item->week_number])) {
                    $schedule[$item->week_number] = [];
                }
                if (!isset($schedule[$item->week_number][$item->day_of_week])) {
                    $schedule[$item->week_number][$item->day_of_week] = [];
                }

                $schedule[$item->week_number][$item->day_of_week][] = [
                    'exercise_id' => $item->exercise_id,
                    'is_time_based' => $item->is_time_based,
                    'sets' => $item->sets,
                    'reps' => $item->reps,
                    'time' => $item->time_in_seconds,
                    'has_warmup' => $item->has_warmup,
                    'warmup_sets' => $item->warmup_sets,
                    'warmup_reps' => $item->warmup_reps,
                    'warmup_time' => $item->warmup_time_in_seconds,
                    'warmup_weight_percentage' => $item->warmup_weight_percentage,
                ];
            }

            $plan->schedule = $schedule;
            $plan->save();
        }

        Schema::dropIfExists('workout_plan_schedule');
    }
}; 