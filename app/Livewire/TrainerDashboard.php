<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\FoodLog;
use Carbon\Carbon;

class TrainerDashboard extends Component
{
    public $clients = [];
    public $selectedClient = null;
    public $clientStats = [];
    public $recentWorkouts = [];
    public $recentNutrition = [];

    public function mount()
    {
        $this->loadClients();
    }

    public function loadClients()
    {
        $user = auth()->user();
        if ($user->isTrainer()) {
            $this->clients = $user->clients()->with(['workoutSessions', 'foodLogs'])->get();
        }
    }

    public function selectClient($clientId)
    {
        $this->selectedClient = User::with([
            'workoutSessions' => function ($query) {
                $query->latest()->limit(10);
            },
            'foodLogs' => function ($query) {
                $query->latest()->limit(7);
            },
            'workoutPlans' => function ($query) {
                $query->latest();
            }
        ])->find($clientId);

        if ($this->selectedClient) {
            $this->loadClientStats();
        }
    }

    public function loadClientStats()
    {
        if (!$this->selectedClient) return;

        $client = $this->selectedClient;
        $now = Carbon::now();
        $weekAgo = $now->copy()->subWeek();
        $monthAgo = $now->copy()->subMonth();

        // Workout statistics
        $totalWorkouts = $client->workoutSessions()->count();
        $weekWorkouts = $client->workoutSessions()
            ->where('created_at', '>=', $weekAgo)
            ->count();
        $monthWorkouts = $client->workoutSessions()
            ->where('created_at', '>=', $monthAgo)
            ->count();

        // Nutrition statistics
        $totalFoodLogs = $client->foodLogs()->count();
        $weekFoodLogs = $client->foodLogs()
            ->where('created_at', '>=', $weekAgo)
            ->count();

        // Recent activity
        $this->recentWorkouts = $client->workoutSessions()
            ->with('workoutPlan')
            ->latest()
            ->limit(5)
            ->get();

        $this->recentNutrition = $client->foodLogs()
            ->latest()
            ->limit(7)
            ->get();

        $this->clientStats = [
            'total_workouts' => $totalWorkouts,
            'week_workouts' => $weekWorkouts,
            'month_workouts' => $monthWorkouts,
            'total_food_logs' => $totalFoodLogs,
            'week_food_logs' => $weekFoodLogs,
            'current_workout_plan' => $client->workoutPlans()->latest()->first(),
        ];
    }

    public function sendMessageToClient($clientId)
    {
        return redirect()->route('messaging.center', ['conversation' => $clientId]);
    }

    public function createWorkoutForClient($clientId)
    {
        return redirect()->route('workout.planner', ['client' => $clientId]);
    }

    public function viewClientProgress($clientId)
    {
        return redirect()->route('trainer.client.progress', $clientId);
    }

    public function render()
    {
        return view('livewire.trainer-dashboard');
    }
} 