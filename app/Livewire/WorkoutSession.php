<?php

namespace App\Livewire;

use App\Models\WorkoutSession as WorkoutSessionModel;
use App\Models\ExerciseSet;
use App\Models\Exercise;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class WorkoutSession extends Component
{
    public $workoutSession;
    public $exerciseSets = [];
    public $currentExercise;
    public $weight;
    public $reps;

    public function mount(WorkoutSessionModel $workoutSession)
    {
        $this->workoutSession = $workoutSession;
        $this->loadExerciseSets();
    }

    public function loadExerciseSets()
    {
        $this->exerciseSets = $this->workoutSession->exerciseSets()
            ->with('exercise')
            ->orderBy('exercise_id')
            ->orderBy('is_warmup')
            ->orderBy('set_number')
            ->get()
            ->groupBy('exercise_id');
    }

    public function toggleSetCompletion(ExerciseSet $set)
    {
        $set->update([
            'completed' => !$set->completed,
            'weight' => $this->weight ?? $set->weight,
            'reps' => $this->reps ?? $set->reps,
        ]);

        $this->loadExerciseSets();
    }

    public function completeWorkout()
    {
        $this->workoutSession->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        $this->dispatch('workoutCompleted', $this->workoutSession->id);
        session()->flash('message', 'Workout completed successfully!');
    }

    public function render()
    {
        return view('livewire.workout-session', [
            'exercises' => Exercise::whereIn('id', array_keys($this->exerciseSets->toArray()))->get(),
        ]);
    }
}
