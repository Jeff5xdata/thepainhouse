<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class WorkoutPlanView extends Component
{
    public $workoutPlan;
    public $exercises;
    public $showPrintModal = false;
    public $showCopyModal = false;
    public $currentWeek = 1;
    public $weekSchedule = [];
    public $sourceDay = null;
    public $targetWeek = null;
    public $targetDay = null;

    public $daysOfWeek = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    public function mount()
    {
        $this->workoutPlan = WorkoutPlan::where('user_id', auth()->id())->first();
        $this->exercises = Exercise::orderBy('name')->get();
    }

    public function togglePrintModal()
    {
        $this->showPrintModal = !$this->showPrintModal;
    }

    public function copyWorkoutModal($day)
    {
        $this->sourceDay = $day;
        $this->targetWeek = $this->currentWeek;
        $this->targetDay = $day;
        $this->showCopyModal = true;
    }

    public function toggleCopyModal()
    {
        $this->showCopyModal = false;
        $this->sourceDay = null;
        $this->targetWeek = null;
        $this->targetDay = null;
    }

    public function copyWorkout()
    {
        try {
            DB::beginTransaction();

            // Get source exercises
            $sourceExercises = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                ->where('week_number', $this->currentWeek)
                ->where('day_of_week', $this->sourceDay)
                ->orderBy('order_in_day')
                ->get();

            // Get max order in target day
            $maxOrder = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                ->where('week_number', $this->targetWeek)
                ->where('day_of_week', $this->targetDay)
                ->max('order_in_day') ?? 0;

            // Copy exercises to target day
            foreach ($sourceExercises as $index => $exercise) {
                $newExercise = $exercise->replicate();
                $newExercise->week_number = $this->targetWeek;
                $newExercise->day_of_week = $this->targetDay;
                $newExercise->order_in_day = $maxOrder + $index + 1;
                $newExercise->save();
            }

            DB::commit();
            Cache::flush();
            
            $this->toggleCopyModal();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Workout copied successfully'
            ]);
            
            if ($this->targetWeek == $this->currentWeek) {
                $this->loadWeekSchedule();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy workout: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to copy workout'
            ]);
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

    public function getScheduleForWeek($weekNumber)
    {
        $schedule = [];
        foreach ($this->daysOfWeek as $day => $dayName) {
            $schedule[$day] = $this->workoutPlan->getScheduleForDay($weekNumber, $day);
        }
        return $schedule;
    }

    public function moveExercise($day, $scheduleItemId, $direction)
    {
        try {
            DB::beginTransaction();

            // Get the current exercise
            $currentExercise = WorkoutPlanSchedule::where('id', $scheduleItemId)->first();
            if (!$currentExercise) {
                DB::rollBack();
                return;
            }

            // Get the exercise to swap with
            $swapExercise = null;
            if ($direction === 'up') {
                $swapExercise = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                    ->where('week_number', $this->currentWeek)
                    ->where('day_of_week', $day)
                    ->where('order_in_day', '<', $currentExercise->order_in_day)
                    ->orderBy('order_in_day', 'desc')
                    ->first();
            } else {
                $swapExercise = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                    ->where('week_number', $this->currentWeek)
                    ->where('day_of_week', $day)
                    ->where('order_in_day', '>', $currentExercise->order_in_day)
                    ->orderBy('order_in_day', 'asc')
                    ->first();
            }

            if (!$swapExercise) {
                DB::rollBack();
                return;
            }

            // Get the max order for this day to use as a temporary value
            $maxOrder = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                ->where('week_number', $this->currentWeek)
                ->where('day_of_week', $day)
                ->max('order_in_day');

            $tempOrder = $maxOrder + 100; // Use a temporary order well above the max

            // Store the original orders
            $currentOrder = $currentExercise->order_in_day;
            $swapOrder = $swapExercise->order_in_day;

            // First move current exercise to temporary position
            DB::update(
                'update workout_plan_schedule set order_in_day = ? where id = ?', 
                [$tempOrder, $currentExercise->id]
            );

            // Then move swap exercise to current exercise's position
            DB::update(
                'update workout_plan_schedule set order_in_day = ? where id = ?', 
                [$currentOrder, $swapExercise->id]
            );

            // Finally move current exercise to swap exercise's original position
            DB::update(
                'update workout_plan_schedule set order_in_day = ? where id = ?', 
                [$swapOrder, $currentExercise->id]
            );

            DB::commit();
            Cache::flush();
            $this->dispatch('exerciseReordered');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to move exercise: ' . $e->getMessage());
        }
    }

    public function nextWeek()
    {
        $this->currentWeek = min(52, $this->currentWeek + 1);
        $this->loadWeekSchedule();
    }

    public function previousWeek()
    {
        $this->currentWeek = max(1, $this->currentWeek - 1);
        $this->loadWeekSchedule();
    }

    protected function loadWeekSchedule()
    {
        $this->weekSchedule = [];
        
        if ($this->workoutPlan) {
            foreach ($this->daysOfWeek as $day => $dayName) {
                $this->weekSchedule[$day] = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                    ->where('week_number', $this->currentWeek)
                    ->where('day_of_week', $day)
                    ->orderBy('order_in_day')
                    ->get();
            }
        }
    }

    public function render()
    {
        $this->weekSchedule = [];
        
        if ($this->workoutPlan) {
            foreach ($this->daysOfWeek as $day => $dayName) {
                $this->weekSchedule[$day] = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
                    ->where('week_number', $this->currentWeek)
                    ->where('day_of_week', $day)
                    ->orderBy('order_in_day')
                    ->with('exercise')
                    ->get();
            }
        }

        return view('livewire.workout-plan-view', [
            'weekSchedule' => $this->weekSchedule,
            'daysOfWeek' => $this->daysOfWeek
        ]);
    }
} 