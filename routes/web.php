<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\WorkoutPlanner;
use App\Livewire\WorkoutSession;
use App\Livewire\WorkoutProgress;
use App\Livewire\ExerciseForm;
use App\Livewire\ExerciseList;
use App\Livewire\Dashboard;
use App\Livewire\WorkoutPlanView;
use App\Http\Controllers\WorkplanSessionController;
use App\Livewire\Nutrition;
use Illuminate\Support\Facades\Route;

// PWA Routes
require __DIR__.'/pwa.php';

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/workout/plan', WorkoutPlanner::class)->name('workout.planner');
    Route::get('/workout/view', WorkoutPlanView::class)->name('workout.view');
    Route::get('/workout/session/{workoutSession}', WorkoutSession::class)->name('workout.session');
    Route::get('/exercises', ExerciseList::class)->name('exercises.index');
    Route::get('/exercises/create', ExerciseForm::class)->name('exercises.create');
    Route::get('/progress', WorkoutProgress::class)->name('progress');
    Route::get('/nutrition', Nutrition::class)->name('nutrition');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/workplan/session', [WorkplanSessionController::class, 'show'])->name('workplan.session');
    Route::post('/workplan/session', [WorkplanSessionController::class, 'store'])->name('workplan.save-session');
    Route::get('/workplan/session/{session}', [WorkplanSessionController::class, 'view'])->name('workplan.session.view');
    Route::get('/workplan/session/{session}/edit', [WorkplanSessionController::class, 'edit'])->name('workplan.session.edit');
    Route::post('/workplan/session/{session}/update', [WorkplanSessionController::class, 'update'])->name('workplan.session.update');
});

require __DIR__.'/auth.php';
