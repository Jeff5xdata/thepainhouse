<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkoutSetting;
use App\Models\Exercise;
use App\Livewire\WorkoutPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkoutPlannerSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_workout_planner_uses_user_settings_for_new_exercises()
    {
        // Create a user with custom workout settings
        $user = User::factory()->create();
        $userSettings = WorkoutSetting::create([
            'user_id' => $user->id,
            'default_rest_timer' => 90,
            'default_warmup_sets' => 3,
            'default_warmup_reps' => 15,
            'default_warmup_weight_percentage' => 60,
            'default_work_sets' => 4,
            'default_work_reps' => 12,
        ]);

        // Create an exercise
        $exercise = Exercise::create([
            'name' => 'Test Exercise',
            'category' => 'Strength',
            'equipment' => 'Barbell',
        ]);

        // Test the WorkoutPlanner component
        Livewire::actingAs($user)
            ->test(WorkoutPlanner::class)
            ->call('addExercise', 1, 1, $exercise->id)
            ->assertSet('schedule.1.1.0.warmup_sets', 3)
            ->assertSet('schedule.1.1.0.warmup_reps', 15)
            ->assertSet('schedule.1.1.0.sets', 4)
            ->assertSet('schedule.1.1.0.reps', 12)
            ->assertSet('schedule.1.1.0.has_warmup', true);
    }

    public function test_workout_planner_uses_fallbacks_when_no_user_settings()
    {
        // Create a user without workout settings
        $user = User::factory()->create();

        // Create an exercise
        $exercise = Exercise::create([
            'name' => 'Test Exercise',
            'category' => 'Strength',
            'equipment' => 'Barbell',
        ]);

        // Test the WorkoutPlanner component
        Livewire::actingAs($user)
            ->test(WorkoutPlanner::class)
            ->call('addExercise', 1, 1, $exercise->id)
            ->assertSet('schedule.1.1.0.warmup_sets', 2) // Default fallback
            ->assertSet('schedule.1.1.0.warmup_reps', 10) // Default fallback
            ->assertSet('schedule.1.1.0.sets', 3) // Default fallback
            ->assertSet('schedule.1.1.0.reps', 10) // Default fallback
            ->assertSet('schedule.1.1.0.has_warmup', false); // No warmup sets by default
    }

    public function test_workout_planner_uses_user_settings_for_add_remove_sets()
    {
        // Create a user with custom workout settings
        $user = User::factory()->create();
        $userSettings = WorkoutSetting::create([
            'user_id' => $user->id,
            'default_rest_timer' => 60,
            'default_warmup_sets' => 2,
            'default_warmup_reps' => 10,
            'default_warmup_weight_percentage' => 50,
            'default_work_sets' => 5,
            'default_work_reps' => 8,
        ]);

        // Create an exercise
        $exercise = Exercise::create([
            'name' => 'Test Exercise',
            'category' => 'Strength',
            'equipment' => 'Barbell',
        ]);

        // Test adding and removing sets uses user settings as fallbacks
        Livewire::actingAs($user)
            ->test(WorkoutPlanner::class)
            ->call('addExercise', 1, 1, $exercise->id)
            ->call('removeSet', 1, 1, 0)
            ->assertSet('schedule.1.1.0.sets', 4) // Should be 5-1=4
            ->call('addSet', 1, 1, 0)
            ->assertSet('schedule.1.1.0.sets', 5); // Should be 4+1=5
    }
} 