<?php

namespace App\Livewire;

use App\Models\WorkoutSession;
use App\Models\Exercise;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.navigation')]
class WorkoutProgress extends Component
{
    public $selectedExercise = null;
    public $timeframe = 'month';
    public $progressData = [];

    public function mount()
    {
        $this->loadProgressData();
    }

    public function updatedSelectedExercise()
    {
        $this->loadProgressData();
    }

    public function updatedTimeframe()
    {
        $this->loadProgressData();
    }

    public function loadProgressData()
    {
        if (!$this->selectedExercise) {
            return;
        }

        $startDate = match($this->timeframe) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };

        $sets = WorkoutSession::where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->whereHas('exerciseSets', function ($query) {
                $query->where('exercise_id', $this->selectedExercise)
                    ->where('completed', true);
            })
            ->with(['exerciseSets' => function ($query) {
                $query->where('exercise_id', $this->selectedExercise)
                    ->where('completed', true)
                    ->orderBy('created_at');
            }])
            ->get();

        $this->progressData = $sets->flatMap(function ($session) {
            return $session->exerciseSets->map(function ($set) use ($session) {
                return [
                    'date' => $session->completed_at->format('Y-m-d'),
                    'weight' => $set->weight,
                    'reps' => $set->reps,
                    'volume' => $set->weight * $set->reps,
                ];
            });
        })->groupBy('date')->map(function ($sets) {
            return [
                'max_weight' => $sets->max('weight'),
                'total_volume' => $sets->sum('volume'),
                'total_reps' => $sets->sum('reps'),
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.workout-progress', [
            'exercises' => Exercise::orderBy('name')->get(),
        ]);
    }
}
