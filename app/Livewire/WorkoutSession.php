<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSession as WorkoutSessionModel;
use App\Models\ExerciseSet;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
class WorkoutSession extends Component
{
    public $workoutSession;
    public $exerciseSets = [];
    public $currentExercise;
    public $weight = [];
    public $reps = [];
    public $sets = [];
    public $isNewSession = true;
    public $workoutPlan;
    public $currentWeek;
    public $currentDay;
    public $sessionDate;
    public $todayExercises;
    public $lastWorkouts = [];
    public $sessionNotes;
    public $useProgression = [];
    public $showProgress = [];
    public $tempSavedData = [];

    public function mount($workoutSession = null)
    {
        if ($workoutSession) {
            $this->workoutSession = $workoutSession;
            $this->isNewSession = false;
            $this->workoutPlan = $workoutSession->workoutPlan;
            $this->currentWeek = $workoutSession->week_number;
            $this->currentDay = ucfirst($workoutSession->day_of_week);
            $this->sessionDate = $workoutSession->date->format('Y-m-d');
            $this->todayExercises = $this->workoutPlan->getScheduleForDay($workoutSession->week_number, $workoutSession->day_of_week);
            $this->loadExerciseSets();
        } else {
            // Find active workout plan
            $this->workoutPlan = WorkoutPlan::with([
                'exercises' => function($query) {
                    $query->withPivot([
                        'default_sets',
                        'default_reps',
                        'default_weight',
                        'has_warmup',
                        'warmup_sets',
                        'warmup_reps',
                        'warmup_weight_percentage'
                    ]);
                },
                'scheduleItems.exercise'
            ])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

            // If no active plan, get most recent
            if (!$this->workoutPlan) {
                $this->workoutPlan = WorkoutPlan::with([
                    'exercises',
                    'scheduleItems.exercise'
                ])
                ->where('user_id', auth()->id())
                ->latest()
                ->first();
            }

            if (!$this->workoutPlan) {
                session()->flash('error', 'No workout plan found. Please create a workout plan first.');
                $this->redirect(route('workout.planner'));
                return;
            }

            // Calculate current week
            $this->currentWeek = 1;
            $totalWeeks = $this->workoutPlan->weeks_duration;
            if ($totalWeeks > 1) {
                $startDate = Carbon::parse($this->workoutPlan->created_at)->startOfDay();
                $currentWeekNumber = Carbon::now()->startOfDay()->diffInDays($startDate) / 7;
                $currentWeekNumber = ceil($currentWeekNumber);
                $this->currentWeek = min(max(1, $currentWeekNumber), $totalWeeks);
            }

            $this->currentDay = ucfirst(strtolower(Carbon::now()->format('l')));
            $this->sessionDate = now()->format('Y-m-d');
            $this->todayExercises = $this->workoutPlan->getScheduleForDay($this->currentWeek, strtolower($this->currentDay));
            $this->loadLastWorkouts();
        }
    }

    protected function loadLastWorkouts()
    {
        $lastWorkouts = WorkoutSessionModel::with(['exerciseSets' => function($query) {
            $query->where('is_warmup', false)
                ->orderBy('set_number', 'desc')
                ->latest();
        }])
        ->where('user_id', auth()->id())
        ->where('status', 'completed')
        ->select('id', 'user_id', 'created_at')
        ->latest()
        ->take(10)
        ->get()
        ->pluck('exerciseSets')
        ->flatten()
        ->groupBy('exercise_id')
        ->map(function($sets) {
            return $sets->first();
        });

        $this->lastWorkouts = $lastWorkouts;
    }

    public function saveSet($exerciseId)
    {
        // Store the values in the component without saving to database
        if (!isset($this->weight[$exerciseId]) || !isset($this->reps[$exerciseId])) {
            session()->flash('error', 'Please enter both weight and reps before saving.');
            return;
        }

        session()->flash('message', 'Values stored. Save session to complete the workout.');
    }

    /**
     * Toggle the completion status of a set
     */
    public function toggleSetCompletion($setId)
    {
        try {
            $set = ExerciseSet::find($setId);
            if ($set) {
                $set->completed = !$set->completed;
                $set->save();

                // Store the current values in temporary storage
                if ($set->completed) {
                    $this->tempSavedData[$setId] = [
                        'exercise_id' => $set->exercise_id,
                        'set_number' => $set->set_number,
                        'is_warmup' => $set->is_warmup,
                        'weight' => $this->weight[$set->exercise_id] ?? $set->weight,
                        'reps' => $this->reps[$set->exercise_id] ?? $set->reps,
                    ];
                } else {
                    // Remove from temporary storage if uncompleted
                    unset($this->tempSavedData[$setId]);
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to toggle set completion.');
        }
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

    public function toggleProgress($exerciseId)
    {
        // Just toggle visibility of progress data
        $this->showProgress[$exerciseId] = !($this->showProgress[$exerciseId] ?? false);
    }

    public function completeWorkout()
    {
        try {
            // Validate all inputs
            $validationRules = [];
            foreach ($this->exerciseSets as $exerciseId => $sets) {
                $validationRules["weight.$exerciseId"] = 'required|numeric|min:0';
                $validationRules["reps.$exerciseId"] = 'required|numeric|min:1';
            }
            $this->validate($validationRules);

            // Check if any sets are completed
            $hasCompletedSets = false;
            foreach ($this->exerciseSets as $exerciseId => $sets) {
                foreach ($sets as $set) {
                    if ($set->completed) {
                        $hasCompletedSets = true;
                        // Store in temporary data if not already stored
                        if (!isset($this->tempSavedData[$set->id])) {
                            $this->tempSavedData[$set->id] = [
                                'exercise_id' => $exerciseId,
                                'set_number' => $set->set_number,
                                'is_warmup' => $set->is_warmup,
                                'weight' => $this->weight[$exerciseId] ?? $set->weight,
                                'reps' => $this->reps[$exerciseId] ?? $set->reps,
                            ];
                        }
                    }
                }
            }

            if (!$hasCompletedSets) {
                session()->flash('error', 'Please complete at least one set before finishing the workout.');
                return;
            }

            DB::transaction(function () {
                // Create the workout session
                $workoutSession = WorkoutSessionModel::create([
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'week' => $this->currentWeek,
                    'day' => $this->currentDay,
                    'name' => $this->workoutPlan->name . ' - ' . now()->format('Y-m-d'),
                    'workout_plan_id' => $this->workoutPlan->id,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Save all the completed sets
                foreach ($this->tempSavedData as $setId => $setData) {
                    ExerciseSet::create([
                        'workout_session_id' => $workoutSession->id,
                        'exercise_id' => $setData['exercise_id'],
                        'set_number' => $setData['set_number'],
                        'weight' => $setData['weight'],
                        'reps' => $setData['reps'],
                        'is_warmup' => $setData['is_warmup'],
                        'completed' => true,
                    ]);
                }

                // Clear the temporary data after successful save
                $this->tempSavedData = [];
            });

            session()->flash('message', 'Workout completed successfully!');
            
            // Redirect to the workout details page
            return redirect()->route('workout.history.details', ['workoutSession' => $workoutSession->id]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save workout. Please try again.');
            \Log::error('Workout completion failed: ' . $e->getMessage());
            return null;
        }
    }

    public function toggleProgression($exerciseId)
    {
        // Toggle progression for this specific exercise
        $this->useProgression[$exerciseId] = !($this->useProgression[$exerciseId] ?? false);

        // If turning on progression and we have last workout data, pre-fill the values
        if ($this->useProgression[$exerciseId] && isset($this->lastWorkouts[$exerciseId])) {
            $this->weight[$exerciseId] = $this->lastWorkouts[$exerciseId]->weight;
            $this->reps[$exerciseId] = $this->lastWorkouts[$exerciseId]->reps;
        }
    }

    public function render()
    {
        return view('livewire.workout-session', [
            'exercises' => $this->isNewSession 
                ? Exercise::whereIn('id', collect($this->todayExercises)->pluck('exercise_id'))->get()
                : Exercise::whereIn('id', array_keys($this->exerciseSets->toArray()))->get(),
        ]);
    }
}

