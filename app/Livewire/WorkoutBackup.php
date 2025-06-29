<?php

namespace App\Livewire;

use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use App\Models\ExerciseSet;
use App\Models\WorkoutSetting;
use App\Models\Exercise;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.navigation')]
class WorkoutBackup extends Component
{
    use WithFileUploads;

    public $backupFile;
    public $showRestoreModal = false;
    public $restorePreview = [];
    public $restoreOptions = [
        'overwrite_existing' => false,
        'include_settings' => true,
        'include_history' => true,
    ];

    public function mount()
    {
        // Check for session messages
        if (session()->has('success')) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => session('success')
            ]);
        }
        
        if (session()->has('error')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => session('error')
            ]);
        }
    }

    public function createBackup()
    {
        // Redirect to controller for download
        return redirect()->route('workout.backup.download');
    }

    public function previewRestore()
    {
        $this->validate([
            'backupFile' => 'required|file|mimes:json|max:10240', // 10MB max
        ]);

        try {
            $jsonContent = file_get_contents($this->backupFile->getRealPath());
            $backupData = json_decode($jsonContent, true);

            if (!$backupData || !isset($backupData['version'])) {
                throw new \Exception('Invalid backup file format');
            }

            $this->restorePreview = [
                'version' => $backupData['version'],
                'created_at' => $backupData['created_at'],
                'user' => $backupData['user'],
                'workout_plans_count' => count($backupData['workout_plans'] ?? []),
                'workout_sessions_count' => count($backupData['workout_sessions'] ?? []),
                'exercise_sets_count' => count($backupData['exercise_sets'] ?? []),
                'has_settings' => !empty($backupData['workout_settings']),
                'backup_data' => $backupData,
            ];

            $this->showRestoreModal = true;

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to read backup file: ' . $e->getMessage()
            ]);
        }
    }

    public function restoreBackup()
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $backupData = $this->restorePreview['backup_data'];

            // Restore workout settings if option is enabled
            if ($this->restoreOptions['include_settings'] && !empty($backupData['workout_settings'])) {
                $settingsData = $backupData['workout_settings'];
                unset($settingsData['id'], $settingsData['user_id'], $settingsData['created_at'], $settingsData['updated_at']);
                
                WorkoutSetting::updateOrCreate(
                    ['user_id' => $user->id],
                    $settingsData
                );
            }

            // Restore workout plans
            foreach ($backupData['workout_plans'] as $planData) {
                // Check if plan already exists
                $existingPlan = WorkoutPlan::where('user_id', $user->id)
                    ->where('name', $planData['name'])
                    ->first();

                if ($existingPlan && !$this->restoreOptions['overwrite_existing']) {
                    // Skip existing plans if not overwriting
                    continue;
                }

                if ($existingPlan && $this->restoreOptions['overwrite_existing']) {
                    // Delete existing plan and related data
                    $existingPlan->delete();
                }

                // Create new plan
                $planCreateData = [
                    'name' => $planData['name'],
                    'description' => $planData['description'] ?? '',
                    'weeks_duration' => $planData['weeks_duration'] ?? 1,
                    'user_id' => $user->id,
                    'is_active' => $planData['is_active'] ?? true,
                ];

                $newPlan = WorkoutPlan::create($planCreateData);

                // Restore plan exercises
                if (!empty($planData['exercises'])) {
                    foreach ($planData['exercises'] as $exerciseData) {
                        // Find exercise by name (assuming exercises exist in the system)
                        $exercise = Exercise::where('name', $exerciseData['exercise_name'])->first();
                        
                        if ($exercise) {
                            $pivotData = $exerciseData['pivot'];
                            unset($pivotData['workout_plan_id'], $pivotData['exercise_id'], $pivotData['created_at'], $pivotData['updated_at']);
                            $newPlan->exercises()->attach($exercise->id, $pivotData);
                        }
                    }
                }

                // Restore schedule items
                if (!empty($planData['schedule_items'])) {
                    foreach ($planData['schedule_items'] as $scheduleData) {
                        $exercise = Exercise::where('name', $scheduleData['exercise_name'])->first();
                        
                        if ($exercise) {
                            $scheduleInsertData = $scheduleData['schedule_data'];
                            $scheduleInsertData['workout_plan_id'] = $newPlan->id;
                            $scheduleInsertData['exercise_id'] = $exercise->id;
                            unset($scheduleInsertData['id'], $scheduleInsertData['created_at'], $scheduleInsertData['updated_at']);
                            
                            DB::table('workout_plan_schedule')->insert($scheduleInsertData);
                        }
                    }
                }
            }

            // Restore workout sessions and exercise sets if option is enabled
            if ($this->restoreOptions['include_history']) {
                foreach ($backupData['workout_sessions'] as $sessionData) {
                    // Find the corresponding workout plan
                    $plan = WorkoutPlan::where('user_id', $user->id)
                        ->where('name', $sessionData['workout_plan_name'] ?? 'Unknown Plan')
                        ->first();

                    if ($plan) {
                        $sessionCreateData = [
                            'user_id' => $user->id,
                            'workout_plan_id' => $plan->id,
                            'name' => $sessionData['name'] ?? 'Restored Session',
                            'date' => $sessionData['date'],
                            'week_number' => $sessionData['week_number'] ?? 1,
                            'day_of_week' => $sessionData['day_of_week'] ?? 'monday',
                            'status' => $sessionData['status'] ?? 'completed',
                            'notes' => $sessionData['notes'] ?? null,
                            'completed_at' => $sessionData['completed_at'] ?? null,
                        ];

                        $newSession = WorkoutSession::create($sessionCreateData);

                        // Restore exercise sets for this session
                        foreach ($backupData['exercise_sets'] as $setData) {
                            if ($setData['set_data']['workout_session_id'] == $sessionData['id']) {
                                $exercise = Exercise::where('name', $setData['exercise_name'])->first();
                                
                                if ($exercise) {
                                    $setCreateData = [
                                        'workout_session_id' => $newSession->id,
                                        'exercise_id' => $exercise->id,
                                        'set_number' => $setData['set_data']['set_number'] ?? 1,
                                        'reps' => $setData['set_data']['reps'] ?? 0,
                                        'weight' => $setData['set_data']['weight'] ?? null,
                                        'completed' => $setData['set_data']['completed'] ?? false,
                                        'is_warmup' => $setData['set_data']['is_warmup'] ?? false,
                                        'time_in_seconds' => $setData['set_data']['time_in_seconds'] ?? null,
                                        'notes' => $setData['set_data']['notes'] ?? null,
                                    ];

                                    ExerciseSet::create($setCreateData);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            
            $this->showRestoreModal = false;
            $this->restorePreview = [];
            $this->backupFile = null;
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Backup restored successfully!'
            ]);
            
            // Redirect to dashboard
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to restore backup: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelRestore()
    {
        $this->showRestoreModal = false;
        $this->restorePreview = [];
        $this->backupFile = null;
    }

    public function render()
    {
        return view('livewire.workout-backup');
    }
} 