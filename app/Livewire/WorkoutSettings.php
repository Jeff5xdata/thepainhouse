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
    public $weightUnitPreference = 'kg';
    
    // Quit Smoking Settings
    public $quitDate;
    public $packPrice = 10.00;
    public $cigarettesPerPack = 20;
    public $maxCigarettesPerDay = 0;
    public $enableReductionPlan = false;
    
    // Push Notification Settings
    public $enableSmokeReminders = true;
    public $enableDailyProgress = true;
    public $enableMilestoneCelebrations = true;

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

        // Load user's weight unit preference
        $this->weightUnitPreference = auth()->user()->getPreferredWeightUnit();
        
        // Load quit smoking settings
        $this->quitDate = auth()->user()->quit_date;
        $this->packPrice = auth()->user()->pack_price ?? 10.00;
        $this->cigarettesPerPack = auth()->user()->cigarettes_per_pack ?? 20;
        $this->maxCigarettesPerDay = auth()->user()->max_cigarettes_per_day ?? 0;
        $this->enableReductionPlan = auth()->user()->enable_reduction_plan ?? false;
        
        // Load push notification settings
        $this->enableSmokeReminders = auth()->user()->enable_smoke_reminders ?? true;
        $this->enableDailyProgress = auth()->user()->enable_daily_progress ?? true;
        $this->enableMilestoneCelebrations = auth()->user()->enable_milestone_celebrations ?? true;
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
            'weightUnitPreference' => 'required|in:kg,lbs',
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

        // Update user's weight unit preference
        auth()->user()->update([
            'weight_unit_preference' => $this->weightUnitPreference,
        ]);

        $this->dispatch('settings-saved');
    }
    
    public function saveQuitSmokingSettings()
    {
        // Validate the quit smoking input
        $this->validate([
            'quitDate' => 'nullable|date|before_or_equal:today',
            'packPrice' => 'required|numeric|min:0|max:1000',
            'cigarettesPerPack' => 'required|integer|min:1|max:50',
            'maxCigarettesPerDay' => 'required|integer|min:0|max:100',
            'enableReductionPlan' => 'boolean',
        ]);

        // Update user's quit smoking settings
        auth()->user()->update([
            'quit_date' => $this->quitDate,
            'pack_price' => $this->packPrice,
            'cigarettes_per_pack' => $this->cigarettesPerPack,
            'max_cigarettes_per_day' => $this->maxCigarettesPerDay,
            'enable_reduction_plan' => $this->enableReductionPlan,
            'enable_smoke_reminders' => $this->enableSmokeReminders,
            'enable_daily_progress' => $this->enableDailyProgress,
            'enable_milestone_celebrations' => $this->enableMilestoneCelebrations,
        ]);

        $this->dispatch('settings-saved');
    }

    public function render()
    {
        return view('livewire.workout-settings');
    }
} 