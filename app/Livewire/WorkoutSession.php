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

#[Layout('layouts.navigation')]
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
    public $showNotesModal = false;
    public $currentExerciseId = null;
    public $exerciseNotes = '';
    public $setWeights = [];
    public $setReps = [];
    public $setNotes = [];
    public $exercises = [];
    public $exerciseCompletionStatus = [];

    public function mount($workoutSession = null)
    {
        try {
            // If workoutSession is a string (ID), resolve it manually
            if (is_string($workoutSession) || is_numeric($workoutSession)) {
                $workoutSession = \App\Models\WorkoutSession::where('id', $workoutSession)
                    ->where('user_id', auth()->id())
                    ->first();
            }
            
            // If no workoutSession parameter, try to get it from request
            if (!$workoutSession) {
                $sessionId = request()->route('workoutSession');
                
                if ($sessionId) {
                    $workoutSession = \App\Models\WorkoutSession::where('id', $sessionId)
                        ->where('user_id', auth()->id())
                        ->first();
                }
            }
            
            if ($workoutSession) {
                $this->workoutSession = $workoutSession;
                $this->isNewSession = false;
                $this->workoutPlan = $workoutSession->workoutPlan;
                $this->currentWeek = $workoutSession->week_number;
                $this->currentDay = ucfirst($workoutSession->day_of_week);
                $this->sessionDate = $workoutSession->date->format('Y-m-d');
                $this->todayExercises = $this->workoutPlan->getScheduleForDay($workoutSession->week_number, $workoutSession->day_of_week)
                    ->unique('exercise_id')
                    ->values();
                $this->loadExerciseSets();
                $this->loadLastWorkouts();
                $this->loadExistingNotes();
                $this->initializeSetValues();
                $this->loadExistingSetValues();
                $this->loadExercises();
                $this->loadExerciseCompletionStatus();
            } else {
                // Check if there's an existing session for today
                $existingSession = WorkoutSessionModel::where('user_id', auth()->id())
                    ->whereDate('date', now()->format('Y-m-d'))
                    ->first();

                if ($existingSession) {
                    $this->workoutSession = $existingSession;
                    $this->isNewSession = false;
                    $this->workoutPlan = $existingSession->workoutPlan;
                    $this->currentWeek = $existingSession->week_number;
                    $this->currentDay = ucfirst($existingSession->day_of_week);
                    $this->sessionDate = $existingSession->date->format('Y-m-d');
                    $this->todayExercises = $this->workoutPlan->getScheduleForDay($existingSession->week_number, $existingSession->day_of_week)
                        ->unique('exercise_id')
                        ->values();
                    $this->loadExerciseSets();
                    $this->loadLastWorkouts();
                    $this->loadExistingNotes();
                    $this->initializeSetValues();
                    $this->loadExistingSetValues();
                    $this->loadExercises();
                    $this->loadExerciseCompletionStatus();
                    return;
                }

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
                    $this->currentWeek = max(1, min($currentWeekNumber, $totalWeeks));
                }

                $this->currentDay = ucfirst(strtolower(Carbon::now()->format('l')));
                $this->sessionDate = now()->format('Y-m-d');
                $this->todayExercises = $this->workoutPlan->getScheduleForDay($this->currentWeek, strtolower($this->currentDay))
                    ->unique('exercise_id')
                    ->values();
                $this->loadLastWorkouts();
                $this->loadExistingNotes();
                $this->initializeSetValues();
                // Don't load exercises for new sessions
                $this->exercises = [];
                $this->loadExerciseCompletionStatus();
            }
        } catch (\Exception $e) {
            \Log::error('WorkoutSession mount error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to load workout session. Please try again or contact support if the problem persists.');
            
            // Only redirect to dashboard if it's a critical error
            if (str_contains($e->getMessage(), 'SQLSTATE') || str_contains($e->getMessage(), 'database')) {
                $this->redirect(route('dashboard'));
            }
        }
    }

    protected function loadExercises()
    {
        // Only load exercises for existing sessions
        if (!$this->isNewSession && $this->workoutSession) {
            $this->exercises = $this->workoutSession->exerciseSets()
                ->with('exercise')
                ->get()
                ->groupBy('exercise_id')
                ->map(function($sets) {
                    $firstSet = $sets->first();
                    return [
                        'id' => $firstSet->exercise_id,
                        'name' => $firstSet->exercise->name,
                    ];
                })
                ->values()
                ->toArray();
        } else {
            // Clear exercises for new sessions
            $this->exercises = [];
        }
    }

    protected function loadLastWorkouts()
    {
        // First query: Get distinct exercise IDs from completed sessions
        $exerciseIds = ExerciseSet::whereHas('workoutSession', function($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', 'completed');
            })
            ->where('is_warmup', false)
            ->select('exercise_id')
            ->distinct()
            ->pluck('exercise_id');

        // Second query: Get the latest set for each exercise
        $lastWorkouts = ExerciseSet::with(['exercise', 'workoutSession'])
            ->whereHas('workoutSession', function($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', 'completed');
            })
            ->where('is_warmup', false)
            ->whereIn('exercise_id', $exerciseIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('exercise_id')
            ->map(function($sets) {
                $set = $sets->first();
                return [
                    'weight' => $set->weight,
                    'reps' => $set->reps,
                    'exercise_id' => $set->exercise_id
                ];
            });

        $this->lastWorkouts = $lastWorkouts->toArray();
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

                // Update the local exercise sets array
                if (isset($this->exerciseSets[$set->exercise_id])) {
                    foreach ($this->exerciseSets[$set->exercise_id] as &$localSet) {
                        if ($localSet['id'] === $setId) {
                            $localSet['completed'] = $set->completed;
                            break;
                        }
                    }
                }

                // Store the current values in temporary storage
                if ($set->completed) {
                    $this->tempSavedData[$setId] = [
                        'exercise_id' => $set->exercise_id,
                        'set_number' => $set->set_number,
                        'is_warmup' => $set->is_warmup,
                        'weight' => $this->weight[$set->exercise_id] ?? $set->weight,
                        'reps' => $this->reps[$set->exercise_id] ?? $set->reps,
                        'notes' => $this->setNotes[$set->exercise_id] ?? $set->notes,
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
        if (!$this->workoutSession) {
            $this->exerciseSets = [];
            return;
        }
        
        $sets = $this->workoutSession->exerciseSets()
            ->with('exercise')
            ->orderBy('exercise_id')
            ->orderBy('is_warmup', 'desc')
            ->orderBy('set_number')
            ->get();

        $this->exerciseSets = $sets->groupBy('exercise_id')
            ->map(function($exerciseSets) {
                return $exerciseSets->map(function($set) {
                    return [
                        'id' => $set->id,
                        'set_number' => $set->set_number,
                        'weight' => $set->weight,
                        'reps' => $set->reps,
                        'time_in_seconds' => $set->time_in_seconds,
                        'is_warmup' => $set->is_warmup,
                        'completed' => $set->completed,
                        'notes' => $set->notes,
                    ];
                })->toArray();
            })->toArray();
    }

    public function toggleProgress($exerciseId)
    {
        $this->showProgress[$exerciseId] = !($this->showProgress[$exerciseId] ?? false);
    }

    public function completeWorkout()
    {
        try {
            DB::beginTransaction();

            if ($this->isNewSession) {
                // Create new workout session
                $this->workoutSession = WorkoutSessionModel::create([
                    'user_id' => auth()->id(),
                    'workout_plan_id' => $this->workoutPlan->id,
                    'name' => 'Workout Session - ' . now()->format('M j, Y'),
                    'date' => $this->sessionDate,
                    'week_number' => $this->currentWeek,
                    'day_of_week' => strtolower($this->currentDay),
                    'status' => 'completed',
                    'notes' => $this->sessionNotes,
                    'completed_at' => now(),
                ]);

                // Save exercise sets
                $this->saveExerciseSets();
                
                session()->flash('message', 'Workout completed successfully!');
            } else {
                // Update existing session
                $this->workoutSession->update([
                    'status' => 'completed',
                    'notes' => $this->sessionNotes,
                ]);

                // Update exercise sets
                $this->updateExerciseSets();
                
                session()->flash('message', 'Workout updated successfully!');
            }

            DB::commit();

            $this->redirect(route('workout.history'));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save workout: ' . $e->getMessage());
        }
    }

    protected function saveExerciseSets()
    {
        $setsToCreate = [];
        
        foreach ($this->todayExercises as $scheduleItem) {
            $exerciseId = $scheduleItem->exercise_id;
            $isTimeBased = $scheduleItem->is_time_based ?? false;
            
            // Prepare warmup sets
            if ($scheduleItem->has_warmup) {
                for ($i = 1; $i <= $scheduleItem->warmup_sets; $i++) {
                    $weight = $isTimeBased ? null : ($this->setWeights[$exerciseId]['warmup'][$i] ?? 0);
                    $weight = $weight ?: 0; // Convert null/empty to 0
                    $reps = $isTimeBased ? 0 : ($this->setReps[$exerciseId]['warmup'][$i] ?? $scheduleItem->warmup_reps);
                    $timeInSeconds = $isTimeBased ? ($this->setReps[$exerciseId]['warmup'][$i] ?? $scheduleItem->warmup_time_in_seconds) : null;
                    
                    $setsToCreate[] = [
                        'workout_session_id' => $this->workoutSession->id,
                        'exercise_id' => $exerciseId,
                        'set_number' => $i,
                        'weight' => $weight,
                        'reps' => $reps,
                        'time_in_seconds' => $timeInSeconds,
                        'is_warmup' => true,
                        'completed' => true,
                        'notes' => $this->setNotes[$exerciseId]['warmup'][$i] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Prepare working sets
            for ($i = 1; $i <= $scheduleItem->sets; $i++) {
                $weight = $isTimeBased ? null : ($this->setWeights[$exerciseId]['working'][$i] ?? 0);
                $weight = $weight ?: 0; // Convert null/empty to 0
                $reps = $isTimeBased ? 0 : ($this->setReps[$exerciseId]['working'][$i] ?? $scheduleItem->reps);
                $timeInSeconds = $isTimeBased ? ($this->setReps[$exerciseId]['working'][$i] ?? $scheduleItem->time_in_seconds) : null;
                
                $setsToCreate[] = [
                    'workout_session_id' => $this->workoutSession->id,
                    'exercise_id' => $exerciseId,
                    'set_number' => $i,
                    'weight' => $weight,
                    'reps' => $reps,
                    'time_in_seconds' => $timeInSeconds,
                    'is_warmup' => false,
                    'completed' => true,
                    'notes' => $this->setNotes[$exerciseId]['working'][$i] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Batch insert all sets at once
        if (!empty($setsToCreate)) {
            ExerciseSet::insert($setsToCreate);
        }
    }

    protected function updateExerciseSets()
    {
        // Update existing sets with current form values
        foreach ($this->todayExercises as $scheduleItem) {
            $exerciseId = $scheduleItem->exercise_id;
            $isTimeBased = $scheduleItem->is_time_based ?? false;
            
            // Update warmup sets
            if ($scheduleItem->has_warmup) {
                for ($i = 1; $i <= $scheduleItem->warmup_sets; $i++) {
                    $set = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                        ->where('exercise_id', $exerciseId)
                        ->where('is_warmup', true)
                        ->where('set_number', $i)
                        ->first();
                        
                    if ($set) {
                        $weight = $isTimeBased ? null : ($this->setWeights[$exerciseId]['warmup'][$i] ?? 0);
                        $weight = $weight ?: 0; // Convert null/empty to 0
                        $reps = $isTimeBased ? 0 : ($this->setReps[$exerciseId]['warmup'][$i] ?? $scheduleItem->warmup_reps);
                        $timeInSeconds = $isTimeBased ? ($this->setReps[$exerciseId]['warmup'][$i] ?? $scheduleItem->warmup_time_in_seconds) : null;
                        
                        $set->update([
                            'weight' => $weight,
                            'reps' => $reps,
                            'time_in_seconds' => $timeInSeconds,
                            'notes' => $this->setNotes[$exerciseId]['warmup'][$i] ?? null,
                        ]);
                    }
                }
            }
            
            // Update working sets
            for ($i = 1; $i <= $scheduleItem->sets; $i++) {
                $set = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                    ->where('exercise_id', $exerciseId)
                    ->where('is_warmup', false)
                    ->where('set_number', $i)
                    ->first();
                    
                if ($set) {
                    $weight = $isTimeBased ? null : ($this->setWeights[$exerciseId]['working'][$i] ?? 0);
                    $weight = $weight ?: 0; // Convert null/empty to 0
                    $reps = $isTimeBased ? 0 : ($this->setReps[$exerciseId]['working'][$i] ?? $scheduleItem->reps);
                    $timeInSeconds = $isTimeBased ? ($this->setReps[$exerciseId]['working'][$i] ?? $scheduleItem->time_in_seconds) : null;
                    
                    $set->update([
                        'weight' => $weight,
                        'reps' => $reps,
                        'time_in_seconds' => $timeInSeconds,
                        'notes' => $this->setNotes[$exerciseId]['working'][$i] ?? null,
                    ]);
                }
            }
        }
    }

    public function toggleProgression($exerciseId)
    {
        $this->useProgression[$exerciseId] = !($this->useProgression[$exerciseId] ?? false);
    }

    public function completeExercise($exerciseId)
    {
        $this->currentExerciseId = $exerciseId;
        $this->showNotesModal = true;
    }

    public function saveExerciseCompletion()
    {
        \Log::info('saveExerciseCompletion called');
        try {
            if ($this->currentExerciseId) {
                \Log::info('Processing exercise completion for ID: ' . $this->currentExerciseId);
                
                // Ensure we have a workout session
                if (!$this->workoutSession) {
                    \Log::info('Creating new workout session');
                    // Create a new workout session if it doesn't exist
                    $this->workoutSession = WorkoutSessionModel::create([
                        'user_id' => auth()->id(),
                        'workout_plan_id' => $this->workoutPlan->id,
                        'name' => 'Workout Session - ' . now()->format('M j, Y'),
                        'date' => $this->sessionDate,
                        'week_number' => $this->currentWeek,
                        'day_of_week' => strtolower($this->currentDay),
                        'status' => 'in_progress',
                        'notes' => $this->sessionNotes,
                    ]);
                    
                    // Create exercise sets for this exercise
                    $this->createExerciseSets($this->currentExerciseId);
                } else {
                    \Log::info('Using existing workout session: ' . $this->workoutSession->id);
                    // Check if exercise sets exist for this exercise
                    $existingSets = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                        ->where('exercise_id', $this->currentExerciseId)
                        ->count();
                    
                    if ($existingSets === 0) {
                        \Log::info('Creating exercise sets for exercise: ' . $this->currentExerciseId);
                        // Create exercise sets if they don't exist
                        $this->createExerciseSets($this->currentExerciseId);
                    }
                }
                
                // Save the form data (weights, reps, notes) for this exercise
                $this->saveExerciseData($this->currentExerciseId);
                
                // Mark all sets for this exercise as completed
                if (isset($this->exerciseSets[$this->currentExerciseId])) {
                    foreach ($this->exerciseSets[$this->currentExerciseId] as &$set) {
                        $set['completed'] = true;
                    }
                }
                
                // Update the exercise sets in the database to mark them as completed
                \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                    ->where('exercise_id', $this->currentExerciseId)
                    ->update(['completed' => true]);
                
                $this->exerciseNotes = '';
                $this->showNotesModal = false;
                $this->currentExerciseId = null;
                
                \Log::info('Exercise completion saved successfully');
                session()->flash('message', 'Exercise completed successfully!');
            }
        } catch (\Exception $e) {
            \Log::error('Error in saveExerciseCompletion: ' . $e->getMessage());
            session()->flash('error', 'Failed to complete exercise: ' . $e->getMessage());
        }
    }

    public function closeNotesModal()
    {
        $this->showNotesModal = false;
        $this->currentExerciseId = null;
        $this->exerciseNotes = '';
    }

    public function loadExistingNotes()
    {
        if (!$this->workoutSession) {
            $this->sessionNotes = '';
            return;
        }
        
        $this->sessionNotes = $this->workoutSession->notes ?? '';
    }

    public function initializeSetValues()
    {
        try {
            // Initialize set values with default data from database
            foreach ($this->todayExercises as $scheduleItem) {
                $exerciseId = $scheduleItem->exercise_id;
                $isTimeBased = $scheduleItem->is_time_based ?? false;
                
                // Initialize warmup sets
                if ($scheduleItem->has_warmup) {
                    for ($i = 1; $i <= $scheduleItem->warmup_sets; $i++) {
                        $this->setWeights[$exerciseId]['warmup'][$i] = null;
                        if ($isTimeBased) {
                            $this->setReps[$exerciseId]['warmup'][$i] = $scheduleItem->warmup_time_in_seconds ?? 60;
                        } else {
                            $this->setReps[$exerciseId]['warmup'][$i] = $scheduleItem->warmup_reps;
                        }
                    }
                }
                
                // Initialize working sets
                for ($i = 1; $i <= $scheduleItem->sets; $i++) {
                    $this->setWeights[$exerciseId]['working'][$i] = null;
                    if ($isTimeBased) {
                        $this->setReps[$exerciseId]['working'][$i] = $scheduleItem->time_in_seconds ?? 60;
                    } else {
                        $this->setReps[$exerciseId]['working'][$i] = $scheduleItem->reps;
                    }
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to initialize set values: ' . $e->getMessage());
        }
    }

    protected function loadExistingSetValues()
    {
        if (!$this->workoutSession) {
            return;
        }
        
        $sets = $this->workoutSession->exerciseSets()
            ->orderBy('exercise_id')
            ->orderBy('is_warmup', 'desc')
            ->orderBy('set_number')
            ->get();

        foreach ($sets as $set) {
            $exerciseId = $set->exercise_id;
            $setType = $set->is_warmup ? 'warmup' : 'working';
            
            if (!isset($this->setWeights[$exerciseId])) {
                $this->setWeights[$exerciseId] = ['warmup' => [], 'working' => []];
            }
            if (!isset($this->setReps[$exerciseId])) {
                $this->setReps[$exerciseId] = ['warmup' => [], 'working' => []];
            }
            if (!isset($this->setNotes[$exerciseId])) {
                $this->setNotes[$exerciseId] = ['warmup' => [], 'working' => []];
            }
            
            $this->setWeights[$exerciseId][$setType][$set->set_number] = $set->weight;
            $this->setReps[$exerciseId][$setType][$set->set_number] = $set->reps;
            $this->setNotes[$exerciseId][$setType][$set->set_number] = $set->notes;
        }
    }

    protected function loadExerciseCompletionStatus()
    {
        try {
            if ($this->workoutPlan && $this->todayExercises) {
                $exerciseIds = $this->todayExercises->pluck('exercise_id');
                
                // Check completion status through workout sessions and exercise sets
                $completionStatus = [];
                foreach ($exerciseIds as $exerciseId) {
                    $isCompleted = \App\Models\WorkoutSession::where('workout_plan_id', $this->workoutPlan->id)
                        ->where('user_id', auth()->id())
                        ->whereDate('date', now()->format('Y-m-d'))
                        ->where('status', 'completed')
                        ->whereHas('exerciseSets', function($query) use ($exerciseId) {
                            $query->where('exercise_id', $exerciseId);
                        })
                        ->exists();
                    
                    $completionStatus[$exerciseId] = $isCompleted;
                }
                
                $this->exerciseCompletionStatus = $completionStatus;
            }
        } catch (\Exception $e) {
            // Silently handle this error as it's not critical
            $this->exerciseCompletionStatus = [];
        }
    }

    protected function createExerciseSets($exerciseId)
    {
        $scheduleItem = $this->todayExercises->firstWhere('exercise_id', $exerciseId);
        if (!$scheduleItem) {
            return;
        }
        
        $isTimeBased = $scheduleItem->is_time_based ?? false;
        
        // Create warmup sets if needed
        if ($scheduleItem->has_warmup) {
            for ($i = 1; $i <= $scheduleItem->warmup_sets; $i++) {
                \App\Models\ExerciseSet::create([
                    'workout_session_id' => $this->workoutSession->id,
                    'exercise_id' => $exerciseId,
                    'set_number' => $i,
                    'is_warmup' => true,
                    'reps' => $isTimeBased ? 0 : $scheduleItem->warmup_reps,
                    'weight' => $isTimeBased ? null : 0,
                    'time_in_seconds' => $isTimeBased ? $scheduleItem->warmup_time_in_seconds : null,
                    'completed' => false,
                ]);
            }
        }
        
        // Create working sets
        for ($i = 1; $i <= $scheduleItem->sets; $i++) {
            \App\Models\ExerciseSet::create([
                'workout_session_id' => $this->workoutSession->id,
                'exercise_id' => $exerciseId,
                'set_number' => $i,
                'is_warmup' => false,
                'reps' => $isTimeBased ? 0 : $scheduleItem->reps,
                'weight' => $isTimeBased ? null : 0,
                'time_in_seconds' => $isTimeBased ? $scheduleItem->time_in_seconds : null,
                'completed' => false,
            ]);
        }
    }

    protected function saveExerciseData($exerciseId)
    {
        $scheduleItem = $this->todayExercises->firstWhere('exercise_id', $exerciseId);
        if (!$scheduleItem) {
            return;
        }
        
        $isTimeBased = $scheduleItem->is_time_based ?? false;
        
        // Save warmup sets if they exist
        if ($scheduleItem->has_warmup) {
            for ($i = 1; $i <= $scheduleItem->warmup_sets; $i++) {
                $set = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                    ->where('exercise_id', $exerciseId)
                    ->where('is_warmup', true)
                    ->where('set_number', $i)
                    ->first();
                    
                if ($set) {
                    $weight = $isTimeBased ? null : ($this->setWeights[$exerciseId]['warmup'][$i] ?? 0);
                    $weight = $weight ?: 0; // Convert null/empty to 0
                    $reps = $isTimeBased ? 0 : ($this->setReps[$exerciseId]['warmup'][$i] ?? $scheduleItem->warmup_reps);
                    $timeInSeconds = $isTimeBased ? ($this->setReps[$exerciseId]['warmup'][$i] ?? $scheduleItem->warmup_time_in_seconds) : null;
                    
                    $set->update([
                        'weight' => $weight,
                        'reps' => $reps,
                        'time_in_seconds' => $timeInSeconds,
                        'notes' => $this->setNotes[$exerciseId]['warmup'][$i] ?? null,
                        'completed' => true,
                    ]);
                }
            }
        }
        
        // Save working sets
        for ($i = 1; $i <= $scheduleItem->sets; $i++) {
            $set = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                ->where('exercise_id', $exerciseId)
                ->where('is_warmup', false)
                ->where('set_number', $i)
                ->first();
                
            if ($set) {
                $weight = $isTimeBased ? null : ($this->setWeights[$exerciseId]['working'][$i] ?? 0);
                $weight = $weight ?: 0; // Convert null/empty to 0
                $reps = $isTimeBased ? 0 : ($this->setReps[$exerciseId]['working'][$i] ?? $scheduleItem->reps);
                $timeInSeconds = $isTimeBased ? ($this->setReps[$exerciseId]['working'][$i] ?? $scheduleItem->time_in_seconds) : null;
                
                $set->update([
                    'weight' => $weight,
                    'reps' => $reps,
                    'time_in_seconds' => $timeInSeconds,
                    'notes' => $this->setNotes[$exerciseId]['working'][$i] ?? null,
                    'completed' => true,
                ]);
            }
        }
        
        // Save exercise-level notes from the modal
        if ($this->exerciseNotes) {
            \Log::info('Saving exercise notes: ' . $this->exerciseNotes);
            // Store exercise notes in the session notes or create a separate field
            $this->setNotes[$exerciseId] = $this->exerciseNotes;
            
            // Also save the exercise notes to the workout session notes
            $currentNotes = $this->workoutSession->notes ?? '';
            $exerciseName = $scheduleItem->exercise->name ?? 'Exercise ' . $exerciseId;
            $newNote = "\n\n" . $exerciseName . " Notes: " . $this->exerciseNotes;
            $this->workoutSession->update(['notes' => $currentNotes . $newNote]);
            \Log::info('Exercise notes saved to workout session');
        } else {
            \Log::info('No exercise notes to save');
        }
    }

    public function forceShowModal()
    {
        $this->showNotesModal = true;
        $this->currentExerciseId = 58;
        \Log::info('Force show modal called - showNotesModal: ' . ($this->showNotesModal ? 'true' : 'false') . ', currentExerciseId: ' . $this->currentExerciseId);
    }

    public function render()
    {
        return view('livewire.workout-session');
    }
}