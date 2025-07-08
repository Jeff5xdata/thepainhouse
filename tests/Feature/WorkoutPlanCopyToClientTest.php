<?php

use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use App\Models\Exercise;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('trainer can copy workout to client', function () {
    // Create trainer and client
    $trainer = User::create([
        'name' => 'Test Trainer',
        'email' => 'trainer@test.com',
        'password' => bcrypt('password'),
        'is_trainer' => true
    ]);
    
    $client = User::create([
        'name' => 'Test Client',
        'email' => 'client@test.com',
        'password' => bcrypt('password'),
        'my_trainer' => $trainer->id
    ]);
    
    // Create trainer's workout plan
    $trainerPlan = WorkoutPlan::create([
        'user_id' => $trainer->id,
        'name' => 'Trainer Plan',
        'description' => 'Test plan',
        'weeks_duration' => 4,
        'is_active' => true
    ]);
    
    // Create exercise
    $exercise = Exercise::create([
        'name' => 'Test Exercise',
        'description' => 'Test exercise description',
        'category' => 'Strength',
        'equipment' => 'Barbell',
        'user_id' => $trainer->id
    ]);
    
    // Create workout schedule for trainer
    $scheduleItem = WorkoutPlanSchedule::create([
        'workout_plan_id' => $trainerPlan->id,
        'exercise_id' => $exercise->id,
        'week_number' => 1,
        'day_of_week' => 'monday',
        'order_in_day' => 1,
        'sets' => 3,
        'reps' => 10,
        'is_time_based' => false,
        'has_warmup' => false
    ]);
    
    // Act as trainer
    $this->actingAs($trainer);
    
    // Test the copy functionality
    Livewire::test(\App\Livewire\WorkoutPlanView::class)
        ->set('sourceDay', 'monday')
        ->set('targetWeek', 2)
        ->set('targetDay', 'tuesday')
        ->set('selectedClientId', $client->id)
        ->call('copyWorkout')
        ->assertDispatched('notify', [
            'type' => 'success',
            'message' => 'Workout copied to client successfully'
        ]);
    
    // Verify client has a workout plan
    $this->assertDatabaseHas('workout_plans', [
        'user_id' => $client->id,
        'name' => 'Trainer Plan (Copied)'
    ]);
    
    // Verify workout was copied to client's plan
    $clientPlan = $client->workoutPlans()->first();
    $this->assertDatabaseHas('workout_plan_schedule', [
        'workout_plan_id' => $clientPlan->id,
        'exercise_id' => $exercise->id,
        'week_number' => 2,
        'day_of_week' => 'tuesday',
        'sets' => 3,
        'reps' => 10
    ]);
    
    // Verify notification was sent
    $this->assertDatabaseHas('messages', [
        'sender_id' => $trainer->id,
        'recipient_id' => $client->id,
        'subject' => 'New Workout Assigned'
    ]);
});

test('non-trainer cannot copy workout to client', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'user@test.com',
        'password' => bcrypt('password'),
        'is_trainer' => false
    ]);
    
    $client = User::create([
        'name' => 'Test Client',
        'email' => 'client@test.com',
        'password' => bcrypt('password')
    ]);
    
    $this->actingAs($user);
    
    // Non-trainers shouldn't have the copy to client functionality available
    $component = Livewire::test(\App\Livewire\WorkoutPlanView::class);
    $this->assertFalse($component->get('isTrainer'));
    $this->assertEmpty($component->get('clients'));
});

test('trainer can only copy to their own clients', function () {
    $trainer = User::create([
        'name' => 'Test Trainer',
        'email' => 'trainer@test.com',
        'password' => bcrypt('password'),
        'is_trainer' => true
    ]);
    
    $otherTrainer = User::create([
        'name' => 'Other Trainer',
        'email' => 'other@test.com',
        'password' => bcrypt('password'),
        'is_trainer' => true
    ]);
    
    $otherClient = User::create([
        'name' => 'Other Client',
        'email' => 'otherclient@test.com',
        'password' => bcrypt('password'),
        'my_trainer' => $otherTrainer->id
    ]);
    
    $this->actingAs($trainer);
    
    // The trainer should not see the other client in their clients list
    $component = Livewire::test(\App\Livewire\WorkoutPlanView::class);
    $this->assertTrue($component->get('isTrainer'));
    $this->assertEmpty($component->get('clients'));
}); 