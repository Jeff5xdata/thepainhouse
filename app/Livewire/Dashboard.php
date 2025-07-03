<?php

namespace App\Livewire;

// Import necessary classes for Livewire component and data models
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\WorkoutSession;
use App\Models\WorkoutPlan;
use Carbon\Carbon;

/**
 * Dashboard Component
 * 
 * This Livewire component displays the main dashboard for authenticated users.
 * It shows workout statistics, recent sessions, workout plans, and calculates
 * various metrics like workout streaks and total weight lifted.
 */
#[Layout('layouts.navigation')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * Calculate the current workout streak for the user
     * 
     * A streak is the number of consecutive days the user has worked out.
     * The streak continues if the user works out today or yesterday.
     * 
     * @param \Illuminate\Database\Eloquent\Collection $sessions - Collection of workout sessions
     * @return int - The current streak count
     */
    private function calculateStreak($sessions)
    {
        // Return 0 if no sessions exist
        if ($sessions->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::today();
        
        // Filter out sessions without a valid date to prevent errors
        $validSessions = $sessions->filter(function($session) {
            return $session->date !== null;
        });
        
        // Return 0 if no valid sessions exist
        if ($validSessions->isEmpty()) {
            return 0;
        }
        
        // Get the most recent workout date
        $lastWorkout = $validSessions->first()->date->startOfDay();

        // If the last workout was more than 1 day ago, streak is broken
        if ($lastWorkout->lt($today->copy()->subDay())) {
            return 0;
        }

        // Count consecutive days by checking each session
        foreach ($validSessions as $session) {
            $sessionDate = $session->date->startOfDay();
            
            // Check if this session is within 1 day of the previous session
            if ($lastWorkout->diffInDays($sessionDate) <= 1) {
                $streak++;
                $lastWorkout = $sessionDate;
            } else {
                // Streak is broken, stop counting
                break;
            }
        }

        return $streak;
    }

    /**
     * Render the dashboard view with workout statistics and data
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Get recent workout sessions with related data for the authenticated user
        // Include workout plan and exercise sets with exercises for complete data
        $recentSessions = WorkoutSession::with(['workoutPlan', 'exerciseSets.exercise'])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(30) // Get more sessions for accurate streak calculation
            ->get();

        // Get recent workout plans created by the user
        $workoutPlans = WorkoutPlan::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        // Calculate total weight lifted across all workout sessions
        // This uses proper Eloquent methods for efficient database queries
        $totalWeight = WorkoutSession::where('user_id', auth()->id())
            ->with('exerciseSets')
            ->get()
            ->sum(function ($session) {
                return $session->exerciseSets
                    ->where('is_warmup', false) // Only count working sets, exclude warmup sets
                    ->sum(function ($set) {
                        // Only count sets with valid weight and reps (both > 0)
                        if ($set->weight && $set->reps && $set->weight > 0 && $set->reps > 0) {
                            return $set->weight * $set->reps; // Calculate total weight for this set
                        }
                        return 0;
                    });
            });

        // Return the dashboard view with all calculated data
        return view('livewire.dashboard', [
            'recentSessions' => $recentSessions->take(5), // Only pass 5 sessions for display
            'workoutPlans' => $workoutPlans,
            'activeStreak' => $this->calculateStreak($recentSessions),
            'totalWorkouts' => WorkoutSession::where('user_id', auth()->id())->count(),
            'totalWeight' => $totalWeight,
        ]);
    }
} 