<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\FoodLog;
use App\Models\WeightMeasurement;
use App\Models\BodyMeasurement;
use Carbon\Carbon;

class TrainerDashboard extends Component
{
    public $clients = [];
    public $selectedClient = null;
    public $clientStats = [];
    public $recentWorkouts = [];
    public $recentNutrition = [];
    public $recentWeightMeasurements = [];
    public $recentBodyMeasurements = [];
    public $weightStats = [];
    public $bodyMeasurementStats = [];

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
            'weightMeasurements' => function ($query) {
                $query->latest()->limit(5);
            },
            'bodyMeasurements' => function ($query) {
                $query->latest()->limit(5);
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

        $this->recentWeightMeasurements = $client->weightMeasurements()
            ->latest()
            ->limit(5)
            ->get();

        $this->recentBodyMeasurements = $client->bodyMeasurements()
            ->latest()
            ->limit(5)
            ->get();

        // Weight statistics
        $totalWeightMeasurements = $client->weightMeasurements()->count();
        $latestWeight = $client->weightMeasurements()->latest()->first();
        $weightChange = null;
        $currentWeight = null;
        
        if ($latestWeight) {
            $currentWeight = $latestWeight->weight_in_kg;
            $firstWeight = $client->weightMeasurements()->oldest()->first();
            if ($firstWeight && $firstWeight->id !== $latestWeight->id) {
                $weightChange = $currentWeight - $firstWeight->weight_in_kg;
            }
        }

        // Body measurement statistics
        $totalBodyMeasurements = $client->bodyMeasurements()->count();
        $latestBodyMeasurement = $client->bodyMeasurements()->latest()->first();
        $currentBMI = $latestBodyMeasurement ? $latestBodyMeasurement->bmi : null;

        $this->clientStats = [
            'total_workouts' => $totalWorkouts,
            'week_workouts' => $weekWorkouts,
            'month_workouts' => $monthWorkouts,
            'total_food_logs' => $totalFoodLogs,
            'week_food_logs' => $weekFoodLogs,
            'total_weight_measurements' => $totalWeightMeasurements,
            'current_weight' => $currentWeight,
            'weight_change' => $weightChange,
            'total_body_measurements' => $totalBodyMeasurements,
            'current_bmi' => $currentBMI,
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

    public function viewClientWeightTracker($clientId)
    {
        return redirect()->route('trainer.client.weight', ['clientId' => $clientId]);
    }

    public function viewClientBodyMeasurements($clientId)
    {
        return redirect()->route('trainer.client.body', ['clientId' => $clientId]);
    }

    public function viewClientProgressCharts($clientId)
    {
        return redirect()->route('trainer.client.charts', ['clientId' => $clientId]);
    }

    public function render()
    {
        return view('livewire.trainer-dashboard');
    }
} 