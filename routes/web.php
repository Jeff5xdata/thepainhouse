<?php

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
use App\Http\Controllers\ShareLinkController;
use Illuminate\Support\Facades\Route;

// PWA Routes
require __DIR__.'/pwa.php';

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Route::get('/', function () {
    return redirect()->route('welcome');
});

// Public routes with rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/shared/workout-plans/{token}', [ShareLinkController::class, 'show'])->name('workout-plans.shared');
});

Route::middleware(['auth', 'verified', 'throttle:120,1'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

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
    
    Route::get('/nutrition', Nutrition::class)->name('nutrition');

    // Trainer Request Routes
    Route::get('/trainer/request', TrainerRequestForm::class)->name('trainer.request');
    
    // Messaging Routes
    Route::get('/messaging/{conversation?}', MessagingCenter::class)->name('messaging.center');
    
    // Trainer Dashboard Routes
    Route::get('/trainer/dashboard', TrainerDashboard::class)->name('trainer.dashboard');
    Route::get('/trainer/client/{clientId}/progress', ClientProgress::class)->name('trainer.client.progress');
    Route::get('/trainer/client/{clientId}/workout-history', function($clientId) {
        return redirect()->route('workout.history', ['client' => $clientId]);
    })->name('trainer.client.workout.history');
    Route::get('/trainer/client/{clientId}/nutrition-history', function($clientId) {
        return redirect()->route('nutrition', ['client' => $clientId]);
    })->name('trainer.client.nutrition.history');
});

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rate limit sharing operations more strictly
    Route::middleware(['throttle:10,1'])->group(function () {
        Route::post('/workout-plans/{workoutPlan}/share', [ShareLinkController::class, 'generateLink'])->name('workout-plans.share');
        Route::post('/workout-plans/{workoutPlan}/share-emails', [ShareLinkController::class, 'shareEmails'])->name('workout-plans.share-emails');
    });
});

Route::view('/offline', 'offline')->name('offline');

require __DIR__.'/auth.php';