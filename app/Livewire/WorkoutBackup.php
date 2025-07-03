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

        // Initialize restore options
        $this->restoreOptions = [
            'overwrite_existing' => false,
            'include_settings' => true,
            'include_history' => true,
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

            $this->restorePreview = [
                'version' => $backupData['version'],
                'created_at' => $backupData['created_at'] ?? 'Unknown',
                'user' => $backupData['user'],
                'workout_plans_count' => count($backupData['workout_plans'] ?? []),
                'workout_sessions_count' => count($backupData['workout_sessions'] ?? []),
                'exercise_sets_count' => count($backupData['exercise_sets'] ?? []),
                'has_settings' => !empty($backupData['workout_settings']),
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
                // Check if plan already exists
                $existingPlan = WorkoutPlan::where('user_id', $user->id)
                    ->where('name', $planData['name'])
                    ->first();

                if ($existingPlan && !$this->restoreOptions['overwrite_existing']) {
                    // Skip existing plans if not overwriting
                    $planIdMapping[$planData['id']] = $existingPlan->id;
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
                $planIdMapping[$planData['id']] = $newPlan->id;

                // Restore plan exercises
                if (!empty($planData['exercises'])) {
                    foreach ($planData['exercises'] as $exerciseData) {
                        // Find exercise by name (assuming exercises exist in the system)
                        $exercise = Exercise::where('name', $exerciseData['exercise_name'])->first();
                        
                        if ($exercise && $exerciseData['pivot']) {
                            $pivotData = $exerciseData['pivot'];
                            unset($pivotData['workout_plan_id'], $pivotData['exercise_id'], $pivotData['created_at'], $pivotData['updated_at']);
                            $newPlan->exercises()->attach($exercise->id, $pivotData);
                        }
                    }
                }

                // Restore schedule items
                if (!empty($planData['schedule_items'])) {
                    foreach ($planData['schedule_items'] as $scheduleData) {
                        try {
                            $exercise = Exercise::where('name', $scheduleData['exercise_name'])->first();
                            
                            if ($exercise) {
                                $scheduleInsertData = $scheduleData['schedule_data'];
                                $scheduleInsertData['workout_plan_id'] = $newPlan->id;
                                $scheduleInsertData['exercise_id'] = $exercise->id;
                                unset($scheduleInsertData['id'], $scheduleInsertData['created_at'], $scheduleInsertData['updated_at']);
                                
                                // Handle set_details field - convert array to JSON string if needed
                                if (isset($scheduleInsertData['set_details']) && is_array($scheduleInsertData['set_details'])) {
                                    $scheduleInsertData['set_details'] = json_encode($scheduleInsertData['set_details']);
                                }
                                
                                // Only include fields that belong to workout_plan_schedule table
                                $allowedFields = [
                                    'workout_plan_id', 'exercise_id', 'week_number', 'day_of_week', 
                                    'order_in_day', 'set_details'
                                ];
                                
                                $filteredData = array_intersect_key($scheduleInsertData, array_flip($allowedFields));
                                
                                // Ensure required fields have proper types
                                $filteredData['week_number'] = (int) ($filteredData['week_number'] ?? 1);
                                $filteredData['day_of_week'] = (string) ($filteredData['day_of_week'] ?? 1);
                                $filteredData['order_in_day'] = (int) ($filteredData['order_in_day'] ?? 0);
                                
                                DB::table('workout_plan_schedule')->insert($filteredData);
                            } else {
                                \Log::warning('Exercise not found during restore: ' . $scheduleData['exercise_name']);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to restore schedule item: ' . $e->getMessage(), [
                                'exercise_name' => $scheduleData['exercise_name'] ?? 'Unknown',
                                'schedule_data' => $scheduleData['schedule_data'] ?? []
                            ]);
                            // Continue with other items instead of failing completely
                        }
                    }
                }
            }

            // Restore workout sessions and exercise sets if option is enabled
            if ($this->restoreOptions['include_history']) {
                foreach ($backupData['workout_sessions'] as $sessionData) {
                    // Find the corresponding workout plan using the mapping
                    $newPlanId = $planIdMapping[$sessionData['workout_plan_id']] ?? null;
                    
                    if ($newPlanId) {
                        $sessionCreateData = [
                            'user_id' => $user->id,
                            'workout_plan_id' => $newPlanId,
                            'name' => $sessionData['name'] ?? 'Restored Session',
                            'date' => $sessionData['date'],
                            'week_number' => $sessionData['week_number'] ?? 1,
                            'day_of_week' => $sessionData['day_of_week'] ?? 1,
                            'status' => $sessionData['status'] ?? 'completed',
                            'notes' => $sessionData['notes'] ?? null,
                            'completed_at' => $sessionData['completed_at'] ?? null,
                        ];

                        $newSession = WorkoutSession::create($sessionCreateData);

                        // Restore exercise sets for this session
                        foreach ($backupData['exercise_sets'] as $setData) {
                            if ($setData['set_data']['workout_session_id'] == $sessionData['id']) {
                                try {
                                    $exercise = Exercise::where('name', $setData['exercise_name'])->first();
                                    
                                    if ($exercise) {
                                        $setCreateData = [
                                            'workout_session_id' => $newSession->id,
                                            'exercise_id' => $exercise->id,
                                            'set_number' => (int) ($setData['set_data']['set_number'] ?? 1),
                                            'reps' => (int) ($setData['set_data']['reps'] ?? 0),
                                            'weight' => $setData['set_data']['weight'] ?? null,
                                            'completed' => (bool) ($setData['set_data']['completed'] ?? false),
                                            'is_warmup' => (bool) ($setData['set_data']['is_warmup'] ?? false),
                                            'time_in_seconds' => $setData['set_data']['time_in_seconds'] ?? null,
                                            'notes' => $setData['set_data']['notes'] ?? null,
                                        ];

                                        ExerciseSet::create($setCreateData);
                                    } else {
                                        \Log::warning('Exercise not found during set restore: ' . $setData['exercise_name']);
                                    }
                                } catch (\Exception $e) {
                                    \Log::error('Failed to restore exercise set: ' . $e->getMessage(), [
                                        'exercise_name' => $setData['exercise_name'] ?? 'Unknown',
                                        'set_data' => $setData['set_data'] ?? []
                                    ]);
                                    // Continue with other sets instead of failing completely
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