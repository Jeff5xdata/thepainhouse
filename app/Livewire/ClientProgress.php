<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\FoodLog;
use Carbon\Carbon;

class ClientProgress extends Component
{
    public $client;
    public $currentWorkout = null;
    public $weekNutrition = [];
    public $progressStats = [];
    public $workoutHistory = [];
    public $nutritionHistory = [];

    public function mount($clientId)
    {
        $user = auth()->user();
        
        // Check if user is a trainer and has access to this client
        if (!$user->isTrainer()) {
            abort(403, 'Unauthorized access.');
        }

        $this->client = User::with([
            'workoutSessions.workoutPlan',
            'foodLogs.foodItem',
            'workoutPlans',
            'workoutSettings'
        ])->find($clientId);

        if (!$this->client || $this->client->my_trainer !== $user->id) {
            abort(403, 'Unauthorized access to this client.');
        }

        $this->loadCurrentWorkout();
        $this->loadWeekNutrition();
        $this->loadProgressStats();
        $this->loadHistory();
    }

    public function loadCurrentWorkout()
    {
        $this->currentWorkout = $this->client->workoutSessions()
            ->with('workoutPlan')
            ->where('completed', true)
            ->latest()
            ->first();
    }

    public function loadWeekNutrition()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $this->weekNutrition = $this->client->foodLogs()
            ->with('foodItem')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            });
    }

    public function loadProgressStats()
    {
        $now = Carbon::now();
        $monthAgo = $now->copy()->subMonth();
        $threeMonthsAgo = $now->copy()->subMonths(3);

        // Workout progress
        $totalWorkouts = $this->client->workoutSessions()->count();
        $monthWorkouts = $this->client->workoutSessions()
            ->where('created_at', '>=', $monthAgo)
            ->count();
        $threeMonthWorkouts = $this->client->workoutSessions()
            ->where('created_at', '>=', $threeMonthsAgo)
            ->count();

        // Nutrition progress
        $totalFoodLogs = $this->client->foodLogs()->count();
        $monthFoodLogs = $this->client->foodLogs()
            ->where('created_at', '>=', $monthAgo)
            ->count();

        // Calculate average calories per day for the last week
        $weekStart = $now->copy()->subWeek();
        $weeklyCalories = $this->client->foodLogs()
            ->where('created_at', '>=', $weekStart)
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            })
            ->map(function ($dayLogs) {
                return $dayLogs->sum('calories');
            });

        $avgDailyCalories = $weeklyCalories->count() > 0 ? $weeklyCalories->avg() : 0;

        $this->progressStats = [
            'total_workouts' => $totalWorkouts,
            'month_workouts' => $monthWorkouts,
            'three_month_workouts' => $threeMonthWorkouts,
            'total_food_logs' => $totalFoodLogs,
            'month_food_logs' => $monthFoodLogs,
            'avg_daily_calories' => round($avgDailyCalories),
            'workout_consistency' => $monthWorkouts > 0 ? round(($monthWorkouts / 30) * 100, 1) : 0,
        ];
    }

    public function loadHistory()
    {
        $this->workoutHistory = $this->client->workoutSessions()
            ->with('workoutPlan')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $this->nutritionHistory = $this->client->foodLogs()
            ->with('foodItem')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    public function sendMessage()
    {
        return redirect()->route('messaging.center', ['conversation' => $this->client->id]);
    }

    public function createWorkout()
    {
        return redirect()->route('workout.planner', ['client' => $this->client->id]);
    }

    public function viewFullWorkoutHistory()
    {
        return redirect()->route('trainer.client.workout.history', $this->client->id);
    }

    public function viewFullNutritionHistory()
    {
        return redirect()->route('trainer.client.nutrition.history', $this->client->id);
    }

    public function render()
    {
        return view('livewire.client-progress');
    }
} 