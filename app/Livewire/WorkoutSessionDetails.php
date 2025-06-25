<?php

namespace App\Livewire;

use App\Models\WorkoutSession;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class WorkoutSessionDetails extends Component
{
    public $session;
    public $exerciseGroups;
    public $totalVolume = 0;
    public $totalExercises = 0;
    public $totalSets = 0;
    public $totalReps = 0;

    public function mount(WorkoutSession $workoutSession)
    {
        $this->session = $workoutSession->load(['workoutPlan', 'exerciseSets.exercise']);
        
        // Group exercise sets by exercise
        $this->exerciseGroups = $this->session->exerciseSets
            ->groupBy('exercise_id')
            ->map(function($sets) {
                $exercise = $sets->first()->exercise;
                $workingSets = $sets->where('is_warmup', false);
                
                return [
                    'exercise' => $exercise,
                    'warmup_sets' => $sets->where('is_warmup', true)->values(),
                    'working_sets' => $workingSets->values(),
                    'max_weight' => $workingSets->whereNull('time_in_seconds')->max('weight'),
                    'total_reps' => $workingSets->whereNull('time_in_seconds')->sum('reps'),
                    'total_time' => $workingSets->whereNotNull('time_in_seconds')->sum('time_in_seconds'),
                    'total_volume' => $workingSets->whereNull('time_in_seconds')->sum(function($set) {
                        return $set->weight * $set->reps;
                    }),
                ];
            });

        // Calculate session totals
        $this->totalExercises = $this->exerciseGroups->count();
        $this->totalSets = $this->session->exerciseSets->where('is_warmup', false)->count();
        $this->totalReps = $this->session->exerciseSets->where('is_warmup', false)->whereNull('time_in_seconds')->sum('reps');
        $this->totalVolume = $this->session->exerciseSets
            ->where('is_warmup', false)
            ->whereNull('time_in_seconds')
            ->sum(function($set) {
                return $set->weight * $set->reps;
            });
    }

    public function render()
    {
        return view('livewire.workout-session-details');
    }
} 