<?php

namespace App\Livewire;

use App\Models\WorkoutSetting;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.navigation')]
class WorkoutSettings extends Component
{
    public $defaultRestTimer = 60;
    public $defaultWarmupSets = 2;
    public $defaultWarmupReps = 10;
    public $defaultWarmupWeightPercentage = 50;
    public $defaultWorkSets = 3;
    public $defaultWorkReps = 10;

    public function mount()
    {
        // Load settings from database
        $settings = auth()->user()->workoutSettings;
        
        if ($settings) {
            $this->defaultRestTimer = $settings->default_rest_timer;
            $this->defaultWarmupSets = $settings->default_warmup_sets;
            $this->defaultWarmupReps = $settings->default_warmup_reps;
            $this->defaultWarmupWeightPercentage = $settings->default_warmup_weight_percentage;
            $this->defaultWorkSets = $settings->default_work_sets;
            $this->defaultWorkReps = $settings->default_work_reps;
        } else {
            // Create default settings for new users
            $settings = WorkoutSetting::create([
                'user_id' => auth()->id(),
                'default_rest_timer' => $this->defaultRestTimer,
                'default_warmup_sets' => $this->defaultWarmupSets,
                'default_warmup_reps' => $this->defaultWarmupReps,
                'default_warmup_weight_percentage' => $this->defaultWarmupWeightPercentage,
                'default_work_sets' => $this->defaultWorkSets,
                'default_work_reps' => $this->defaultWorkReps,
            ]);
        }
    }

    public function saveSettings()
    {
        // Validate the input
        $this->validate([
            'defaultRestTimer' => 'required|integer|min:10|max:300',
            'defaultWarmupSets' => 'required|integer|min:0|max:5',
            'defaultWarmupReps' => 'required|integer|min:1|max:30',
            'defaultWarmupWeightPercentage' => 'required|integer|min:10|max:90',
            'defaultWorkSets' => 'required|integer|min:1|max:10',
            'defaultWorkReps' => 'required|integer|min:1|max:30',
        ]);

        // Update or create settings in database
        WorkoutSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'default_rest_timer' => $this->defaultRestTimer,
                'default_warmup_sets' => $this->defaultWarmupSets,
                'default_warmup_reps' => $this->defaultWarmupReps,
                'default_warmup_weight_percentage' => $this->defaultWarmupWeightPercentage,
                'default_work_sets' => $this->defaultWorkSets,
                'default_work_reps' => $this->defaultWorkReps,
            ]
        );

        $this->dispatch('settings-saved');
    }

    public function render()
    {
        return view('livewire.workout-settings');
    }
} 