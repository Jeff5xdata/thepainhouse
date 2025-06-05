<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;

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

    public function mount()
    {
        $this->exercises = Exercise::orderBy('name')->get();
        $this->filteredExercises = $this->exercises;
        $this->existingPlan = WorkoutPlan::where('user_id', auth()->id())->first();

        if ($this->existingPlan) {
            $this->name = $this->existingPlan->name;
            $this->description = $this->existingPlan->description;
            $this->weeks_duration = $this->existingPlan->weeks_duration;
            
            // Load schedule from WorkoutPlanSchedule
            for ($week = 1; $week <= $this->weeks_duration; $week++) {
                foreach ($this->daysOfWeek as $day => $dayName) {
                    $scheduleItems = $this->existingPlan->getScheduleForDay($week, $day);
                    if ($scheduleItems->isNotEmpty()) {
                        $this->schedule[$week][$day] = $scheduleItems->map(function($item) {
                            return [
                                'exercise_id' => $item->exercise_id,
                                'sets' => $item->sets,
                                'reps' => $item->reps,
                                'is_time_based' => $item->is_time_based,
                                'time_in_seconds' => $item->time_in_seconds,
                                'has_warmup' => $item->has_warmup,
                                'warmup_sets' => $item->warmup_sets,
                                'warmup_reps' => $item->warmup_reps,
                                'warmup_time_in_seconds' => $item->warmup_time_in_seconds,
                                'warmup_weight_percentage' => $item->warmup_weight_percentage,
                                'order_in_day' => $item->order_in_day,
                            ];
                        })->toArray();
                    }
                }
            }
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

        $this->schedule[$week][$day][] = [
            'exercise_id' => $exerciseId,
            'sets' => 3,
            'reps' => 10,
            'is_time_based' => false,
            'time_in_seconds' => null,
            'has_warmup' => false,
            'warmup_sets' => null,
            'warmup_reps' => null,
            'warmup_time_in_seconds' => null,
            'warmup_weight_percentage' => null,
            'order_in_day' => $orderInDay,
        ];
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
        }
    }

    public function confirmSave()
    {
        if ($this->existingPlan) {
            $this->toggleConfirmModal();
        } else {
            $this->save();
        }
    }

    public function deletePlan()
    {
        if ($this->existingPlan) {
            $this->existingPlan->delete();
            $this->existingPlan = null;
            $this->name = '';
            $this->description = '';
            $this->weeks_duration = 1;
            $this->schedule = [];
            $this->initializeSchedule();
            $this->showDeleteConfirmModal = false;
            session()->flash('message', 'Workout plan deleted successfully!');
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->existingPlan) {
            // Delete existing schedule items
            $this->existingPlan->scheduleItems()->delete();
            $this->existingPlan->update([
                'name' => $this->name,
                'description' => $this->description,
                'weeks_duration' => $this->weeks_duration,
            ]);
        } else {
            $this->existingPlan = WorkoutPlan::create([
                'name' => $this->name,
                'description' => $this->description,
                'weeks_duration' => $this->weeks_duration,
                'user_id' => auth()->id(),
            ]);
        }

        // Create new schedule items
        foreach ($this->schedule as $week => $days) {
            foreach ($days as $day => $exercises) {
                foreach ($exercises as $exercise) {
                    // For time-based exercises, ensure reps is 0
                    $reps = $exercise['is_time_based'] ? 0 : ($exercise['reps'] ?? 10);
                    
                    WorkoutPlanSchedule::create([
                        'workout_plan_id' => $this->existingPlan->id,
                        'exercise_id' => $exercise['exercise_id'],
                        'week_number' => $week,
                        'day_of_week' => $day,
                        'order_in_day' => $exercise['order_in_day'],
                        'is_time_based' => $exercise['is_time_based'],
                        'sets' => $exercise['sets'],
                        'reps' => $reps,
                        'time_in_seconds' => $exercise['time_in_seconds'],
                        'has_warmup' => $exercise['has_warmup'],
                        'warmup_sets' => $exercise['warmup_sets'],
                        'warmup_reps' => $exercise['warmup_reps'],
                        'warmup_time_in_seconds' => $exercise['warmup_time_in_seconds'],
                        'warmup_weight_percentage' => $exercise['warmup_weight_percentage'],
                    ]);
                }
            }
        }

        $this->showConfirmModal = false;
        $this->dispatch('workoutPlanCreated', $this->existingPlan->id);
        session()->flash('message', $this->existingPlan ? 'Workout plan updated successfully!' : 'Workout plan created successfully!');
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

        $this->schedule[$this->currentWeek][$this->currentDay][] = [
            'exercise_id' => $exerciseId,
            'sets' => 3,
            'reps' => 10,
            'is_time_based' => false,
            'time_in_seconds' => null,
            'has_warmup' => false,
            'warmup_sets' => null,
            'warmup_reps' => null,
            'warmup_time_in_seconds' => null,
            'warmup_weight_percentage' => null,
            'order_in_day' => $orderInDay,
        ];

        $this->showExerciseModal = false;
    }

    public function toggleWarmup($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $this->schedule[$week][$day][$index]['has_warmup'] = !$this->schedule[$week][$day][$index]['has_warmup'];
        
        if ($this->schedule[$week][$day][$index]['has_warmup']) {
            $this->schedule[$week][$day][$index]['warmup_sets'] = 2;
            $this->schedule[$week][$day][$index]['warmup_reps'] = 10;
            $this->schedule[$week][$day][$index]['warmup_weight_percentage'] = 50;
        } else {
            $this->schedule[$week][$day][$index]['warmup_sets'] = null;
            $this->schedule[$week][$day][$index]['warmup_reps'] = null;
            $this->schedule[$week][$day][$index]['warmup_weight_percentage'] = null;
        }
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