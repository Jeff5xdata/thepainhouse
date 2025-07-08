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
        // Check if user is authenticated
        if (!auth()->check()) {
            session()->flash('error', 'Please log in to access your workout session.');
            $this->redirect(route('login'));
            return;
        }
        
        // Initialize todayExercises as empty collection to prevent null errors
        $this->todayExercises = collect();
        
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
                $this->currentDay = Carbon::now()->dayOfWeek; // 0 (Sunday) through 6 (Saturday)
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
                    $this->currentDay = Carbon::now()->dayOfWeek; // 0 (Sunday) through 6 (Saturday)
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
                    'exercises',
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

                // Calculate current week using ISO week numbers
                $this->currentWeek = Carbon::now()->isoWeek();

                // Set current day before any getScheduleForDay call
                $this->currentDay = Carbon::now()->dayOfWeek; // 0 (Sunday) through 6 (Saturday)

                // Find the first week with exercises for the current day
                $this->todayExercises = $this->workoutPlan->getScheduleForDay($this->currentWeek, $this->currentDay)
                    ->unique('exercise_id')
                    ->values();
                    
                // If no exercises found for current ISO week, find the first week with exercises
                if ($this->todayExercises->isEmpty()) {
                    // Get all weeks that have data for this day
                    $scheduleItems = $this->workoutPlan->scheduleItems()
                        ->where('day_of_week', $this->currentDay)
                        ->orderBy('week_number')
                        ->get();
                    
                    if ($scheduleItems->isNotEmpty()) {
                        // Use the first available week
                        $this->currentWeek = $scheduleItems->first()->week_number;
                        $this->todayExercises = $this->workoutPlan->getScheduleForDay($this->currentWeek, $this->currentDay)
                            ->unique('exercise_id')
                            ->values();
                    }
                }

                $this->sessionDate = now()->format('Y-m-d');
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
            
            // Ensure todayExercises is always set to prevent null errors
            $this->todayExercises = collect();
            // Ensure workoutPlan is set to null to prevent undefined variable errors
            $this->workoutPlan = null;
            
            session()->flash('error', 'Failed to load workout session. Please try again or contact support if the problem persists.');
            
            // Only redirect to dashboard if it's a critical error
            if (str_contains($e->getMessage(), 'SQLSTATE') || str_contains($e->getMessage(), 'database')) {
                $this->redirect(route('dashboard'));
            }
        }
        
        // Final fallback to ensure todayExercises is always set
        if (!$this->todayExercises) {
            $this->todayExercises = collect();
        }
        
        // Final fallback to ensure workoutPlan is always set
        if (!isset($this->workoutPlan)) {
            $this->workoutPlan = null;
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
                    'week_number' => Carbon::now()->isoWeek(),
                    'day_of_week' => $this->currentDay,
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

            // Delete the workout plan schedule items after completion
            if ($this->workoutPlan) {
                \App\Models\WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)->delete();
            }

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
        try {
            \Log::info('completeExercise called for ID: ' . $exerciseId);
            
            // Ensure we have a workout session
            if (!$this->workoutSession) {
                \Log::info('Creating new workout session');
                // Create a new workout session if it doesn't exist
                $this->workoutSession = WorkoutSessionModel::create([
                    'user_id' => auth()->id(),
                    'workout_plan_id' => $this->workoutPlan->id,
                    'name' => 'Workout Session - ' . now()->format('M j, Y'),
                    'date' => $this->sessionDate,
                    'week_number' => Carbon::now()->isoWeek(),
                    'day_of_week' => $this->currentDay,
                    'status' => 'in_progress',
                    'notes' => $this->sessionNotes,
                ]);
                
                // Create exercise sets for this exercise
                $this->createExerciseSets($exerciseId);
            } else {
                \Log::info('Using existing workout session: ' . $this->workoutSession->id);
                // Check if exercise sets exist for this exercise
                $existingSets = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                    ->where('exercise_id', $exerciseId)
                    ->count();
                
                if ($existingSets === 0) {
                    \Log::info('Creating exercise sets for exercise: ' . $exerciseId);
                    // Create exercise sets if they don't exist
                    $this->createExerciseSets($exerciseId);
                }
            }
            
            // Save the form data (weights, reps, notes) for this exercise
            $this->saveExerciseData($exerciseId);
            
            // Mark all sets for this exercise as completed
            if (isset($this->exerciseSets[$exerciseId])) {
                foreach ($this->exerciseSets[$exerciseId] as &$set) {
                    $set['completed'] = true;
                }
            }
            
            // Update the exercise sets in the database to mark them as completed
            \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                ->where('exercise_id', $exerciseId)
                ->update(['completed' => true]);
            
            // Reload exercise completion status
            $this->loadExerciseCompletionStatus();
            
            \Log::info('Exercise completion saved successfully');
            session()->flash('message', 'Exercise completed successfully!');
        } catch (\Exception $e) {
            \Log::error('Error in completeExercise: ' . $e->getMessage());
            session()->flash('error', 'Failed to complete exercise: ' . $e->getMessage());
        }
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
            // Initialize set values with default data from JSON
            foreach ($this->todayExercises as $scheduleItem) {
                $exerciseId = $scheduleItem->exercise_id;
                $setDetails = $this->getSetDetails($scheduleItem);
                
                // Initialize warmup sets
                foreach ($setDetails['warmup_sets'] as $set) {
                    $this->setWeights[$exerciseId]['warmup'][$set['set_number']] = null;
                    $this->setReps[$exerciseId]['warmup'][$set['set_number']] = $set['reps'] ?? 0;
                    $this->setNotes[$exerciseId]['warmup'][$set['set_number']] = '';
                }
                
                // Initialize working sets
                foreach ($setDetails['working_sets'] as $set) {
                    $this->setWeights[$exerciseId]['working'][$set['set_number']] = null;
                    $this->setReps[$exerciseId]['working'][$set['set_number']] = $set['reps'] ?? 0;
                    $this->setNotes[$exerciseId]['working'][$set['set_number']] = '';
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error initializing set values: ' . $e->getMessage());
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
                
                // Check completion status through completed exercise sets for today
                $completionStatus = [];
                foreach ($exerciseIds as $exerciseId) {
                    $isCompleted = \App\Models\ExerciseSet::where('exercise_id', $exerciseId)
                        ->where('completed', true)
                        ->whereHas('workoutSession', function($query) {
                            $query->where('user_id', auth()->id())
                                ->whereDate('date', now()->format('Y-m-d'));
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
        
        $setDetails = $this->getSetDetails($scheduleItem);
        
        // Create sets based on JSON data
        foreach ($setDetails['warmup_sets'] as $set) {
            \App\Models\ExerciseSet::create([
                'workout_session_id' => $this->workoutSession->id,
                'exercise_id' => $exerciseId,
                'set_number' => $set['set_number'],
                'is_warmup' => true,
                'reps' => $set['reps'] ?? 0,
                'weight' => $set['weight'] ?? 0,
                'time_in_seconds' => $set['time_in_seconds'] ?? null,
                'completed' => true,
                'notes' => $set['notes'] ?? null,
            ]);
        }
        
        foreach ($setDetails['working_sets'] as $set) {
            \App\Models\ExerciseSet::create([
                'workout_session_id' => $this->workoutSession->id,
                'exercise_id' => $exerciseId,
                'set_number' => $set['set_number'],
                'is_warmup' => false,
                'reps' => $set['reps'] ?? 0,
                'weight' => $set['weight'] ?? 0,
                'time_in_seconds' => $set['time_in_seconds'] ?? null,
                'completed' => true,
                'notes' => $set['notes'] ?? null,
            ]);
        }
    }

    protected function saveExerciseData($exerciseId)
    {
        $scheduleItem = $this->todayExercises->firstWhere('exercise_id', $exerciseId);
        if (!$scheduleItem) {
            return;
        }
        
        $setDetails = $this->getSetDetails($scheduleItem);
        
        // Save warmup sets
        foreach ($setDetails['warmup_sets'] as $set) {
            $setModel = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                ->where('exercise_id', $exerciseId)
                ->where('is_warmup', true)
                ->where('set_number', $set['set_number'])
                ->first();
                
            if ($setModel) {
                $weight = $this->setWeights[$exerciseId]['warmup'][$set['set_number']] ?? 0;
                $reps = $this->setReps[$exerciseId]['warmup'][$set['set_number']] ?? $set['reps'] ?? 0;
                $timeInSeconds = $scheduleItem->is_time_based ? ($this->setReps[$exerciseId]['warmup'][$set['set_number']] ?? $set['time_in_seconds']) : null;
                
                $setModel->update([
                    'weight' => $weight,
                    'reps' => $reps,
                    'time_in_seconds' => $timeInSeconds,
                    'notes' => $this->setNotes[$exerciseId]['warmup'][$set['set_number']] ?? null,
                ]);
            }
        }
        
        // Save working sets
        foreach ($setDetails['working_sets'] as $set) {
            $setModel = \App\Models\ExerciseSet::where('workout_session_id', $this->workoutSession->id)
                ->where('exercise_id', $exerciseId)
                ->where('is_warmup', false)
                ->where('set_number', $set['set_number'])
                ->first();
                
            if ($setModel) {
                $weight = $this->setWeights[$exerciseId]['working'][$set['set_number']] ?? 0;
                $reps = $this->setReps[$exerciseId]['working'][$set['set_number']] ?? $set['reps'] ?? 0;
                $timeInSeconds = $scheduleItem->is_time_based ? ($this->setReps[$exerciseId]['working'][$set['set_number']] ?? $set['time_in_seconds']) : null;
                
                $setModel->update([
                    'weight' => $weight,
                    'reps' => $reps,
                    'time_in_seconds' => $timeInSeconds,
                    'notes' => $this->setNotes[$exerciseId]['working'][$set['set_number']] ?? null,
                ]);
            }
        }
    }

    /**
     * Get processed set details from JSON for a schedule item
     */
    public function getSetDetails($scheduleItem)
    {
        if (empty($scheduleItem->set_details)) {
            throw new \Exception("set_details is required but empty for exercise ID: {$scheduleItem->exercise_id} in week {$scheduleItem->week_number}, day {$scheduleItem->day_of_week}. Please ensure the workout plan has properly configured set_details.");
        }

        // Ensure set_details is an array
        $setDetails = is_string($scheduleItem->set_details) ? json_decode($scheduleItem->set_details, true) : $scheduleItem->set_details;
        
        if (!is_array($setDetails)) {
            throw new \Exception("set_details is not a valid array for exercise ID: {$scheduleItem->exercise_id}");
        }

        // Handle nested JSON structure with exercise_config and sets
        if (isset($setDetails['sets'])) {
            $setDetails = $setDetails['sets'];
        }

        $warmupSets = [];
        $workingSets = [];

        foreach ($setDetails as $set) {
            if ($set['is_warmup'] ?? false) {
                $warmupSets[] = $set;
            } else {
                $workingSets[] = $set;
            }
        }

        return [
            'warmup_sets' => $warmupSets,
            'working_sets' => $workingSets
        ];
    }

    /**
     * Get the day name from day number
     */
    public function getDayName($dayNumber)
    {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];

        return $days[$dayNumber] ?? 'Unknown Day';
    }

    /**
     * Get the user's preferred weight unit
     * @return string
     */
    public function getPreferredWeightUnit(): string
    {
        return auth()->user()->getPreferredWeightUnit();
    }

    /**
     * Get exercise history for a specific exercise
     */
    public function getExerciseHistory($exerciseId)
    {
        try {
            $history = \App\Models\ExerciseSet::with(['workoutSession'])
                ->where('exercise_id', $exerciseId)
                ->where('is_warmup', false) // Only working sets
                ->where('completed', true)
                ->whereHas('workoutSession', function($query) {
                    $query->where('user_id', auth()->id())
                        ->where('status', 'completed');
                })
                ->orderBy('created_at', 'desc')
                ->limit(5) // Show last 5 sessions
                ->get()
                ->groupBy('workout_session_id')
                ->map(function($sets) {
                    $session = $sets->first()->workoutSession;
                    $bestSet = $sets->sortByDesc('weight')->first();
                    
                    return [
                        'date' => $session->date->format('M j, Y'),
                        'weight' => $bestSet->weight ?? 0,
                        'reps' => $bestSet->reps ?? 0,
                        'sets_count' => $sets->count(),
                        'is_time_based' => $bestSet->time_in_seconds ? true : false,
                        'time_in_seconds' => $bestSet->time_in_seconds ?? null,
                    ];
                })
                ->values()
                ->toArray();

            return $history;
        } catch (\Exception $e) {
            \Log::error('Error getting exercise history: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        return view('livewire.workout-session');
    }
}