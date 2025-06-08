<?php

use App\Http\Controllers\WelcomeController;

use App\Http\Controllers\ProfileController;
use App\Livewire\Dashboard;
use App\Livewire\WorkoutPlanner;
use App\Livewire\WorkoutSession;
use App\Livewire\WorkoutProgress;
use App\Livewire\ExerciseForm;
use App\Livewire\ExerciseList;
use App\Livewire\WorkoutPlanView;
use App\Livewire\WorkoutHistory;
use App\Livewire\WorkoutSessionDetails;

use App\Livewire\ExerciseManager;
use Illuminate\Support\Facades\Route;

// PWA Routes
require __DIR__.'/pwa.php';

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Route::get('/', function () {
    return redirect()->route('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/workout/planner/{week?}/{day?}', WorkoutPlanner::class)->name('workout.planner');
    Route::get('/workout/session', WorkoutSession::class)->name('workout.session');
    Route::get('/workout/plan/{id}', WorkoutPlanView::class)->name('workout.plan');
    Route::get('/workout/exercises', ExerciseManager::class)->name('workout.exercises');
    Route::get('/workout/progress', WorkoutProgress::class)->name('workout.progress');
    Route::get('/workout/history', WorkoutHistory::class)->name('workout.history');
    Route::get('/workout/history/{workoutSession}', WorkoutSessionDetails::class)->name('workout.history.details');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::view('/offline', 'offline')->name('offline');

require __DIR__.'/auth.php';