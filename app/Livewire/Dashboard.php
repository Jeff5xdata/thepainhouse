<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\WorkoutSession;
use App\Models\WorkoutPlan;
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    private function calculateStreak($sessions)
    {
        if ($sessions->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::today();
        $lastWorkout = $sessions->first()->created_at->startOfDay();

        // If no workout today or yesterday, streak is 0
        if ($lastWorkout->lt($today->copy()->subDay())) {
            return 0;
        }

        // Count consecutive days
        foreach ($sessions as $session) {
            $sessionDate = $session->created_at->startOfDay();
            
            if ($lastWorkout->diffInDays($sessionDate) <= 1) {
                $streak++;
                $lastWorkout = $sessionDate;
            } else {
                break;
            }
        }

        return $streak;
    }

    public function render()
    {
        $recentSessions = WorkoutSession::with(['workoutPlan', 'exerciseSets.exercise'])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(30) // Get more sessions for accurate streak calculation
            ->get();

        $workoutPlans = WorkoutPlan::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'recentSessions' => $recentSessions->take(5), // Only pass 5 for display
            'workoutPlans' => $workoutPlans,
            'activeStreak' => $this->calculateStreak($recentSessions),
            'totalWorkouts' => WorkoutSession::where('user_id', auth()->id())->count(),
            'totalWeight' => WorkoutSession::where('user_id', auth()->id())
                ->join('exercise_sets', 'workout_sessions.id', '=', 'exercise_sets.workout_session_id')
                ->sum(\DB::raw('exercise_sets.set_number * exercise_sets.reps * exercise_sets.weight')),
        ]);
    }
} 