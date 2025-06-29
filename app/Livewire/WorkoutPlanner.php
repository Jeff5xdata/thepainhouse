<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.navigation')]
class WorkoutPlanner extends Component
{
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:1000')]
    public $description = '';

    #[Rule('required|integer|min:1|max:52')]
    public $weeks_duration = 1;

    public $schedule = [];
    public $exercises = [];
    public $selectedExercises = [];
    public $currentDay = 'monday';
    public $currentWeek = 1;
    public $search = '';
    public $filteredExercises = [];
    public $showExerciseModal = false;
    public $existingPlan = null;
    public $showConfirmModal = false;
    public $showDeleteConfirmModal = false;
    public $showPrintModal = false;
    public $selectedExercise = null;
    public $exerciseModal = false;
    public $categories;
    public $workoutPlan;
    public $planName;
    public $weeksDuration = 1;
    public $exerciseDetails = [];
    public $editingExercise = null;
    public $editingScheduleItem = null;
    public $showDebug = false;
    public $debugMessage = '';

    public $daysOfWeek = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday'
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'weeks_duration' => 'required|integer|min:1|max:52',
        'schedule' => 'array',
    ];

    public function mount($week = null, $day = null, $plan_id = null)
    {
        // Initialize with passed week and day or defaults
        $this->currentWeek = $week ?? 1;
        $this->currentDay = $day ?? 'monday';

        $this->exercises = Exercise::orderBy('name')->get();
        $this->categories = config('exercises.categories');
        
        if ($plan_id) {
            // Load specific plan for editing
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
            ->findOrFail($plan_id);
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
        }

        if ($this->workoutPlan) {
            // Initialize form with existing plan data
            $this->name = $this->workoutPlan->name;
            $this->description = $this->workoutPlan->description;
            $this->weeks_duration = $this->workoutPlan->weeks_duration;
            $this->loadSchedule();
            
            // Debug: Log the loaded schedule
            \Log::info('Loaded schedule from existing plan', [
                'plan_id' => $this->workoutPlan->id,
                'schedule' => $this->schedule,
                'schedule_count' => is_array($this->schedule) ? count($this->schedule) : 'not array',
            ]);
        } else {
            // Initialize empty schedule for new plan
            $this->schedule = [];
            $this->name = '';
            $this->description = '';
            $this->weeks_duration = 1;
            
            // Debug: Log the empty schedule
            \Log::info('Initialized empty schedule for new plan', [
                'schedule' => $this->schedule,
            ]);
        }
    }

    protected function loadSchedule()
    {
        if (!$this->workoutPlan) {
            $this->schedule = [];
            return;
        }

        $scheduleItems = $this->workoutPlan->scheduleItems()
            ->with('exercise')
            ->orderBy('week_number')
            ->orderBy('day_of_week')
            ->orderBy('order_in_day')
            ->get();

        $this->schedule = [];

        // Convert schedule items to the format expected by the view
        foreach ($scheduleItems as $item) {
            if (!isset($this->schedule[$item->week_number])) {
                $this->schedule[$item->week_number] = [];
            }
            if (!isset($this->schedule[$item->week_number][$item->day_of_week])) {
                $this->schedule[$item->week_number][$item->day_of_week] = [];
            }
            
            $exerciseData = [
                'exercise_id' => $item->exercise_id,
                'exercise' => $item->exercise,
                'is_time_based' => $item->is_time_based,
                'sets' => $item->sets,
                'reps' => $item->reps,
                'time_in_seconds' => $item->time_in_seconds,
                'has_warmup' => $item->has_warmup,
                'warmup_sets' => $item->warmup_sets,
                'warmup_reps' => $item->warmup_reps,
                'warmup_time_in_seconds' => $item->warmup_time_in_seconds,
                'warmup_weight_percentage' => $item->warmup_weight_percentage,
                'weight' => $item->weight,
                'notes' => $item->notes,
                'set_details' => $item->formatted_set_details
            ];
            
            $this->schedule[$item->week_number][$item->day_of_week][] = $exerciseData;
        }
        
        // Ensure all exercises have synchronized sets/reps with JSON
        foreach ($this->schedule as $week => $days) {
            foreach ($days as $day => $exercises) {
                foreach ($exercises as $index => $exercise) {
                    $this->syncSetsAndRepsWithJson($week, $day, $index);
                }
            }
        }
    }

    public function updatedSearch()
    {
        if (empty($this->search)) {
            $this->filteredExercises = $this->exercises;
        } else {
            $this->filteredExercises = $this->exercises->filter(function ($exercise) {
                return str_contains(strtolower($exercise->name), strtolower($this->search)) ||
                       str_contains(strtolower($exercise->category), strtolower($this->search)) ||
                       str_contains(strtolower($exercise->equipment ?? ''), strtolower($this->search));
            });
        }
    }

    public function initializeSchedule()
    {
        if (!isset($this->schedule[$this->currentWeek])) {
            $this->schedule[$this->currentWeek] = [];
        }
        if (!isset($this->schedule[$this->currentWeek][$this->currentDay])) {
            $this->schedule[$this->currentWeek][$this->currentDay] = [];
        }
    }

    public function updatedWeeksDuration()
    {
        // Ensure current week doesn't exceed the new duration
        if ($this->currentWeek > $this->weeks_duration) {
            $this->currentWeek = $this->weeks_duration;
        }
    }

    public function toggleExerciseModal()
    {
        $this->showExerciseModal = !$this->showExerciseModal;
        if ($this->showExerciseModal) {
            $this->filteredExercises = $this->exercises;
        }
    }

    public function toggleConfirmModal()
    {
        $this->showConfirmModal = !$this->showConfirmModal;
    }

    public function toggleDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = !$this->showDeleteConfirmModal;
    }

    public function togglePrintModal()
    {
        $this->showPrintModal = !$this->showPrintModal;
    }

    public function addExercise($week, $day, $exerciseId)
    {
        $this->initializeSchedule();
        
        $exercise = $this->exercises->find($exerciseId);
        if (!$exercise) {
            return;
        }

        $orderInDay = count($this->schedule[$week][$day]);
        
        $this->schedule[$week][$day][] = [
            'exercise_id' => $exercise->id,
            'exercise' => $exercise,
            'is_time_based' => false,
            'sets' => 3,
            'reps' => 10,
            'time_in_seconds' => null,
            'has_warmup' => false,
            'warmup_sets' => 2,
            'warmup_reps' => 10,
            'warmup_time_in_seconds' => null,
            'warmup_weight_percentage' => 50,
            'weight' => null,
            'notes' => '',
            'set_details' => []
        ];

        $this->regenerateSetDetails($week, $day, $orderInDay);
        $this->toggleExerciseModal();
    }

    public function removeExercise($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            unset($this->schedule[$week][$day][$index]);
            $this->schedule[$week][$day] = array_values($this->schedule[$week][$day]);
        }
    }

    public function moveExercise($week, $day, $fromIndex, $toIndex)
    {
        if (isset($this->schedule[$week][$day][$fromIndex])) {
            $exercise = $this->schedule[$week][$day][$fromIndex];
            unset($this->schedule[$week][$day][$fromIndex]);
            $this->schedule[$week][$day] = array_values($this->schedule[$week][$day]);
            
            array_splice($this->schedule[$week][$day], $toIndex, 0, [$exercise]);
        }
    }

    public function updateExercise($week, $day, $index, $field, $value)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index][$field] = $value;
            
            // For time-based exercises, if the main time is updated, propagate to all work sets
            if ($field === 'time_in_seconds' && ($this->schedule[$week][$day][$index]['is_time_based'] ?? false)) {
                $this->propagateMainTimeToSets($week, $day, $index, $value);
            }
            
            // For time-based exercises, if the warmup time is updated, propagate to all warmup sets
            if ($field === 'warmup_time_in_seconds' && ($this->schedule[$week][$day][$index]['is_time_based'] ?? false)) {
                $this->propagateWarmupTimeToSets($week, $day, $index, $value);
            }
            
            // If reps are updated, propagate to all work sets
            if ($field === 'reps') {
                $this->propagateRepsToSets($week, $day, $index, $value);
            }
            
            // If weight is updated, propagate to all work sets
            if ($field === 'weight') {
                $this->propagateWeightToSets($week, $day, $index, $value);
            }
            
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    /**
     * Propagate main time_in_seconds to all work sets in the JSON
     */
    protected function propagateMainTimeToSets($week, $day, $index, $timeInSeconds)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if (!($set['is_warmup'] ?? false)) {
                $set['time_in_seconds'] = $timeInSeconds;
            }
        }
    }

    /**
     * Propagate warmup time_in_seconds to all warmup sets in the JSON
     */
    protected function propagateWarmupTimeToSets($week, $day, $index, $timeInSeconds)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if ($set['is_warmup'] ?? false) {
                $set['time_in_seconds'] = $timeInSeconds;
            }
        }
    }

    protected function regenerateSetDetails($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $setDetails = [];
        $setNumber = 1;

        // Add warmup sets if enabled
        if ($exercise['has_warmup'] && ($exercise['warmup_sets'] ?? 0) > 0) {
            for ($i = 1; $i <= $exercise['warmup_sets']; $i++) {
                $setDetails[] = [
                    'set_number' => $setNumber++,
                    'reps' => $exercise['warmup_reps'] ?? 10,
                    'weight' => null,
                    'notes' => "Warmup Set {$i}",
                    'time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? null,
                    'is_warmup' => true,
                ];
            }
        }

        // Add work sets
        for ($i = 1; $i <= ($exercise['sets'] ?? 3); $i++) {
            $setDetails[] = [
                'set_number' => $setNumber++,
                'reps' => $exercise['reps'] ?? 10,
                'weight' => $exercise['weight'] ?? null,
                'notes' => "Work Set {$i}",
                'time_in_seconds' => $exercise['time_in_seconds'] ?? null,
                'is_warmup' => false,
            ];
        }

        $this->schedule[$week][$day][$index]['set_details'] = $setDetails;
    }

    /**
     * Ensure sets and reps columns are synchronized with JSON set_details
     */
    protected function syncSetsAndRepsWithJson($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        // If set_details exists, ensure sets and reps match the JSON structure
        if (!empty($exercise['set_details'])) {
            $workSets = 0;
            $workReps = 0;
            $warmupSets = 0;
            $warmupReps = 0;
            $workTimeInSeconds = null;
            $warmupTimeInSeconds = null;
            
            foreach ($exercise['set_details'] as $set) {
                if ($set['is_warmup'] ?? false) {
                    $warmupSets++;
                    $warmupReps = $set['reps'] ?? 0;
                    // For time-based exercises, use the time from the first warmup set
                    if ($exercise['is_time_based'] && $warmupTimeInSeconds === null) {
                        $warmupTimeInSeconds = $set['time_in_seconds'] ?? null;
                    }
                } else {
                    $workSets++;
                    $workReps = $set['reps'] ?? 0;
                    // For time-based exercises, use the time from the first work set
                    if ($exercise['is_time_based'] && $workTimeInSeconds === null) {
                        $workTimeInSeconds = $set['time_in_seconds'] ?? null;
                    }
                }
            }
            
            // Update the exercise data to match the JSON
            $exercise['sets'] = $workSets;
            $exercise['reps'] = $workReps;
            $exercise['warmup_sets'] = $warmupSets > 0 ? $warmupSets : null;
            $exercise['warmup_reps'] = $warmupSets > 0 ? $warmupReps : null;
            
            // For time-based exercises, sync the time values
            if ($exercise['is_time_based']) {
                $exercise['time_in_seconds'] = $workTimeInSeconds;
                $exercise['warmup_time_in_seconds'] = $warmupTimeInSeconds;
            }
        }
    }

    /**
     * Update both sets/reps columns and regenerate JSON set_details
     */
    public function updateSetsAndReps($week, $day, $index, $sets = null, $reps = null, $warmupSets = null, $warmupReps = null)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        // Update the values if provided
        if ($sets !== null) {
            $exercise['sets'] = $sets;
        }
        if ($reps !== null) {
            $exercise['reps'] = $reps;
        }
        if ($warmupSets !== null) {
            $exercise['warmup_sets'] = $warmupSets;
        }
        if ($warmupReps !== null) {
            $exercise['warmup_reps'] = $warmupReps;
        }
        
        // Regenerate the JSON to keep it in sync
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function confirmSave()
    {
        $this->dispatch('open-confirm-modal');
    }

    public function deletePlan()
    {
        if ($this->workoutPlan) {
            $this->workoutPlan->delete();
            session()->flash('message', 'Workout plan deleted successfully!');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function save()
    {
        \Log::info('Save method called');
        
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weeks_duration' => 'required|integer|min:1',
        ]);

        // Debug: Log the schedule data
        \Log::info('Saving workout plan', [
            'name' => $this->name,
            'description' => $this->description,
            'weeks_duration' => $this->weeks_duration,
            'schedule' => $this->schedule,
            'schedule_count' => is_array($this->schedule) ? count($this->schedule) : 'not array',
        ]);

        try {
            DB::beginTransaction();

            if ($this->workoutPlan) {
                // Update existing plan
                $this->workoutPlan->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'weeks_duration' => $this->weeks_duration,
                ]);

                // Delete existing schedule items
                $this->workoutPlan->scheduleItems()->delete();
            } else {
                // Create new plan
                $this->workoutPlan = WorkoutPlan::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'weeks_duration' => $this->weeks_duration,
                    'user_id' => auth()->id(),
                    'is_active' => true,
                ]);
            }

            // Create or update schedule items
            if (!empty($this->schedule)) {
                $scheduleItems = [];
                
                foreach ($this->schedule as $week => $days) {
                    foreach ($days as $day => $exercises) {
                        foreach ($exercises as $index => $exercise) {
                            if (empty($exercise['exercise_id'])) {
                                continue; // Skip if no exercise is selected
                            }
                            
                            // Ensure sets and reps are synchronized with JSON before saving
                            $this->syncSetsAndRepsWithJson($week, $day, $index);
                            
                            $scheduleItems[] = [
                                'workout_plan_id' => $this->workoutPlan->id,
                                'exercise_id' => $exercise['exercise_id'],
                                'week_number' => $week,
                                'day_of_week' => $day,
                                'order_in_day' => $index,
                                'is_time_based' => $exercise['is_time_based'] ?? false,
                                'sets' => $exercise['sets'] ?? 3,
                                'reps' => $exercise['reps'] ?? 10,
                                'weight' => $exercise['weight'] ?? null,
                                'time_in_seconds' => $exercise['time_in_seconds'] ?? null,
                                'notes' => $exercise['notes'] ?? '',
                                'has_warmup' => $exercise['has_warmup'] ?? false,
                                'warmup_sets' => $exercise['warmup_sets'] ?? null,
                                'warmup_reps' => $exercise['warmup_reps'] ?? null,
                                'warmup_time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? null,
                                'warmup_weight_percentage' => $exercise['warmup_weight_percentage'] ?? null,
                                'set_details' => !empty($exercise['set_details']) ? json_encode($exercise['set_details']) : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // Debug: Log the schedule items being inserted
                \Log::info('Schedule items to insert', [
                    'count' => count($scheduleItems),
                    'items' => $scheduleItems
                ]);

                // Batch insert all schedule items
                if (!empty($scheduleItems)) {
                    WorkoutPlanSchedule::insert($scheduleItems);
                }
            } else {
                \Log::warning('No schedule data to save');
            }

            DB::commit();
            
            session()->flash('message', $this->workoutPlan ? 'Workout plan updated successfully!' : 'Workout plan created successfully!');
            
            // Redirect to the dashboard
            return $this->redirect(route('dashboard'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save workout plan. Please try again. Error: ' . $e->getMessage());
            \Log::error('Workout plan save error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    public function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' sec';
        }
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return $minutes . ' min' . ($remainingSeconds > 0 ? ' ' . $remainingSeconds . ' sec' : '');
    }

    public function getExerciseName($exerciseId)
    {
        return $this->exercises->firstWhere('id', $exerciseId)->name ?? 'Unknown Exercise';
    }

    public function addExerciseToDay($exerciseId)
    {
        $this->addExercise($this->currentWeek, $this->currentDay, $exerciseId);
    }

    public function toggleWarmup($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index]['has_warmup'] = !($this->schedule[$week][$day][$index]['has_warmup'] ?? false);
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function toggleTimeBased($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index]['is_time_based'] = !($this->schedule[$week][$day][$index]['is_time_based'] ?? false);
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function addSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index]['sets'] = ($this->schedule[$week][$day][$index]['sets'] ?? 3) + 1;
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function removeSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $currentSets = $this->schedule[$week][$day][$index]['sets'] ?? 3;
            if ($currentSets > 1) {
                $this->schedule[$week][$day][$index]['sets'] = $currentSets - 1;
                $this->regenerateSetDetails($week, $day, $index);
            }
        }
    }

    public function addWarmupSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index]['warmup_sets'] = ($this->schedule[$week][$day][$index]['warmup_sets'] ?? 2) + 1;
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function removeWarmupSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $currentWarmupSets = $this->schedule[$week][$day][$index]['warmup_sets'] ?? 2;
            if ($currentWarmupSets > 1) {
                $this->schedule[$week][$day][$index]['warmup_sets'] = $currentWarmupSets - 1;
                $this->regenerateSetDetails($week, $day, $index);
            }
        }
    }

    public function checkScheduleState()
    {
        // Debug method to check the current state of the schedule
        $this->debugMessage = 'Schedule state: ' . json_encode($this->schedule, JSON_PRETTY_PRINT);
        \Log::info('Schedule state check', ['schedule' => $this->schedule]);
    }

    public function render()
    {
        return view('livewire.workout-planner');
    }

    /**
     * Update individual set time and sync with main exercise time
     */
    public function updateSetTime($week, $day, $index, $setIndex, $timeInSeconds)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'][$setIndex])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        $set = &$exercise['set_details'][$setIndex];
        
        // Update the set time
        $set['time_in_seconds'] = $timeInSeconds;
        
        // For time-based exercises, update the main exercise time if this is the first work set
        if ($exercise['is_time_based'] && !($set['is_warmup'] ?? false)) {
            // Find the first work set index
            $firstWorkSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if (!($s['is_warmup'] ?? false)) {
                    $firstWorkSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first work set, update the main exercise time
            if ($firstWorkSetIndex === $setIndex) {
                $exercise['time_in_seconds'] = $timeInSeconds;
            }
        }
        
        // For warmup sets, update the main warmup time if this is the first warmup set
        if ($exercise['is_time_based'] && ($set['is_warmup'] ?? false)) {
            // Find the first warmup set index
            $firstWarmupSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if ($s['is_warmup'] ?? false) {
                    $firstWarmupSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first warmup set, update the main warmup time
            if ($firstWarmupSetIndex === $setIndex) {
                $exercise['warmup_time_in_seconds'] = $timeInSeconds;
            }
        }
    }

    /**
     * Update individual set reps and sync with main exercise reps
     */
    public function updateSetReps($week, $day, $index, $setIndex, $reps)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'][$setIndex])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        $set = &$exercise['set_details'][$setIndex];
        
        // Update the set reps
        $set['reps'] = $reps;
        
        // Update the main exercise reps if this is the first work set
        if (!($set['is_warmup'] ?? false)) {
            // Find the first work set index
            $firstWorkSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if (!($s['is_warmup'] ?? false)) {
                    $firstWorkSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first work set, update the main exercise reps
            if ($firstWorkSetIndex === $setIndex) {
                $exercise['reps'] = $reps;
            }
        }
        
        // Update the main warmup reps if this is the first warmup set
        if ($set['is_warmup'] ?? false) {
            // Find the first warmup set index
            $firstWarmupSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if ($s['is_warmup'] ?? false) {
                    $firstWarmupSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first warmup set, update the main warmup reps
            if ($firstWarmupSetIndex === $setIndex) {
                $exercise['warmup_reps'] = $reps;
            }
        }
    }

    /**
     * Update individual set weight and sync with main exercise weight
     */
    public function updateSetWeight($week, $day, $index, $setIndex, $weight)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'][$setIndex])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        $set = &$exercise['set_details'][$setIndex];
        
        // Update the set weight
        $set['weight'] = $weight;
        
        // Update the main exercise weight if this is the first work set
        if (!($set['is_warmup'] ?? false)) {
            // Find the first work set index
            $firstWorkSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if (!($s['is_warmup'] ?? false)) {
                    $firstWorkSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first work set, update the main exercise weight
            if ($firstWorkSetIndex === $setIndex) {
                $exercise['weight'] = $weight;
            }
        }
    }

    /**
     * Propagate main reps to all work sets in the JSON
     */
    protected function propagateRepsToSets($week, $day, $index, $reps)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if (!($set['is_warmup'] ?? false)) {
                $set['reps'] = $reps;
            }
        }
    }

    /**
     * Propagate main weight to all work sets in the JSON
     */
    protected function propagateWeightToSets($week, $day, $index, $weight)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if (!($set['is_warmup'] ?? false)) {
                $set['weight'] = $weight;
            }
        }
    }
}