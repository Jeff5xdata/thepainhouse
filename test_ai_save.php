<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Livewire\WorkoutPlanner;
use App\Models\User;
use App\Models\WorkoutPlan;
use Livewire\Livewire;

echo "Testing AI Workout Generation with Auto-Save\n";
echo "===========================================\n\n";

// Get a user (assuming user ID 1 exists)
$user = User::find(1);
if (!$user) {
    echo "Error: No user found with ID 1\n";
    exit(1);
}

// Authenticate as the user
auth()->login($user);

echo "1. Authenticated as user: {$user->name}\n";

// Count existing workout plans
$existingPlans = WorkoutPlan::where('user_id', $user->id)->count();
echo "2. Existing workout plans: {$existingPlans}\n";

// Create a Livewire component instance
$component = Livewire::test(WorkoutPlanner::class);

echo "3. Created WorkoutPlanner component\n";

// Set AI workout preferences
$aiPreferences = [
    'goal' => 'strength',
    'experience_level' => 'intermediate',
    'days_per_week' => 3,
    'focus_areas' => ['chest', 'back', 'legs'],
    'equipment_available' => ['barbell', 'bodyweight'],
    'time_per_workout' => 60
];

echo "4. Setting AI preferences:\n";
foreach ($aiPreferences as $key => $value) {
    echo "   {$key}: " . (is_array($value) ? implode(', ', $value) : $value) . "\n";
}

// Set the preferences on the component
$component->set('aiWorkoutPreferences', $aiPreferences);

echo "\n5. Calling generateAiWorkout() method...\n";

// Call the generateAiWorkout method
$component->call('generateAiWorkout');

echo "6. Method called successfully\n";

// Check if a new workout plan was created
$newPlans = WorkoutPlan::where('user_id', $user->id)->count();
echo "7. Workout plans after generation: {$newPlans}\n";

if ($newPlans > $existingPlans) {
    echo "8. ✅ SUCCESS: New workout plan was created!\n";
    
    // Get the latest workout plan
    $latestPlan = WorkoutPlan::where('user_id', $user->id)->latest()->first();
    echo "9. Latest workout plan:\n";
    echo "   - Name: {$latestPlan->name}\n";
    echo "   - Description: {$latestPlan->description}\n";
    echo "   - Weeks duration: {$latestPlan->weeks_duration}\n";
    echo "   - Schedule items: " . $latestPlan->scheduleItems()->count() . "\n";
    
    // Show schedule items
    echo "10. Schedule items:\n";
    foreach ($latestPlan->scheduleItems as $item) {
        echo "    - Week {$item->week_number}, Day {$item->day_of_week}: Exercise ID {$item->exercise_id}\n";
    }
} else {
    echo "8. ❌ FAILED: No new workout plan was created\n";
}

// Check for any flash messages
$session = session();
if ($session->has('message')) {
    echo "\n11. Success message: " . $session->get('message') . "\n";
}
if ($session->has('error')) {
    echo "\n11. Error message: " . $session->get('error') . "\n";
}

echo "\nTest completed!\n"; 