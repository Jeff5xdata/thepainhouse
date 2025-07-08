<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.navigation')]
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
    public $currentDay = 1; // Monday = 1
    public $currentWeek;
    public $search = '';
    public $filteredExercises = [];
    public $showExerciseModal = false;
    public $existingPlan = null;
    public $showConfirmModal = false;
    public $showDeleteConfirmModal = false;
    public $showPrintModal = false;
    public $showAiWorkoutModal = false;
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
    public $oldestWeek = 1;
    public $newestWeek = 1;
    
    // AI Workout properties
    public $aiWorkoutPreferences = [
        'goal' => 'strength',
        'experience_level' => 'intermediate',
        'days_per_week' => 3,
        'focus_areas' => [],
        'equipment_available' => [],
        'time_per_workout' => 60
    ];

    public $daysOfWeek = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday'
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'weeks_duration' => 'required|integer|min:1|max:52',
        'schedule' => 'array',
    ];

    public function mount($week = null, $day = null, $plan_id = null)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            session()->flash('error', 'Please log in to create or edit workout plans.');
            $this->redirect(route('login'));
            return;
        }
        
        $this->exercises = Exercise::orderBy('name')->get();
        $this->categories = config('exercises.categories', [
            'chest' => 'Chest',
            'back' => 'Back',
            'legs' => 'Legs',
            'shoulders' => 'Shoulders',
            'biceps' => 'Biceps',
            'triceps' => 'Triceps',
            'abs' => 'Abs',
            'core' => 'Core',
            'glutes' => 'Glutes',
            'cardio' => 'Cardio',
            'full_body' => 'Full Body',
            'other' => 'Other',
        ]);
        
        if ($plan_id) {
            // Load specific plan for editing
            $this->workoutPlan = WorkoutPlan::with([
                'scheduleItems.exercise'
            ])
            ->where('user_id', auth()->id())
            ->findOrFail($plan_id);
        } else {
            // Find active workout plan
            $this->workoutPlan = WorkoutPlan::with([
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
            $this->weeks_duration = $this->workoutPlan->weeks_duration; // Load duration from database
            $this->loadSchedule();
            
            // Calculate week range based on existing data, but preserve the database duration
            $this->calculateWeekRange();
            
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
            
            // Calculate week range for new plan
            $this->calculateWeekRangeForNewPlan();
            
            // Debug: Log the empty schedule
            \Log::info('Initialized empty schedule for new plan', [
                'schedule' => $this->schedule,
            ]);
        }

        // Initialize with passed week and day or defaults
        $this->currentWeek = $week ?? $this->getDefaultWeek();
        $this->currentDay = $day ?? Carbon::now()->dayOfWeek; // 0 (Sunday) through 6 (Saturday)
    }

    /**
     * Calculate the week range based on existing data
     */
    protected function calculateWeekRange()
    {
        if (empty($this->schedule)) {
            $this->oldestWeek = Carbon::now()->isoWeek();
            $this->newestWeek = Carbon::now()->isoWeek();
            // Only set weeks_duration if not already set from database
            if (!isset($this->weeks_duration) || $this->weeks_duration <= 0) {
                $this->weeks_duration = 1;
            }
            return;
        }

        $weekNumbers = array_keys($this->schedule);
        if (empty($weekNumbers)) {
            $this->oldestWeek = Carbon::now()->isoWeek();
            $this->newestWeek = Carbon::now()->isoWeek();
            // Only set weeks_duration if not already set from database
            if (!isset($this->weeks_duration) || $this->weeks_duration <= 0) {
                $this->weeks_duration = 1;
            }
            return;
        }

        $this->oldestWeek = min($weekNumbers);
        $this->newestWeek = max($weekNumbers);
        
        // Only calculate weeks_duration if not already set from database
        if (!isset($this->weeks_duration) || $this->weeks_duration <= 0) {
            $currentWeek = Carbon::now()->isoWeek();
            $this->weeks_duration = max($currentWeek - (int)$this->oldestWeek + 1, 1);
        }
        
        \Log::info('Calculated week range', [
            'oldest_week' => $this->oldestWeek,
            'newest_week' => $this->newestWeek,
            'current_week' => Carbon::now()->isoWeek(),
            'weeks_duration' => $this->weeks_duration
        ]);
    }

    /**
     * Calculate week range for new plans
     */
    protected function calculateWeekRangeForNewPlan()
    {
        $currentWeek = Carbon::now()->isoWeek();
        
        // For new plans, start from current week
        $this->oldestWeek = $currentWeek;
        $this->newestWeek = $currentWeek;
        $this->weeks_duration = 1;
        
        \Log::info('Calculated week range for new plan', [
            'current_week' => $currentWeek,
            'weeks_duration' => $this->weeks_duration
        ]);
    }

    /**
     * Get the default week to start with
     */
    protected function getDefaultWeek()
    {
        // Always default to current week for better user experience
        return Carbon::now()->isoWeek();
    }

    /**
     * Get the week range for the view
     */
    public function getWeekRange()
    {
        $currentWeek = Carbon::now()->isoWeek();
        $endWeek = max((int)$this->oldestWeek + (int)$this->weeks_duration - 1, $currentWeek);
        return range((int)$this->oldestWeek, $endWeek);
    }

    /**
     * Check if the current week and day is today
     */
    public function isToday()
    {
        return $this->currentWeek === Carbon::now()->isoWeek() && 
               $this->currentDay === Carbon::now()->dayOfWeek;
    }

    /**
     * Get today's date formatted
     */
    public function getTodayDate()
    {
        return Carbon::now()->format('l, F j, Y');
    }

    /**
     * Navigate to today's week and day
     */
    public function goToToday()
    {
        $this->currentWeek = Carbon::now()->isoWeek();
        $this->currentDay = Carbon::now()->dayOfWeek;
    }

    /**
     * Clean up orphaned exercises that no longer exist in the database
     */
    protected function cleanupOrphanedExercises($scheduleItems)
    {
        $validExerciseIds = $this->exercises->pluck('id')->toArray();
        $orphanedItems = $scheduleItems->filter(function ($item) use ($validExerciseIds) {
            return !in_array($item->exercise_id, $validExerciseIds);
        });

        if ($orphanedItems->count() > 0) {
            \Log::warning('Found orphaned exercise references in workout plan', [
                'plan_id' => $this->workoutPlan->id,
                'orphaned_count' => $orphanedItems->count(),
                'orphaned_exercise_ids' => $orphanedItems->pluck('exercise_id')->toArray()
            ]);

            // Remove orphaned items from the database
            foreach ($orphanedItems as $item) {
                $item->delete();
            }
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
            ->get();

        $this->schedule = [];

        // Get user's workout settings for fallbacks
        $userSettings = auth()->user()->workoutSettings;

        // Clean up orphaned exercises (exercises that no longer exist)
        $this->cleanupOrphanedExercises($scheduleItems);

        // Convert schedule items to the format expected by the view
        foreach ($scheduleItems as $item) {
            $setDetailsData = $item->set_details;
            
            // Ensure setDetailsData is an array
            if (is_string($setDetailsData)) {
                $setDetailsData = json_decode($setDetailsData, true);
            }
            
            if (!is_array($setDetailsData)) {
                \Log::warning("Invalid set_details for schedule item ID: {$item->id}");
                continue;
            }
            
            // Handle both old and new JSON structures
            if (isset($setDetailsData['exercise_config'])) {
                // New structure with exercise_config
                $config = $setDetailsData['exercise_config'];
                $setDetails = $setDetailsData['sets'] ?? [];
                
                $exerciseData = [
                    'exercise_id' => $item->exercise_id,
                    'exercise' => $item->exercise,
                    'is_time_based' => $config['is_time_based'] ?? false,
                    'notes' => $config['notes'] ?? '',
                    'set_details' => $setDetails,
                    'has_warmup' => $config['has_warmup'] ?? false,
                    'warmup_sets' => $config['warmup_sets'] ?? 0,
                    'warmup_reps' => $config['warmup_reps'] ?? ($userSettings ? $userSettings->default_warmup_reps : 10),
                    'warmup_time_in_seconds' => $config['warmup_time_in_seconds'] ?? null,
                    'warmup_weight_percentage' => $config['warmup_weight_percentage'] ?? null,
                    'sets' => $config['sets'] ?? ($userSettings ? $userSettings->default_work_sets : 3),
                    'reps' => $config['reps'] ?? ($userSettings ? $userSettings->default_work_reps : 10),
                    'weight' => $config['weight'] ?? null,
                    'time_in_seconds' => $config['time_in_seconds'] ?? null,
                ];
            } else {
                // Old structure - extract properties from set_details
                $setDetails = $setDetailsData;
                $hasWarmup = false;
                $warmupSets = 0;
                $warmupReps = $userSettings ? $userSettings->default_warmup_reps : 10;
                $warmupTimeInSeconds = null;
                $sets = $userSettings ? $userSettings->default_work_sets : 3;
                $reps = $userSettings ? $userSettings->default_work_reps : 10;
                $weight = null;
                $timeInSeconds = null;
                
                if (!empty($setDetails) && is_array($setDetails)) {
                    // Count warmup and work sets
                    $warmupSets = 0;
                    $workSets = 0;
                    
                    foreach ($setDetails as $set) {
                        if ($set['is_warmup'] ?? false) {
                            $warmupSets++;
                            if ($warmupSets === 1) {
                                $warmupReps = $set['reps'] ?? ($userSettings ? $userSettings->default_warmup_reps : 10);
                                $warmupTimeInSeconds = $set['time_in_seconds'] ?? null;
                            }
                        } else {
                            $workSets++;
                            if ($workSets === 1) {
                                $reps = $set['reps'] ?? ($userSettings ? $userSettings->default_work_reps : 10);
                                $weight = $set['weight'] ?? null;
                                $timeInSeconds = $set['time_in_seconds'] ?? null;
                            }
                        }
                    }
                    
                    $hasWarmup = $warmupSets > 0;
                    $sets = $workSets;
                }
                
                $exerciseData = [
                    'exercise_id' => $item->exercise_id,
                    'exercise' => $item->exercise,
                    'is_time_based' => false, // Default for old structure
                    'notes' => '',
                    'set_details' => $setDetails,
                    'has_warmup' => $hasWarmup,
                    'warmup_sets' => $warmupSets,
                    'warmup_reps' => $warmupReps,
                    'warmup_time_in_seconds' => $warmupTimeInSeconds,
                    'warmup_weight_percentage' => null,
                    'sets' => $sets,
                    'reps' => $reps,
                    'weight' => $weight,
                    'time_in_seconds' => $timeInSeconds,
                ];
            }
            
            $this->schedule[$item->week_number][$item->day_of_week][] = $exerciseData;
        }
        
        // Ensure all exercises have proper set_details
        foreach ($this->schedule as $week => $days) {
            foreach ($days as $day => $exercises) {
                foreach ($exercises as $index => $exercise) {
                    if (empty($exercise['set_details'])) {
                        throw new \Exception("set_details is required but empty for exercise in week {$week}, day {$day}, index {$index}. Please ensure all exercises have properly configured set_details.");
                    }
                }
            }
        }
    }

    public function updatedSearch()
    {
        if (empty($this->search)) {
            $this->filteredExercises = $this->exercises;
        } else {
            $this->filteredExercises = $this->exercises->filter(function ($exercise) {
                return str_contains(strtolower($exercise->name), strtolower($this->search)) ||
                       str_contains(strtolower($exercise->category), strtolower($this->search)) ||
                       str_contains(strtolower($exercise->equipment ?? ''), strtolower($this->search));
            });
        }
    }

    public function initializeSchedule()
    {
        if (!isset($this->schedule[$this->currentWeek])) {
            $this->schedule[$this->currentWeek] = [];
        }
        if (!isset($this->schedule[$this->currentWeek][$this->currentDay])) {
            $this->schedule[$this->currentWeek][$this->currentDay] = [];
        }
    }

    public function updatedWeeksDuration()
    {
        // Ensure current week doesn't exceed the calculated range
        $currentWeek = Carbon::now()->isoWeek();
        $maxWeek = (int)$this->oldestWeek + (int)$this->weeks_duration - 1;
        
        if ($this->currentWeek > $maxWeek) {
            $this->currentWeek = $maxWeek;
        }
        
        // Ensure current week is not less than oldest week
        if ($this->currentWeek < (int)$this->oldestWeek) {
            $this->currentWeek = (int)$this->oldestWeek;
        }
    }

    public function toggleExerciseModal()
    {
        $this->showExerciseModal = !$this->showExerciseModal;
        if ($this->showExerciseModal) {
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

    public function toggleAiWorkoutModal()
    {
        $this->showAiWorkoutModal = !$this->showAiWorkoutModal;
    }

    public function generateAiWorkout()
    {
        \Log::info('AI Workout generation started', [
            'preferences' => $this->aiWorkoutPreferences
        ]);
        
        // Validate preferences
        if (empty($this->aiWorkoutPreferences['focus_areas'])) {
            session()->flash('error', 'Please select at least one focus area.');
            \Log::warning('AI Workout generation failed: No focus areas selected');
            return;
        }

        // Set weeks_duration from preferences (default to 1)
        $this->weeks_duration = isset($this->aiWorkoutPreferences['weeks_duration']) && $this->aiWorkoutPreferences['weeks_duration'] ? (int)$this->aiWorkoutPreferences['weeks_duration'] : 1;

        // Clear current schedule
        $this->schedule = [];
        
        \Log::info('Schedule cleared, starting AI generation');
        
        // Generate workout based on preferences
        $this->generateWorkoutFromAi();
        
        \Log::info('AI generation completed', [
            'schedule_count' => is_array($this->schedule) ? count($this->schedule) : 'not array',
            'schedule' => $this->schedule
        ]);
        
        // Auto-save the generated workout
        $this->saveAiWorkout();
        
        // Close modal
        $this->toggleAiWorkoutModal();
    }

    protected function saveAiWorkout()
    {
        // Generate a default name for the AI workout
        $goal = $this->aiWorkoutPreferences['goal'];
        $daysPerWeek = $this->aiWorkoutPreferences['days_per_week'];
        $focusAreas = implode(', ', $this->aiWorkoutPreferences['focus_areas']);
        $weeksDuration = $this->weeks_duration;
        
        $this->name = "AI {$goal} Workout - {$daysPerWeek} days ({$focusAreas})";
        $this->description = "AI-generated workout plan for {$goal} training, {$daysPerWeek} days per week, focusing on {$focusAreas}.";
        $this->weeks_duration = $weeksDuration;
        
        \Log::info('Auto-saving AI workout', [
            'name' => $this->name,
            'description' => $this->description,
            'weeks_duration' => $this->weeks_duration
        ]);
        
        // Call the save method
        $this->save();
    }

    protected function generateWorkoutFromAi()
    {
        $daysPerWeek = $this->aiWorkoutPreferences['days_per_week'];
        $goal = $this->aiWorkoutPreferences['goal'];
        $experienceLevel = $this->aiWorkoutPreferences['experience_level'];
        $focusAreas = $this->aiWorkoutPreferences['focus_areas'];
        $equipmentAvailable = $this->aiWorkoutPreferences['equipment_available'];
        
        // Get exercises based on focus areas and equipment
        $exercises = $this->getExercisesForAiWorkout($focusAreas, $equipmentAvailable);
        
        if ($exercises->isEmpty()) {
            session()->flash('error', 'No exercises found for your selected preferences.');
            return;
        }
        
        // Generate workout schedule
        $this->generateWorkoutSchedule($exercises, $daysPerWeek, $goal, $experienceLevel);
    }

    protected function getExercisesForAiWorkout($focusAreas, $equipmentAvailable)
    {
        $query = Exercise::query();
        
        \Log::info('Getting exercises for AI workout', [
            'focus_areas' => $focusAreas,
            'equipment_available' => $equipmentAvailable
        ]);
        
        // Filter by focus areas (muscle groups)
        if (!empty($focusAreas)) {
            $query->whereIn('category', $focusAreas);
        }
        
        // Filter by equipment if specified
        if (!empty($equipmentAvailable)) {
            $query->where(function($q) use ($equipmentAvailable) {
                foreach ($equipmentAvailable as $equipment) {
                    $q->orWhere('equipment', 'LIKE', "%{$equipment}%");
                }
            });
        }
        
        $exercises = $query->orderBy('name')->get();
        
        \Log::info('Found exercises for AI workout', [
            'exercise_count' => $exercises->count(),
            'exercise_categories' => $exercises->pluck('category')->unique()->toArray(),
            'exercise_names' => $exercises->pluck('name')->toArray()
        ]);
        
        return $exercises;
    }

    protected function generateWorkoutSchedule($exercises, $daysPerWeek, $goal, $experienceLevel)
    {
        $currentWeek = Carbon::now()->isoWeek();
        $this->oldestWeek = $currentWeek;
        $this->newestWeek = $currentWeek;
        
        // Group exercises by muscle groups
        $exerciseGroups = $this->groupExercisesByMuscleGroups($exercises);
        
        // Create workout split based on days per week
        $workoutSplit = $this->createWorkoutSplit($exerciseGroups, $daysPerWeek, $goal);
        
        \Log::info('Created workout split', [
            'workout_split' => $workoutSplit,
            'exercise_groups' => array_keys($exerciseGroups),
            'days_per_week' => $daysPerWeek,
            'goal' => $goal
        ]);
        
        // Apply the workout to the schedule
        $this->applyWorkoutToSchedule($workoutSplit, $currentWeek);
    }

    protected function groupExercisesByMuscleGroups($exercises)
    {
        $groups = [];
        foreach ($exercises as $exercise) {
            $category = $exercise->category;
            if (!isset($groups[$category])) {
                $groups[$category] = [];
            }
            $groups[$category][] = $exercise;
        }
        return $groups;
    }

    protected function createWorkoutSplit($exerciseGroups, $daysPerWeek, $goal)
    {
        $split = [];
        
        switch ($daysPerWeek) {
            case 3:
                // Push/Pull/Legs or Full Body
                if ($goal === 'strength') {
                    $split = [
                        1 => ['chest'], // Push
                        3 => ['back'], // Pull
                        5 => ['legs'] // Legs
                    ];
                } else {
                    // Full body
                    $split = [
                        1 => array_keys($exerciseGroups),
                        3 => array_keys($exerciseGroups),
                        5 => array_keys($exerciseGroups)
                    ];
                }
                break;
                
            case 4:
                $split = [
                    1 => ['chest'],
                    2 => ['back'],
                    4 => ['core'],
                    5 => ['legs']
                ];
                break;
                
            case 5:
                $split = [
                    1 => ['chest'],
                    2 => ['back'],
                    3 => ['core'],
                    4 => ['legs'],
                    5 => ['core']
                ];
                break;
                
            default:
                // Default to full body
                $split = [
                    1 => array_keys($exerciseGroups),
                    3 => array_keys($exerciseGroups),
                    5 => array_keys($exerciseGroups)
                ];
        }
        
        return $split;
    }

    protected function applyWorkoutToSchedule($workoutSplit, $currentWeek)
    {
        $userSettings = auth()->user()->workoutSettings;
        
        \Log::info('Applying workout to schedule', [
            'workout_split' => $workoutSplit,
            'current_week' => $currentWeek,
            'user_settings' => $userSettings ? 'available' : 'not available'
        ]);
        
        foreach ($workoutSplit as $day => $muscleGroups) {
            $exerciseIndex = 0;
            
            \Log::info("Processing day {$day} with muscle groups: " . implode(', ', $muscleGroups));
            
            foreach ($muscleGroups as $muscleGroup) {
                // Get exercises for this muscle group
                $exercises = Exercise::where('category', $muscleGroup)->get();
                
                \Log::info("Found {$exercises->count()} exercises for muscle group: {$muscleGroup}");
                
                if ($exercises->isEmpty()) {
                    \Log::warning("No exercises found for muscle group: {$muscleGroup}");
                    continue;
                }
                
                // Select 2-3 exercises per muscle group
                $selectedExercises = $exercises->random(min(3, $exercises->count()));
                
                \Log::info("Selected " . $selectedExercises->count() . " exercises for {$muscleGroup}: " . $selectedExercises->pluck('name')->implode(', '));
                
                foreach ($selectedExercises as $exercise) {
                    $this->schedule[$currentWeek][$day][] = [
                        'exercise_id' => $exercise->id,
                        'exercise' => $exercise,
                        'is_time_based' => false,
                        'notes' => '',
                        'set_details' => [],
                        'has_warmup' => $userSettings && $userSettings->default_warmup_sets > 0,
                        'warmup_sets' => $userSettings ? $userSettings->default_warmup_sets : 2,
                        'warmup_reps' => $userSettings ? $userSettings->default_warmup_reps : 10,
                        'warmup_time_in_seconds' => null,
                        'warmup_weight_percentage' => $userSettings ? $userSettings->warmup_weight_percentage : null,
                        'sets' => $userSettings ? $userSettings->default_work_sets : 3,
                        'reps' => $userSettings ? $userSettings->default_work_reps : 10,
                        'weight' => null,
                        'time_in_seconds' => null,
                    ];
                    
                    $this->regenerateSetDetails($currentWeek, $day, $exerciseIndex);
                    $exerciseIndex++;
                }
            }
        }
        
        \Log::info('Final schedule after AI generation', [
            'schedule' => $this->schedule,
            'schedule_count' => is_array($this->schedule) ? count($this->schedule) : 'not array'
        ]);
    }

    public function addExercise($week, $day, $exerciseId)
    {
        $this->initializeSchedule();
        
        $exercise = $this->exercises->find($exerciseId);
        if (!$exercise) {
            return;
        }

        // Get user's workout settings
        $userSettings = auth()->user()->workoutSettings;
        
        $orderInDay = count($this->schedule[$week][$day]);
        
        $this->schedule[$week][$day][] = [
            'exercise_id' => $exercise->id,
            'exercise' => $exercise,
            'is_time_based' => false,
            'notes' => '',
            'set_details' => [],
            'has_warmup' => $userSettings && $userSettings->default_warmup_sets > 0,
            'warmup_sets' => $userSettings ? $userSettings->default_warmup_sets : 2,
            'warmup_reps' => $userSettings ? $userSettings->default_warmup_reps : 10,
            'warmup_time_in_seconds' => null,
            'warmup_weight_percentage' => $userSettings ? $userSettings->warmup_weight_percentage : null,
            'sets' => $userSettings ? $userSettings->default_work_sets : 3,
            'reps' => $userSettings ? $userSettings->default_work_reps : 10,
            'weight' => null,
            'time_in_seconds' => null,
        ];

        $this->regenerateSetDetails($week, $day, $orderInDay);
        $this->toggleExerciseModal();
    }

    public function removeExercise($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            unset($this->schedule[$week][$day][$index]);
            $this->schedule[$week][$day] = array_values($this->schedule[$week][$day]);
        }
    }

    public function moveExercise($week, $day, $fromIndex, $toIndex)
    {
        if (isset($this->schedule[$week][$day][$fromIndex])) {
            $exercise = $this->schedule[$week][$day][$fromIndex];
            unset($this->schedule[$week][$day][$fromIndex]);
            $this->schedule[$week][$day] = array_values($this->schedule[$week][$day]);
            
            array_splice($this->schedule[$week][$day], $toIndex, 0, [$exercise]);
        }
    }

    public function updateExercise($week, $day, $index, $field, $value)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index][$field] = $value;
            
            // For time-based exercises, if the main time is updated, propagate to all work sets
            if ($field === 'time_in_seconds' && ($this->schedule[$week][$day][$index]['is_time_based'] ?? false)) {
                $this->propagateMainTimeToSets($week, $day, $index, $value);
            }
            
            // For time-based exercises, if the warmup time is updated, propagate to all warmup sets
            if ($field === 'warmup_time_in_seconds' && ($this->schedule[$week][$day][$index]['is_time_based'] ?? false)) {
                $this->propagateWarmupTimeToSets($week, $day, $index, $value);
            }
            
            // If reps are updated, propagate to all work sets
            if ($field === 'reps') {
                $this->propagateRepsToSets($week, $day, $index, $value);
            }
            
            // If weight is updated, propagate to all work sets
            if ($field === 'weight') {
                $this->propagateWeightToSets($week, $day, $index, $value);
            }
            
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    /**
     * Propagate main time_in_seconds to all work sets in the JSON
     */
    protected function propagateMainTimeToSets($week, $day, $index, $timeInSeconds)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if (!($set['is_warmup'] ?? false)) {
                $set['time_in_seconds'] = $timeInSeconds;
            }
        }
    }

    /**
     * Propagate warmup time_in_seconds to all warmup sets in the JSON
     */
    protected function propagateWarmupTimeToSets($week, $day, $index, $timeInSeconds)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if ($set['is_warmup'] ?? false) {
                $set['time_in_seconds'] = $timeInSeconds;
            }
        }
    }

    protected function regenerateSetDetails($week, $day, $index)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = $this->schedule[$week][$day][$index];
        $setDetails = [];
        $setNumber = 1;

        // Get user's workout settings for fallbacks
        $userSettings = auth()->user()->workoutSettings;

        // Add warmup sets if enabled
        if ($exercise['has_warmup'] && ($exercise['warmup_sets'] ?? 0) > 0) {
            for ($i = 1; $i <= $exercise['warmup_sets']; $i++) {
                $setDetails[] = [
                    'set_number' => $setNumber++,
                    'reps' => $exercise['warmup_reps'] ?? ($userSettings ? $userSettings->default_warmup_reps : 10),
                    'weight' => null,
                    'notes' => "Warmup Set {$i}",
                    'time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? null,
                    'is_warmup' => true,
                ];
            }
        }

        // Add work sets
        for ($i = 1; $i <= ($exercise['sets'] ?? ($userSettings ? $userSettings->default_work_sets : 3)); $i++) {
            $setDetails[] = [
                'set_number' => $setNumber++,
                'reps' => $exercise['reps'] ?? ($userSettings ? $userSettings->default_work_reps : 10),
                'weight' => $exercise['weight'] ?? null,
                'notes' => "Work Set {$i}",
                'time_in_seconds' => $exercise['time_in_seconds'] ?? null,
                'is_warmup' => false,
            ];
        }

        $this->schedule[$week][$day][$index]['set_details'] = $setDetails;
    }

    /**
     * Update both sets/reps columns and regenerate JSON set_details
     */
    public function updateSetsAndReps($week, $day, $index, $sets = null, $reps = null, $warmupSets = null, $warmupReps = null)
    {
        if (!isset($this->schedule[$week][$day][$index])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        // Regenerate the JSON to keep it in sync
        $this->regenerateSetDetails($week, $day, $index);
    }

    public function confirmSave()
    {
        $this->dispatch('open-confirm-modal');
    }

    public function deletePlan()
    {
        if ($this->workoutPlan) {
            $this->workoutPlan->delete();
            session()->flash('message', 'Workout plan deleted successfully!');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function save()
    {
        \Log::info('Save method called');
        
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'weeks_duration' => 'required|integer|min:1',
            ]);
            
            \Log::info('Validation passed');
        } catch (\Exception $e) {
            \Log::error('Validation failed: ' . $e->getMessage());
            throw $e;
        }

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

            // Save schedule items
            foreach ($this->schedule as $week => $days) {
                foreach ($days as $day => $exercises) {
                    foreach ($exercises as $index => $exercise) {
                        // Ensure set_details is properly formatted
                        if (empty($exercise['set_details'])) {
                            throw new \Exception("set_details is required but empty for exercise in week {$week}, day {$day}, index {$index}. Please ensure all exercises have properly configured set_details.");
                        }

                        // Create a comprehensive set_details structure that includes all exercise configuration
                        $comprehensiveSetDetails = [
                            'exercise_config' => [
                                'is_time_based' => $exercise['is_time_based'] ?? false,
                                'notes' => $exercise['notes'] ?? '',
                                'sets' => $exercise['sets'] ?? 3,
                                'reps' => $exercise['reps'] ?? 10,
                                'weight' => $exercise['weight'] ?? null,
                                'time_in_seconds' => $exercise['time_in_seconds'] ?? null,
                                'has_warmup' => $exercise['has_warmup'] ?? false,
                                'warmup_sets' => $exercise['warmup_sets'] ?? 0,
                                'warmup_reps' => $exercise['warmup_reps'] ?? 10,
                                'warmup_time_in_seconds' => $exercise['warmup_time_in_seconds'] ?? null,
                                'warmup_weight_percentage' => $exercise['warmup_weight_percentage'] ?? null,
                            ],
                            'sets' => $exercise['set_details']
                        ];

                        WorkoutPlanSchedule::create([
                            'workout_plan_id' => $this->workoutPlan->id,
                            'exercise_id' => $exercise['exercise_id'],
                            'week_number' => $week,
                            'day_of_week' => (string)$day, // Ensure day is a string
                            'order_in_day' => $index + 1,
                            'set_details' => json_encode($comprehensiveSetDetails),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            
            session()->flash('message', $this->workoutPlan ? 'Workout plan updated successfully!' : 'AI workout plan created and saved successfully!');
            
            // Check if this was called from AI workout generation
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $isAiWorkout = false;
            
            foreach ($backtrace as $trace) {
                if (isset($trace['function']) && $trace['function'] === 'saveAiWorkout') {
                    $isAiWorkout = true;
                    break;
                }
            }
            
            // Only redirect if it's not an AI workout (let AI workouts stay on the page)
            if (!$isAiWorkout) {
                return $this->redirect(route('dashboard'), navigate: true);
            }

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
        $this->addExercise($this->currentWeek, $this->currentDay, $exerciseId);
    }

    public function toggleWarmup($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index]['has_warmup'] = !($this->schedule[$week][$day][$index]['has_warmup'] ?? false);
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function toggleTimeBased($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            $this->schedule[$week][$day][$index]['is_time_based'] = !($this->schedule[$week][$day][$index]['is_time_based'] ?? false);
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function addSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            // Get user's workout settings for fallback
            $userSettings = auth()->user()->workoutSettings;
            $defaultSets = $userSettings ? $userSettings->default_work_sets : 3;
            
            $this->schedule[$week][$day][$index]['sets'] = ($this->schedule[$week][$day][$index]['sets'] ?? $defaultSets) + 1;
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function removeSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            // Get user's workout settings for fallback
            $userSettings = auth()->user()->workoutSettings;
            $defaultSets = $userSettings ? $userSettings->default_work_sets : 3;
            
            $currentSets = $this->schedule[$week][$day][$index]['sets'] ?? $defaultSets;
            if ($currentSets > 1) {
                $this->schedule[$week][$day][$index]['sets'] = $currentSets - 1;
                $this->regenerateSetDetails($week, $day, $index);
            }
        }
    }

    public function addWarmupSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            // Get user's workout settings for fallback
            $userSettings = auth()->user()->workoutSettings;
            $defaultWarmupSets = $userSettings ? $userSettings->default_warmup_sets : 2;
            
            $this->schedule[$week][$day][$index]['warmup_sets'] = ($this->schedule[$week][$day][$index]['warmup_sets'] ?? $defaultWarmupSets) + 1;
            $this->regenerateSetDetails($week, $day, $index);
        }
    }

    public function removeWarmupSet($week, $day, $index)
    {
        if (isset($this->schedule[$week][$day][$index])) {
            // Get user's workout settings for fallback
            $userSettings = auth()->user()->workoutSettings;
            $defaultWarmupSets = $userSettings ? $userSettings->default_warmup_sets : 2;
            
            $currentWarmupSets = $this->schedule[$week][$day][$index]['warmup_sets'] ?? $defaultWarmupSets;
            if ($currentWarmupSets > 1) {
                $this->schedule[$week][$day][$index]['warmup_sets'] = $currentWarmupSets - 1;
                $this->regenerateSetDetails($week, $day, $index);
            }
        }
    }

    public function checkScheduleState()
    {
        // Debug method to check the current state of the schedule
        $this->debugMessage = 'Schedule state: ' . json_encode($this->schedule, JSON_PRETTY_PRINT);
        \Log::info('Schedule state check', ['schedule' => $this->schedule]);
    }

    public function render()
    {
        return view('livewire.workout-planner');
    }

    /**
     * Update individual set time and sync with main exercise time
     */
    public function updateSetTime($week, $day, $index, $setIndex, $timeInSeconds)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'][$setIndex])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        $set = &$exercise['set_details'][$setIndex];
        
        // Update the set time
        $set['time_in_seconds'] = $timeInSeconds;
        
        // For time-based exercises, update the main exercise time if this is the first work set
        if ($exercise['is_time_based'] && !($set['is_warmup'] ?? false)) {
            // Find the first work set index
            $firstWorkSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if (!($s['is_warmup'] ?? false)) {
                    $firstWorkSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first work set, update the main exercise time
            if ($firstWorkSetIndex === $setIndex) {
                $exercise['time_in_seconds'] = $timeInSeconds;
            }
        }
        
        // For warmup sets, update the main warmup time if this is the first warmup set
        if ($exercise['is_time_based'] && ($set['is_warmup'] ?? false)) {
            // Find the first warmup set index
            $firstWarmupSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if ($s['is_warmup'] ?? false) {
                    $firstWarmupSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first warmup set, update the main warmup time
            if ($firstWarmupSetIndex === $setIndex) {
                $exercise['warmup_time_in_seconds'] = $timeInSeconds;
            }
        }
    }

    /**
     * Update individual set reps and sync with main exercise reps
     */
    public function updateSetReps($week, $day, $index, $setIndex, $reps)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'][$setIndex])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        $set = &$exercise['set_details'][$setIndex];
        
        // Update the set reps
        $set['reps'] = $reps;
        
        // Update the main exercise reps if this is the first work set
        if (!($set['is_warmup'] ?? false)) {
            // Find the first work set index
            $firstWorkSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if (!($s['is_warmup'] ?? false)) {
                    $firstWorkSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first work set, update the main exercise reps
            if ($firstWorkSetIndex === $setIndex) {
                $exercise['reps'] = $reps;
            }
        }
        
        // Update the main warmup reps if this is the first warmup set
        if ($set['is_warmup'] ?? false) {
            // Find the first warmup set index
            $firstWarmupSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if ($s['is_warmup'] ?? false) {
                    $firstWarmupSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first warmup set, update the main warmup reps
            if ($firstWarmupSetIndex === $setIndex) {
                $exercise['warmup_reps'] = $reps;
            }
        }
    }

    /**
     * Update individual set weight and sync with main exercise weight
     */
    public function updateSetWeight($week, $day, $index, $setIndex, $weight)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'][$setIndex])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        $set = &$exercise['set_details'][$setIndex];
        
        // Update the set weight
        $set['weight'] = $weight;
        
        // Update the main exercise weight if this is the first work set
        if (!($set['is_warmup'] ?? false)) {
            // Find the first work set index
            $firstWorkSetIndex = null;
            foreach ($exercise['set_details'] as $i => $s) {
                if (!($s['is_warmup'] ?? false)) {
                    $firstWorkSetIndex = $i;
                    break;
                }
            }
            
            // If this is the first work set, update the main exercise weight
            if ($firstWorkSetIndex === $setIndex) {
                $exercise['weight'] = $weight;
            }
        }
    }

    /**
     * Propagate main reps to all work sets in the JSON
     */
    protected function propagateRepsToSets($week, $day, $index, $reps)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if (!($set['is_warmup'] ?? false)) {
                $set['reps'] = $reps;
            }
        }
    }

    /**
     * Propagate main weight to all work sets in the JSON
     */
    protected function propagateWeightToSets($week, $day, $index, $weight)
    {
        if (!isset($this->schedule[$week][$day][$index]['set_details'])) {
            return;
        }

        $exercise = &$this->schedule[$week][$day][$index];
        
        foreach ($exercise['set_details'] as &$set) {
            if (!($set['is_warmup'] ?? false)) {
                $set['weight'] = $weight;
            }
        }
    }
}