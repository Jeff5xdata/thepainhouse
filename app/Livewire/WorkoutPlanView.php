<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use App\Models\WorkoutSession;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

#[Layout('layouts.navigation')]
class WorkoutPlanView extends Component
{
    public $workoutPlan;
    public $exercises;
    public $showPrintModal = false;
    public $showCopyModal = false;
    public $currentWeek;
    public $weekSchedule = [];
    public $sourceDay = null;
    public $targetWeek = null;
    public $targetDay = null;
    public $selectedClientId = null;
    public $clients = [];
    public $isTrainer = false;
    public $incompleteWorkouts = [];

    public $daysOfWeek = [
        1 => 'Monday',
        2 => 'Tuesday', 
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public function mount()
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            session()->flash('error', 'Please log in to view your workout plan.');
            $this->redirect(route('login'));
            return;
        }
        
        $this->workoutPlan = WorkoutPlan::where('user_id', auth()->id())->first();
        $this->exercises = Exercise::orderBy('name')->get();
        $this->currentWeek = Carbon::now()->isoWeek();
        
        // Check if user is a trainer and load clients
        $user = auth()->user();
        $this->isTrainer = $user->isTrainer();
        if ($this->isTrainer) {
            $this->clients = $user->clients()->orderBy('name')->get();
        }
        
        // Load incomplete workouts
        $this->loadIncompleteWorkouts();
    }

    protected function loadIncompleteWorkouts()
    {
        if (!$this->workoutPlan) {
            $this->incompleteWorkouts = [];
            return;
        }

        // Get incomplete workout sessions (in_progress, planned, or recently skipped)
        $incompleteSessions = WorkoutSession::where('workout_plan_id', $this->workoutPlan->id)
            ->where('user_id', auth()->id())
            ->where(function($query) {
                $query->whereIn('status', ['in_progress', 'planned'])
                      ->orWhere(function($q) {
                          $q->where('status', 'skipped')
                            ->where('date', '>=', now()->subDays(7)); // Only show skipped workouts from last 7 days
                      });
            })
            ->orderBy('date', 'desc')
            ->with(['exerciseSets.exercise'])
            ->get();

        $this->incompleteWorkouts = $incompleteSessions->map(function ($session) {
            // Convert day_of_week string to proper day name
            $dayName = ucfirst($session->day_of_week);
            
            // Get exercises that are not completed
            $incompleteExercises = $session->exerciseSets()
                ->where('completed', false)
                ->with('exercise')
                ->get()
                ->groupBy('exercise_id')
                ->map(function ($sets) {
                    $firstSet = $sets->first();
                    return [
                        'exercise_name' => $firstSet->exercise->name,
                        'sets_count' => $sets->count(),
                        'completed_sets' => $sets->where('completed', true)->count(),
                    ];
                })
                ->values();

            return [
                'id' => $session->id,
                'date' => $session->date,
                'day_name' => $dayName,
                'week_number' => $session->week_number,
                'status' => $session->status,
                'incomplete_exercises' => $incompleteExercises,
                'total_exercises' => $session->exerciseSets()->distinct('exercise_id')->count(),
                'completed_exercises' => $session->exerciseSets()->where('completed', true)->distinct('exercise_id')->count(),
            ];
        })->toArray();
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
        $this->selectedClientId = null;
        $this->showCopyModal = true;
    }

    public function toggleCopyModal()
    {
        $this->showCopyModal = false;
        $this->sourceDay = null;
        $this->targetWeek = null;
        $this->targetDay = null;
        $this->selectedClientId = null;
    }

    public function refreshIncompleteWorkouts()
    {
        $this->loadIncompleteWorkouts();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Incomplete workouts refreshed'
        ]);
    }

    public function skipWorkout($workoutId)
    {
        try {
            $workout = WorkoutSession::where('id', $workoutId)
                ->where('user_id', auth()->id())
                ->first();

            if ($workout) {
                $workout->update(['status' => 'skipped']);
                $this->loadIncompleteWorkouts();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Workout marked as skipped'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to skip workout'
            ]);
        }
    }

    public function resumeWorkout($workoutId)
    {
        try {
            $workout = WorkoutSession::where('id', $workoutId)
                ->where('user_id', auth()->id())
                ->first();

            if ($workout) {
                $workout->update(['status' => 'in_progress']);
                $this->loadIncompleteWorkouts();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Workout resumed'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to resume workout'
            ]);
        }
    }

    public function copyWorkout()
    {
        try {
            DB::beginTransaction();

            // If a client is selected, copy to client's workout plan
            if ($this->selectedClientId) {
                $this->copyWorkoutToClient();
            } else {
                // Copy within the same workout plan
                $this->copyWorkoutWithinPlan();
            }

            DB::commit();
            Cache::flush();
            
            $this->toggleCopyModal();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $this->selectedClientId ? 'Workout copied to client successfully' : 'Workout copied successfully'
            ]);
            
            if (!$this->selectedClientId && $this->targetWeek == $this->currentWeek) {
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

    protected function copyWorkoutWithinPlan()
    {
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
    }

    protected function copyWorkoutToClient()
    {
        $client = User::find($this->selectedClientId);
        if (!$client || $client->my_trainer !== auth()->id()) {
            throw new \Exception('Unauthorized access to client');
        }

        // Get or create client's workout plan
        $clientWorkoutPlan = $client->workoutPlans()->first();
        if (!$clientWorkoutPlan) {
            $clientWorkoutPlan = WorkoutPlan::create([
                'user_id' => $client->id,
                'name' => $this->workoutPlan->name . ' (Copied)',
                'description' => $this->workoutPlan->description,
                'weeks_duration' => $this->workoutPlan->weeks_duration,
                'is_active' => true,
            ]);
        }

        // Get source exercises
        $sourceExercises = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
            ->where('week_number', $this->currentWeek)
            ->where('day_of_week', $this->sourceDay)
            ->orderBy('order_in_day')
            ->get();

        // Get max order in target day for client's plan
        $maxOrder = WorkoutPlanSchedule::where('workout_plan_id', $clientWorkoutPlan->id)
            ->where('week_number', $this->targetWeek)
            ->where('day_of_week', $this->targetDay)
            ->max('order_in_day') ?? 0;

        // Copy exercises to client's workout plan
        foreach ($sourceExercises as $index => $exercise) {
            $newExercise = $exercise->replicate();
            $newExercise->workout_plan_id = $clientWorkoutPlan->id;
            $newExercise->week_number = $this->targetWeek;
            $newExercise->day_of_week = $this->targetDay;
            $newExercise->order_in_day = $maxOrder + $index + 1;
            $newExercise->save();
        }

        // Send notification to client
        Message::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $client->id,
            'subject' => 'New Workout Assigned',
            'content' => "Your trainer has assigned you a new workout for {$this->daysOfWeek[$this->targetDay]} (Week {$this->targetWeek}). Check your workout plan to get started!",
            'is_read' => false,
        ]);
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
                Log::error('Current exercise not found: ' . $scheduleItemId);
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
                Log::info('No exercise to swap with for direction: ' . $direction);
                return;
            }

            // Store the original orders
            $currentOrder = $currentExercise->order_in_day;
            $swapOrder = $swapExercise->order_in_day;

            Log::info("Swapping exercises: Current ID {$currentExercise->id} (order {$currentOrder}) with Swap ID {$swapExercise->id} (order {$swapOrder})");

            // Swap the order values
            $currentExercise->order_in_day = $swapOrder;
            $currentExercise->save();

            $swapExercise->order_in_day = $currentOrder;
            $swapExercise->save();

            DB::commit();
            Cache::flush();
            
            // Refresh the week schedule
            $this->loadWeekSchedule();
            
            $this->dispatch('exerciseReordered');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to move exercise: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to move exercise'
            ]);
        }
    }

    public function nextWeek()
    {
        // Find the next week that has data
        $nextWeek = $this->currentWeek + 1;
        while ($nextWeek <= 53) { // ISO weeks can go up to 53
            if ($this->weekHasData($nextWeek)) {
                $this->currentWeek = $nextWeek;
                $this->loadWeekSchedule();
                return;
            }
            $nextWeek++;
        }
    }

    public function previousWeek()
    {
        // Find the previous week that has data
        $prevWeek = $this->currentWeek - 1;
        while ($prevWeek >= 1) {
            if ($this->weekHasData($prevWeek)) {
                $this->currentWeek = $prevWeek;
                $this->loadWeekSchedule();
                return;
            }
            $prevWeek--;
        }
    }

    protected function weekHasData($weekNumber)
    {
        if (!$this->workoutPlan) {
            return false;
        }

        return WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
            ->where('week_number', $weekNumber)
            ->exists();
    }

    public function hasNextWeek()
    {
        if (!$this->workoutPlan) {
            return false;
        }

        $nextWeek = $this->currentWeek + 1;
        while ($nextWeek <= 53) {
            if ($this->weekHasData($nextWeek)) {
                return true;
            }
            $nextWeek++;
        }
        return false;
    }

    public function hasPreviousWeek()
    {
        if (!$this->workoutPlan) {
            return false;
        }

        $prevWeek = $this->currentWeek - 1;
        while ($prevWeek >= 1) {
            if ($this->weekHasData($prevWeek)) {
                return true;
            }
            $prevWeek--;
        }
        return false;
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
                    ->with('exercise')
                    ->get();
            }
        }
    }

    public function moveExerciseCard($day, $exerciseId, $direction)
    {
        $allGroups = WorkoutPlanSchedule::where('workout_plan_id', $this->workoutPlan->id)
            ->where('week_number', $this->currentWeek)
            ->where('day_of_week', $day)
            ->orderBy('order_in_day')
            ->with('exercise')
            ->get()
            ->groupBy('exercise_id')
            ->values();

        $currentIndex = $allGroups->search(function ($g) use ($exerciseId) {
            return $g->first()->exercise_id == $exerciseId;
        });

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
        if ($swapIndex < 0 || $swapIndex >= $allGroups->count()) return;

        $currentGroup = $allGroups[$currentIndex];
        $swapGroup = $allGroups[$swapIndex];

        // Assign temporary high order to avoid unique constraint
        $tempOrder = 1000;
        foreach ($currentGroup as $item) {
            $item->order_in_day += $tempOrder;
            $item->save();
        }
        foreach ($swapGroup as $item) {
            $item->order_in_day += $tempOrder * 2;
            $item->save();
        }

        // Reorder all sets
        $order = 0;
        $all = $allGroups->toArray();
        [$all[$currentIndex], $all[$swapIndex]] = [$all[$swapIndex], $all[$currentIndex]];
        foreach ($all as $group) {
            foreach ($group as $item) {
                $model = WorkoutPlanSchedule::find($item['id']);
                $model->order_in_day = $order++;
                $model->save();
            }
        }

        $this->loadWeekSchedule();
        $this->dispatch('exerciseReordered');
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

        // Reload incomplete workouts
        $this->loadIncompleteWorkouts();

        return view('livewire.workout-plan-view', [
            'weekSchedule' => $this->weekSchedule,
            'daysOfWeek' => $this->daysOfWeek,
            'isTrainer' => $this->isTrainer,
            'clients' => $this->clients,
            'incompleteWorkouts' => $this->incompleteWorkouts
        ]);
    }
} 