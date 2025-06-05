<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class WorkoutPlanView extends Component
{
    public $workoutPlan;
    public $exercises;
    public $showPrintModal = false;
    public $currentWeek = 1;

    protected $daysOfWeek = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday'
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

    public function render()
    {
        $weekSchedule = $this->workoutPlan ? $this->getScheduleForWeek($this->currentWeek) : [];

        return view('livewire.workout-plan-view', [
            'daysOfWeek' => $this->daysOfWeek,
            'weekSchedule' => $weekSchedule,
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