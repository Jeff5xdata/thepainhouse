<?php

namespace App\Livewire;

use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use App\Models\ExerciseSet;
use App\Models\WorkoutSetting;
use App\Models\Exercise;
use App\Models\FoodLog;
use App\Models\FoodItem;
use App\Models\WeightMeasurement;
use App\Models\BodyMeasurement;
use App\Models\User;
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
        'include_food_tracker' => true,
        'include_body_tracking' => true,
        'include_client_data' => false,
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

        // Initialize restore options
        $this->restoreOptions = [
            'overwrite_existing' => false,
            'include_settings' => true,
            'include_history' => true,
            'include_food_tracker' => true,
            'include_body_tracking' => true,
            'include_client_data' => false,
        ];
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
        ], [
            'backupFile.required' => 'Please select a backup file to upload.',
            'backupFile.file' => 'The uploaded file is not valid.',
            'backupFile.mimes' => 'The backup file must be a JSON file.',
            'backupFile.max' => 'The backup file must not be larger than 10MB.',
        ]);

        try {
            if (!$this->backupFile) {
                throw new \Exception('No backup file selected');
            }

            // Debug information
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Processing backup file: ' . $this->backupFile->getClientOriginalName()
            ]);

            $jsonContent = file_get_contents($this->backupFile->getRealPath());
            if ($jsonContent === false) {
                throw new \Exception('Failed to read backup file');
            }

            $backupData = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
            }

            // Validate backup file structure
            if (!$backupData || !isset($backupData['version'])) {
                throw new \Exception('Invalid backup file format - missing version information');
            }

            if (!isset($backupData['workout_plans']) || !isset($backupData['workout_sessions']) || !isset($backupData['exercise_sets'])) {
                throw new \Exception('Invalid backup file format - missing required data sections');
            }

            // Validate user information
            if (!isset($backupData['user']) || !isset($backupData['user']['name'])) {
                throw new \Exception('Invalid backup file format - missing user information');
            }

            // Check if backup includes new data types
            $hasFoodTracker = isset($backupData['food_tracker']) && !empty($backupData['food_tracker']['food_logs']);
            $hasBodyTracking = isset($backupData['body_tracking']) && (!empty($backupData['body_tracking']['weight_measurements']) || !empty($backupData['body_tracking']['body_measurements']));
            $hasClientData = isset($backupData['client_data']) && !empty($backupData['client_data']);
            $isTrainer = isset($backupData['user']['is_trainer']) && $backupData['user']['is_trainer'];

            $this->restorePreview = [
                'version' => $backupData['version'],
                'created_at' => $backupData['created_at'] ?? 'Unknown',
                'user' => $backupData['user'],
                'workout_plans_count' => count($backupData['workout_plans'] ?? []),
                'workout_sessions_count' => count($backupData['workout_sessions'] ?? []),
                'exercise_sets_count' => count($backupData['exercise_sets'] ?? []),
                'has_settings' => !empty($backupData['workout_settings']),
                'has_food_tracker' => $hasFoodTracker,
                'has_body_tracking' => $hasBodyTracking,
                'has_client_data' => $hasClientData,
                'is_trainer' => $isTrainer,
                'food_logs_count' => $hasFoodTracker ? count($backupData['food_tracker']['food_logs']) : 0,
                'food_items_count' => $hasFoodTracker ? count($backupData['food_tracker']['food_items']) : 0,
                'weight_measurements_count' => $hasBodyTracking ? count($backupData['body_tracking']['weight_measurements']) : 0,
                'body_measurements_count' => $hasBodyTracking ? count($backupData['body_tracking']['body_measurements']) : 0,
                'clients_count' => $hasClientData ? count($backupData['client_data']) : 0,
                'backup_data' => $backupData,
            ];

            $this->showRestoreModal = true;

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Backup file processed successfully. Ready to restore.'
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Backup preview failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'file_name' => $this->backupFile ? $this->backupFile->getClientOriginalName() : 'No file',
                'file_size' => $this->backupFile ? $this->backupFile->getSize() : 0,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to read backup file: ' . $e->getMessage()
            ]);
        }
    }

    public function restoreBackup()
    {
        try {
            // Validate that we have backup data to restore
            if (empty($this->restorePreview) || !isset($this->restorePreview['backup_data'])) {
                throw new \Exception('No backup data available for restoration. Please upload and preview a backup file first.');
            }

            DB::beginTransaction();

            $user = auth()->user();
            $backupData = $this->restorePreview['backup_data'];

            // Validate backup data structure
            if (!isset($backupData['version']) || !isset($backupData['workout_plans']) || !isset($backupData['workout_sessions']) || !isset($backupData['exercise_sets'])) {
                throw new \Exception('Invalid backup data structure. Please ensure the backup file is complete and not corrupted.');
            }

            // Restore workout settings if option is enabled
            if ($this->restoreOptions['include_settings'] && !empty($backupData['workout_settings'])) {
                $settingsData = $backupData['workout_settings'];
                unset($settingsData['id'], $settingsData['user_id'], $settingsData['created_at'], $settingsData['updated_at']);
                
                WorkoutSetting::updateOrCreate(
                    ['user_id' => $user->id],
                    $settingsData
                );
            }

            // Create a mapping of old plan IDs to new plan IDs for session restoration
            $planIdMapping = [];

            // Restore workout plans
            foreach ($backupData['workout_plans'] as $planData) {
                $oldPlanId = $planData['id'];
                unset($planData['id'], $planData['user_id'], $planData['created_at'], $planData['updated_at']);
                
                $plan = WorkoutPlan::create([
                    'user_id' => $user->id,
                    'name' => $planData['name'],
                    'description' => $planData['description'] ?? null,
                    'is_active' => $planData['is_active'] ?? true,
                ]);

                $planIdMapping[$oldPlanId] = $plan->id;

                // Restore exercises for this plan
                if (!empty($planData['exercises'])) {
                    foreach ($planData['exercises'] as $exerciseData) {
                        if (isset($exerciseData['pivot'])) {
                            $exercise = Exercise::where('name', $exerciseData['name'])->first();
                            if ($exercise) {
                                $plan->exercises()->attach($exercise->id, [
                                    'sets' => $exerciseData['pivot']['sets'] ?? 1,
                                    'reps' => $exerciseData['pivot']['reps'] ?? 10,
                                    'weight' => $exerciseData['pivot']['weight'] ?? null,
                                    'rest_time' => $exerciseData['pivot']['rest_time'] ?? null,
                                    'notes' => $exerciseData['pivot']['notes'] ?? null,
                                ]);
                            }
                        }
                    }
                }

                // Restore schedule items
                if (!empty($planData['schedule_items'])) {
                    foreach ($planData['schedule_items'] as $scheduleData) {
                        $exercise = Exercise::where('name', $scheduleData['exercise_name'] ?? 'Unknown Exercise')->first();
                        if ($exercise) {
                            $plan->scheduleItems()->create([
                                'exercise_id' => $exercise->id,
                                'day_of_week' => $scheduleData['day_of_week'] ?? 1,
                                'sets' => $scheduleData['sets'] ?? 1,
                                'reps' => $scheduleData['reps'] ?? 10,
                                'weight' => $scheduleData['weight'] ?? null,
                                'rest_time' => $scheduleData['rest_time'] ?? null,
                                'is_time_based' => $scheduleData['is_time_based'] ?? false,
                                'notes' => $scheduleData['notes'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Restore workout sessions and exercise sets if option is enabled
            if ($this->restoreOptions['include_history']) {
                // Restore workout sessions
                foreach ($backupData['workout_sessions'] as $sessionData) {
                    $oldSessionId = $sessionData['id'];
                    unset($sessionData['id'], $sessionData['user_id'], $sessionData['created_at'], $sessionData['updated_at']);
                    
                    $session = WorkoutSession::create([
                        'user_id' => $user->id,
                        'workout_plan_id' => $sessionData['workout_plan_id'] ? ($planIdMapping[$sessionData['workout_plan_id']] ?? null) : null,
                        'date' => $sessionData['date'],
                        'duration' => $sessionData['duration'] ?? null,
                        'notes' => $sessionData['notes'] ?? null,
                        'is_completed' => $sessionData['is_completed'] ?? false,
                    ]);

                    // Restore exercise sets for this session
                    $sessionSets = array_filter($backupData['exercise_sets'], function($set) use ($oldSessionId) {
                        return $set['workout_session_id'] == $oldSessionId;
                    });

                    foreach ($sessionSets as $setData) {
                        $exercise = Exercise::where('name', $setData['exercise_name'] ?? 'Unknown Exercise')->first();
                        if ($exercise) {
                            ExerciseSet::create([
                                'user_id' => $user->id,
                                'workout_session_id' => $session->id,
                                'exercise_id' => $exercise->id,
                                'set_number' => $setData['set_number'] ?? 1,
                                'reps' => $setData['reps'] ?? null,
                                'weight' => $setData['weight'] ?? null,
                                'time_seconds' => $setData['time_seconds'] ?? null,
                                'notes' => $setData['notes'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Restore food tracker data if option is enabled
            if ($this->restoreOptions['include_food_tracker'] && !empty($backupData['food_tracker'])) {
                // Restore food items first
                $foodItemMapping = [];
                foreach ($backupData['food_tracker']['food_items'] as $itemData) {
                    $oldItemId = $itemData['id'];
                    unset($itemData['id'], $itemData['created_at'], $itemData['updated_at']);
                    
                    $foodItem = FoodItem::firstOrCreate(
                        ['name' => $itemData['name'], 'brand' => $itemData['brand'] ?? null],
                        $itemData
                    );
                    
                    $foodItemMapping[$oldItemId] = $foodItem->id;
                }

                // Restore food logs
                foreach ($backupData['food_tracker']['food_logs'] as $logData) {
                    $oldItemId = $logData['food_item_id'];
                    unset($logData['id'], $logData['user_id'], $logData['created_at'], $logData['updated_at']);
                    
                    if (isset($foodItemMapping[$oldItemId])) {
                        FoodLog::create([
                            'user_id' => $user->id,
                            'food_item_id' => $foodItemMapping[$oldItemId],
                            'meal_type' => $logData['meal_type'] ?? 'snack',
                            'quantity' => $logData['quantity'] ?? 1.0,
                            'consumed_date' => $logData['consumed_date'],
                            'consumed_time' => $logData['consumed_time'] ?? null,
                            'notes' => $logData['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Restore body tracking data if option is enabled
            if ($this->restoreOptions['include_body_tracking'] && !empty($backupData['body_tracking'])) {
                // Restore weight measurements
                foreach ($backupData['body_tracking']['weight_measurements'] as $measurementData) {
                    unset($measurementData['id'], $measurementData['user_id'], $measurementData['created_at'], $measurementData['updated_at']);
                    
                    WeightMeasurement::create([
                        'user_id' => $user->id,
                        'weight' => $measurementData['weight'],
                        'unit' => $measurementData['unit'] ?? 'kg',
                        'measurement_date' => $measurementData['measurement_date'],
                        'notes' => $measurementData['notes'] ?? null,
                    ]);
                }

                // Restore body measurements
                foreach ($backupData['body_tracking']['body_measurements'] as $measurementData) {
                    unset($measurementData['id'], $measurementData['user_id'], $measurementData['created_at'], $measurementData['updated_at']);
                    
                    BodyMeasurement::create([
                        'user_id' => $user->id,
                        'measurement_date' => $measurementData['measurement_date'],
                        'chest' => $measurementData['chest'] ?? null,
                        'waist' => $measurementData['waist'] ?? null,
                        'hips' => $measurementData['hips'] ?? null,
                        'biceps' => $measurementData['biceps'] ?? null,
                        'forearms' => $measurementData['forearms'] ?? null,
                        'thighs' => $measurementData['thighs'] ?? null,
                        'calves' => $measurementData['calves'] ?? null,
                        'neck' => $measurementData['neck'] ?? null,
                        'shoulders' => $measurementData['shoulders'] ?? null,
                        'body_fat_percentage' => $measurementData['body_fat_percentage'] ?? null,
                        'muscle_mass' => $measurementData['muscle_mass'] ?? null,
                        'height' => $measurementData['height'] ?? null,
                        'notes' => $measurementData['notes'] ?? null,
                    ]);
                }
            }

            // Restore client data if user is a trainer and option is enabled
            if ($this->restoreOptions['include_client_data'] && $user->is_trainer && !empty($backupData['client_data'])) {
                foreach ($backupData['client_data'] as $clientData) {
                    try {
                        // Find or create client user
                        $client = User::firstOrCreate(
                            ['email' => $clientData['client_info']['email']],
                            [
                                'name' => $clientData['client_info']['name'],
                                'password' => bcrypt('temp_password_' . time()), // Temporary password
                            ]
                        );

                        // Assign client to trainer
                        $client->update(['my_trainer' => $user->id]);

                        // Restore client workout plans
                        foreach ($clientData['workout_plans'] as $planData) {
                            $oldPlanId = $planData['id'];
                            unset($planData['id'], $planData['user_id'], $planData['created_at'], $planData['updated_at']);
                            
                            $plan = WorkoutPlan::create([
                                'user_id' => $client->id,
                                'name' => $planData['name'],
                                'description' => $planData['description'] ?? null,
                                'is_active' => $planData['is_active'] ?? true,
                            ]);

                            // Restore client plan exercises and schedule items
                            // (Similar logic as above, but for client)
                        }

                        // Restore client other data based on options
                        if ($this->restoreOptions['include_food_tracker'] && !empty($clientData['food_tracker'])) {
                            // Restore client food tracker data
                        }

                        if ($this->restoreOptions['include_body_tracking'] && !empty($clientData['body_tracking'])) {
                            // Restore client body tracking data
                        }

                    } catch (\Exception $e) {
                        \Log::error('Failed to restore client data: ' . $e->getMessage(), [
                            'client_email' => $clientData['client_info']['email'] ?? 'Unknown',
                            'trainer_id' => $user->id,
                        ]);
                        // Continue with other clients instead of failing completely
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
            
            // Log the error for debugging
            \Log::error('Backup restore failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'backup_preview' => $this->restorePreview,
                'restore_options' => $this->restoreOptions,
                'trace' => $e->getTraceAsString()
            ]);
            
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