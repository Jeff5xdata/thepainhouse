<?php

// Import necessary controllers and Livewire components for the application
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutBackupController;
use App\Http\Controllers\TrainerRequestController;
use App\Http\Controllers\MessageController;
use App\Livewire\Dashboard;
use App\Livewire\WorkoutPlanner;
use App\Livewire\WorkoutSession;
use App\Livewire\WorkoutProgress;
use App\Livewire\ExerciseForm;
use App\Livewire\ExerciseList;
use App\Livewire\WorkoutPlanView;
use App\Livewire\WorkoutHistory;
use App\Livewire\WorkoutSessionDetails;
use App\Livewire\WorkoutSettings;
use App\Livewire\WorkoutBackup;
use App\Livewire\Nutrition;
use App\Livewire\TrainerRequestForm;
use App\Livewire\MessagingCenter;
use App\Livewire\TrainerDashboard;
use App\Livewire\ClientProgress;
use App\Livewire\ExerciseManager;
use App\Livewire\WeightTracker;
use App\Livewire\BodyMeasurementTracker;
use App\Livewire\ProgressCharts;
use App\Http\Controllers\ShareLinkController;
use App\Http\Controllers\FoodItemController;
use Illuminate\Support\Facades\Route;

// Include PWA (Progressive Web App) routes for offline functionality
require __DIR__.'/pwa.php';

// Welcome page route - displays the landing page for new users
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// Root route - redirects to welcome page
Route::get('/', function () {
    return redirect()->route('welcome');
});

// Public routes with rate limiting (60 requests per minute)
// These routes are accessible without authentication but have rate limiting for security
Route::middleware(['throttle:60,1'])->group(function () {
    // Route for viewing shared workout plans via token
    Route::get('/shared/workout-plans/{token}', [ShareLinkController::class, 'show'])->name('workout-plans.shared');
});

// Protected routes requiring authentication, email verification, and rate limiting (120 requests per minute)
Route::middleware(['auth', 'verified', 'throttle:120,1'])->group(function () {
    // Main dashboard route
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Workout planning and management routes
    Route::get('/workout/planner/{week?}/{day?}', WorkoutPlanner::class)->name('workout.planner');
    Route::get('/workout/session/{workoutSession}', WorkoutSession::class)->name('workout.session.edit');
    Route::get('/workout/session', WorkoutSession::class)->name('workout.session');
    Route::get('/workout/plan/{id}', WorkoutPlanView::class)->name('workout.plan');
    Route::get('/workout/exercises', ExerciseManager::class)->name('workout.exercises');
    Route::get('/workout/progress', WorkoutProgress::class)->name('workout.progress');
    Route::get('/workout/history', WorkoutHistory::class)->name('workout.history');
    Route::get('/workout/history/{workoutSession}', WorkoutSessionDetails::class)->name('workout.history.details');
    Route::get('/workout/settings', WorkoutSettings::class)->name('workout.settings');
    Route::get('/workout/backup', WorkoutBackup::class)->name('workout.backup');
    Route::get('/workout/backup/download', [WorkoutBackupController::class, 'downloadBackup'])->name('workout.backup.download');
    Route::get('/workout/ai-generator', \App\Livewire\AiWorkoutGenerator::class)->name('workout.ai-generator');
    
    // Nutrition tracking route
    Route::get('/nutrition', Nutrition::class)->name('nutrition');
    
    // Weight and body measurement tracking routes
    Route::get('/weight-tracker', WeightTracker::class)->name('weight.tracker');
Route::get('/body-measurements', BodyMeasurementTracker::class)->name('body.measurements');
Route::get('/progress-charts', ProgressCharts::class)->name('progress.charts');

// Trainer routes for client weight and body measurements
Route::get('/trainer/client/{clientId}/weight-tracker', \App\Livewire\TrainerWeightTracker::class)->name('trainer.client.weight');
Route::get('/trainer/client/{clientId}/body-measurements', \App\Livewire\TrainerBodyMeasurementTracker::class)->name('trainer.client.body');
Route::get('/trainer/client/{clientId}/progress-charts', \App\Livewire\TrainerProgressCharts::class)->name('trainer.client.charts');
    
    // Food items management routes
    Route::get('/food-items', [FoodItemController::class, 'index'])->name('food-items.index');
    Route::get('/food-items/{foodItem}', [FoodItemController::class, 'show'])->name('food-items.show');

    // Trainer request route for users to request trainer services
    Route::get('/trainer/request', TrainerRequestForm::class)->name('trainer.request');
    
    // Messaging system routes
    Route::get('/messaging/{conversation?}', MessagingCenter::class)->name('messaging.center');
    
    // Trainer dashboard and client management routes
    Route::get('/trainer/dashboard', TrainerDashboard::class)->name('trainer.dashboard');
    Route::get('/trainer/client/{clientId}/progress', ClientProgress::class)->name('trainer.client.progress');
    // Redirect routes for trainer to view client workout history
    Route::get('/trainer/client/{clientId}/workout-history', function($clientId) {
        return redirect()->route('workout.history', ['client' => $clientId]);
    })->name('trainer.client.workout.history');
    // Redirect routes for trainer to view client nutrition history
    Route::get('/trainer/client/{clientId}/nutrition-history', function($clientId) {
        return redirect()->route('nutrition', ['client' => $clientId]);
    })->name('trainer.client.nutrition.history');
});

// Profile management routes with authentication and rate limiting (60 requests per minute)
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Strictly rate-limited sharing operations (10 requests per minute)
    // These routes handle workout plan sharing which could be resource-intensive
    Route::middleware(['throttle:10,1'])->group(function () {
        // Generate shareable link for workout plans
        Route::post('/workout-plans/{workoutPlan}/share', [ShareLinkController::class, 'generateLink'])->name('workout-plans.share');
        // Share workout plans via email
        Route::post('/workout-plans/{workoutPlan}/share-emails', [ShareLinkController::class, 'shareEmails'])->name('workout-plans.share-emails');
    });
});

// Offline page route for PWA functionality
Route::view('/offline', 'offline')->name('offline');

// Include authentication routes (login, register, password reset, etc.)
require __DIR__.'/auth.php';

Route::get('/test-chart', function () {
    return view('test-chart');
})->name('test.chart');