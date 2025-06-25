<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
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

    protected $daysOfWeek = [
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
            ->orderBy('set_number')
            ->get();

        $this->schedule = [];

        // Group items by week, day, and exercise
        $groupedItems = [];
        foreach ($scheduleItems as $item) {
            $key = "{$item->week_number}_{$item->day_of_week}_{$item->order_in_day}";
            if (!isset($groupedItems[$key])) {
                $groupedItems[$key] = [
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
                    'set_details' => []
                ];
            }
            
            // Add set details
            $groupedItems[$key]['set_details'][] = [
                'set_number' => $item->set_number,
                'reps' => $item->reps,
                'weight' => $item->weight,
                'notes' => $item->notes,
                'is_warmup' => $item->has_warmup && $item->set_number <= $item->warmup_sets,
                'time_in_seconds' => $item->time_in_seconds,
            ];
        }

        // Convert grouped items back to schedule format
        foreach ($groupedItems as $key => $item) {
            list($week, $day, $order) = explode('_', $key);
            
            if (!isset($this->schedule[$week])) {
                $this->schedule[$week] = [];
            }
            if (!isset($this->schedule[$week][$day])) {
                $this->schedule[$week][$day] = [];
            }

            $this->schedule[$week][$day][] = $item;
        }
    }

    public function updatedSearch()
    {
        $this->filteredExercises = $this->exercises->filter(function($exercise) {
            return str_contains(strtolower($exercise->name), strtolower($this->search)) ||
                    str_contains(strtolower($exercise->category), strtolower($this->search)) ||
                    str_contains(strtolower($exercise->equipment), strtolower($this->search));
        })->values();
    }

    public function initializeSchedule()
    {
        for ($week = 1; $week <= $this->weeks_duration; $week++) {
            foreach (array_keys($this->daysOfWeek) as $day) {
                if (!isset($this->schedule[$week][$day])) {
                    $this->schedule[$week][$day] = [];
                }
            }
        }
    }

    public function updatedWeeksDuration()
    {
        $this->initializeSchedule();
    }

    public function toggleExerciseModal()
    {
        $this->showExerciseModal = !$this->showExerciseModal;
        if ($this->showExerciseModal) {
            $this->search = '';
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
        if (!isset($this->schedule[$week])) {
            $this->schedule[$week] = [];
        }
        if (!isset($this->schedule[$week][$day])) {
            $this->schedule[$week][$day] = [];
        }

        $exercise = $this->exercises->firstWhere('id', $exerciseId);
        $orderInDay = count($this->schedule[$week][$day]);

        $exerciseData = [
            'exercise_id' => $exerciseId,
            'sets' => 1,
            'reps' => 10,
            'weight' => 0,
            'notes' => '',
            'is_time_based' => false,
            'time_in_seconds' => null,
            'has_warmup' => false,
            'warmup_sets' => null,
            'warmup_reps' => null,
            'warmup_time_in_seconds' => null,
            'warmup_weight_percentage' => null,
            'order_in_day' => $orderInDay,
        ];

        $this->schedule[$week][$day][] = $exerciseData;
        
        // Initialize set details for the new exercise
        $this->regenerateSetDetails($week, $day, count($this->schedule[$week][$day]) - 1);
    }

    public function removeExercise($week, $day, $index)
    {
        unset($this->schedule[$week][$day][$index]);
        $this->schedule[$week][$day] = array_values($this->schedule[$week][$day]);
        
        // Update order_in_day for remaining exercises
        foreach ($this->schedule[$week][$day] as $i => $exercise) {
            $this->schedule[$week][$day][$i]['order_in_day'] = $i;
        }
    }

    public function moveExercise($week, $day, $fromIndex, $toIndex)
    {
        if ($fromIndex === $toIndex) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$fromIndex];
        unset($this->schedule[$week][$day][$fromIndex]);
        array_splice($this->schedule[$week][$day], $toIndex, 0, [$exercise]);
        $this->schedule[$week][$day] = array_values($this->schedule[$week][$day]);

        // Update order_in_day for all exercises
        foreach ($this->schedule[$week][$day] as $i => $exercise) {
            $this->schedule[$week][$day][$i]['order_in_day'] = $i;
        }
    }

    public function updateExercise($week, $day, $index, $field, $value)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index][$field] = $value;
            
            // If sets or warmup_sets changed, regenerate set details
            if ($field === 'sets' || $field === 'warmup_sets') {
                $this->regenerateSetDetails($week, $day, $index);
            }
        }
    }

    protected function regenerateSetDetails($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $totalSets = $exercise['sets'] ?? 3;
        $totalWarmupSets = $exercise['has_warmup'] ? ($exercise['warmup_sets'] ?? 2) : 0;
        
        $setDetails = [];
        
        // Generate warmup sets
        for ($setNum = 1; $setNum <= $totalWarmupSets; $setNum++) {
            $setDetails[] = [
                'set_number' => $setNum,
                'reps' => $exercise['warmup_reps'] ?? 10,
                'weight' => null,
                'notes' => "Warmup Set {$setNum}",
                'is_warmup' => true,
                'time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? 0,
            ];
        }
        
        // Generate work sets
        for ($setNum = 1; $setNum <= $totalSets; $setNum++) {
            $reps = isset($exercise['is_time_based']) && $exercise['is_time_based'] ? 0 : ($exercise['reps'] ?? 10);
            
            $setDetails[] = [
                'set_number' => $setNum + $totalWarmupSets,
                'reps' => $reps,
                'weight' => $exercise['weight'] ?? 0,
                'notes' => "Work Set {$setNum}",
                'is_warmup' => false,
                'time_in_seconds' => $exercise['time_in_seconds'] ?? 0,
            ];
        }
        
        $this->schedule[$week][$day][$index]['set_details'] = $setDetails;
    }

    public function confirmSave()
    {
        if ($this->workoutPlan) {
            // If editing an existing plan, show confirmation dialog
            $this->dispatch('open-confirm-modal');
        } else {
            // If creating a new plan, save directly
            $this->save();
        }
    }

    public function deletePlan()
    {
        try {
            if (!$this->workoutPlan) {
                session()->flash('error', 'No workout plan found to delete.');
                return;
            }

            DB::beginTransaction();

            // Delete schedule items first (due to foreign key constraint)
            $this->workoutPlan->scheduleItems()->delete();
            
            // Delete the plan
            $this->workoutPlan->delete();

            DB::commit();

            session()->flash('message', 'Workout plan deleted successfully!');
            return $this->redirect(route('dashboard'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to delete workout plan. Please try again.');
            \Log::error('Workout plan delete error: ' . $e->getMessage());
        }

        $this->showDeleteConfirmModal = false;
    }

    public function save()
    {
        \Log::info('Save method called');
        
        // Check the current schedule state
        $this->checkScheduleState();
        
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
                            
                            $totalSets = $exercise['sets'] ?? 3;
                            $totalWarmupSets = $exercise['has_warmup'] ? ($exercise['warmup_sets'] ?? 2) : 0;
                            
                            // Check if we have individual set details from the form
                            $hasIndividualSets = isset($exercise['set_details']) && !empty($exercise['set_details']);
                            
                            if ($hasIndividualSets) {
                                // Use individual set details from form
                                foreach ($exercise['set_details'] as $setDetail) {
                                    // Ensure set_number is not null
                                    $setNumber = $setDetail['set_number'] ?? 1;
                                    
                                    $scheduleItems[] = [
                                        'workout_plan_id' => $this->workoutPlan->id,
                                        'exercise_id' => $exercise['exercise_id'],
                                        'week_number' => $week,
                                        'day_of_week' => $day,
                                        'order_in_day' => $index,
                                        'set_number' => $setNumber,
                                        'is_time_based' => $exercise['is_time_based'] ?? false,
                                        'sets' => $totalSets,
                                        'reps' => $setDetail['reps'] ?? 10,
                                        'weight' => $setDetail['weight'] ?? 0,
                                        'notes' => $setDetail['notes'] ?? '',
                                        'time_in_seconds' => $setDetail['time_in_seconds'] ?? 0,
                                        'has_warmup' => $setDetail['is_warmup'] ?? false,
                                        'warmup_sets' => $totalWarmupSets,
                                        'warmup_reps' => $exercise['warmup_reps'] ?? 10,
                                        'warmup_time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? 0,
                                        'warmup_weight_percentage' => $exercise['warmup_weight_percentage'] ?? 50,
                                        'warmup_weight' => null,
                                        'warmup_notes' => null,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }
                            } else {
                                // Create records for warmup sets
                                for ($setNum = 1; $setNum <= $totalWarmupSets; $setNum++) {
                                    $scheduleItems[] = [
                                        'workout_plan_id' => $this->workoutPlan->id,
                                        'exercise_id' => $exercise['exercise_id'],
                                        'week_number' => $week,
                                        'day_of_week' => $day,
                                        'order_in_day' => $index,
                                        'set_number' => $setNum,
                                        'is_time_based' => $exercise['is_time_based'] ?? false,
                                        'sets' => $totalSets,
                                        'reps' => $exercise['warmup_reps'] ?? 10,
                                        'weight' => null, // Will be calculated based on percentage
                                        'notes' => "Warmup Set {$setNum}",
                                        'time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? 0,
                                        'has_warmup' => true,
                                        'warmup_sets' => $totalWarmupSets,
                                        'warmup_reps' => $exercise['warmup_reps'] ?? 10,
                                        'warmup_time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? 0,
                                        'warmup_weight_percentage' => $exercise['warmup_weight_percentage'] ?? 50,
                                        'warmup_weight' => null,
                                        'warmup_notes' => "Warmup Set {$setNum}",
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }
                                
                                // Create records for work sets
                                for ($setNum = 1; $setNum <= $totalSets; $setNum++) {
                                    // For time-based exercises, ensure reps is 0
                                    $reps = isset($exercise['is_time_based']) && $exercise['is_time_based'] ? 0 : ($exercise['reps'] ?? 10);
                                    
                                    $scheduleItems[] = [
                                        'workout_plan_id' => $this->workoutPlan->id,
                                        'exercise_id' => $exercise['exercise_id'],
                                        'week_number' => $week,
                                        'day_of_week' => $day,
                                        'order_in_day' => $index,
                                        'set_number' => $setNum + $totalWarmupSets, // Continue numbering after warmup sets
                                        'is_time_based' => $exercise['is_time_based'] ?? false,
                                        'sets' => $totalSets,
                                        'reps' => $reps,
                                        'weight' => $exercise['weight'] ?? 0,
                                        'notes' => "Work Set {$setNum}",
                                        'time_in_seconds' => $exercise['time_in_seconds'] ?? 0,
                                        'has_warmup' => $exercise['has_warmup'] ?? false,
                                        'warmup_sets' => $totalWarmupSets,
                                        'warmup_reps' => $exercise['warmup_reps'] ?? 10,
                                        'warmup_time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? 0,
                                        'warmup_weight_percentage' => $exercise['warmup_weight_percentage'] ?? 50,
                                        'warmup_weight' => null,
                                        'warmup_notes' => null,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }
                            }
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
        if (!isset($this->schedule[$this->currentWeek])) {
            $this->schedule[$this->currentWeek] = [];
        }
        if (!isset($this->schedule[$this->currentWeek][$this->currentDay])) {
            $this->schedule[$this->currentWeek][$this->currentDay] = [];
        }

        $exercise = $this->exercises->firstWhere('id', $exerciseId);
        $orderInDay = count($this->schedule[$this->currentWeek][$this->currentDay]);

        // Get user's workout settings
        $settings = auth()->user()->workoutSettings;

        $exerciseData = [
            'exercise_id' => $exerciseId,
            'sets' => $settings ? 1 : 1,
            'reps' => $settings ? $settings->default_work_reps : 10,
            'weight' => 0,
            'notes' => '',
            'order_in_day' => $orderInDay,
            'has_warmup' => false,
            'warmup_sets' => $settings ? $settings->default_warmup_sets : 2,
            'warmup_reps' => $settings ? $settings->default_warmup_reps : 10,
            'warmup_weight_percentage' => $settings ? $settings->default_warmup_weight_percentage : 50,
            'exercise_name' => $exercise->name,
            'is_time_based' => false,
            'time_in_seconds' => null
        ];

        $this->schedule[$this->currentWeek][$this->currentDay][] = $exerciseData;
        
        // Initialize set details for the new exercise
        $this->regenerateSetDetails($this->currentWeek, $this->currentDay, count($this->schedule[$this->currentWeek][$this->currentDay]) - 1);
        
        // Debug: Log the exercise addition
        \Log::info('Added exercise to day', [
            'exercise_id' => $exerciseId,
            'exercise_name' => $exercise->name,
            'week' => $this->currentWeek,
            'day' => $this->currentDay,
            'schedule' => $this->schedule,
        ]);
        
        $this->toggleExerciseModal();
    }

    public function toggleWarmup($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $this->schedule[$week][$day][$index]['has_warmup'] = !$this->schedule[$week][$day][$index]['has_warmup'];
        
        // Get user's workout settings
        $settings = auth()->user()->workoutSettings;
        
        if ($this->schedule[$week][$day][$index]['has_warmup']) {
            $this->schedule[$week][$day][$index]['warmup_sets'] = $settings ? $settings->default_warmup_sets : 2;
            $this->schedule[$week][$day][$index]['warmup_reps'] = $settings ? $settings->default_warmup_reps : 10;
            $this->schedule[$week][$day][$index]['warmup_weight_percentage'] = 50;
        } else {
            $this->schedule[$week][$day][$index]['warmup_sets'] = null;
            $this->schedule[$week][$day][$index]['warmup_reps'] = null;
            $this->schedule[$week][$day][$index]['warmup_weight_percentage'] = null;
        }
        
        // Regenerate set details after toggling warmup
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function toggleTimeBased($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $this->schedule[$week][$day][$index]['is_time_based'] = !$this->schedule[$week][$day][$index]['is_time_based'];
        
        if ($this->schedule[$week][$day][$index]['is_time_based']) {
            $this->schedule[$week][$day][$index]['time_in_seconds'] = 60;
            $this->schedule[$week][$day][$index]['sets'] = 1;
            $this->schedule[$week][$day][$index]['reps'] = 0;
        } else {
            $this->schedule[$week][$day][$index]['time_in_seconds'] = null;
            $this->schedule[$week][$day][$index]['sets'] = 3;
            $this->schedule[$week][$day][$index]['reps'] = 10;
        }
        
        // Regenerate set details after switching modes
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function addSet($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $currentSets = $exercise['sets'] ?? 1;
        $totalWarmupSets = $exercise['has_warmup'] ? ($exercise['warmup_sets'] ?? 2) : 0;
        
        // Increment the number of sets
        $this->schedule[$week][$day][$index]['sets'] = $currentSets + 1;
        
        // Regenerate set details to include the new set
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function removeSet($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $currentSets = $exercise['sets'] ?? 1;
        
        // Don't allow less than 1 set
        if ($currentSets <= 1) {
            return;
        }
        
        // Decrement the number of sets
        $this->schedule[$week][$day][$index]['sets'] = $currentSets - 1;
        
        // Regenerate set details
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function addWarmupSet($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $currentWarmupSets = $exercise['warmup_sets'] ?? 2;
        
        // Increment the number of warmup sets
        $this->schedule[$week][$day][$index]['warmup_sets'] = $currentWarmupSets + 1;
        
        // Regenerate set details to include the new warmup set
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function removeWarmupSet($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $currentWarmupSets = $exercise['warmup_sets'] ?? 2;
        
        // Don't allow less than 1 warmup set
        if ($currentWarmupSets <= 1) {
            return;
        }
        
        // Decrement the number of warmup sets
        $this->schedule[$week][$day][$index]['warmup_sets'] = $currentWarmupSets - 1;
        
        // Regenerate set details
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function checkScheduleState()
    {
        \Log::info('Current schedule state', [
            'schedule' => $this->schedule,
            'schedule_count' => is_array($this->schedule) ? count($this->schedule) : 'not array',
            'current_week' => $this->currentWeek,
            'current_day' => $this->currentDay,
            'has_current_week' => isset($this->schedule[$this->currentWeek]),
            'has_current_day' => isset($this->schedule[$this->currentWeek][$this->currentDay]),
            'exercises_in_current_day' => isset($this->schedule[$this->currentWeek][$this->currentDay]) ? count($this->schedule[$this->currentWeek][$this->currentDay]) : 0,
        ]);
    }

    public function render()
    {
        return view('livewire.workout-planner', [
            'daysOfWeek' => $this->daysOfWeek,
            'categories' => [
                'chest' => 'Chest',
                'back' => 'Back',
                'legs' => 'Legs',
                'shoulders' => 'Shoulders',
                'arms' => 'Arms',
                'core' => 'Core',
                'cardio' => 'Cardio',
                'other' => 'Other',
            ],
        ]);
    }
}