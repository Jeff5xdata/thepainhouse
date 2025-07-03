<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use App\Services\GeminiApiService;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

#[Layout('layouts.navigation')]
class AiWorkoutGenerator extends Component
{
    #[Rule('required|integer|min:1|max:52')]
    public $weeks_duration = 4;

    #[Rule('required|string')]
    public $split_type = '2_on_1_off';

    #[Rule('required|string')]
    public $fitness_level = 'intermediate';

    #[Rule('required|string')]
    public $goals = 'general_fitness';

    #[Rule('required|string')]
    public $equipment = 'gym';

    #[Rule('required|integer|min:30|max:180')]
    public $time_per_workout = 60;

    public $isGenerating = false;
    public $generatedPlan = null;
    public $error = null;
    public $showPreview = false;
    public $previewData = [];

    public $splitTypes = [
        '2_on_1_off' => '2 Days On, 1 Day Off',
        '3_on_1_off' => '3 Days On, 1 Day Off',
        '4_on_1_off' => '4 Days On, 1 Day Off',
        '5_on_2_off' => '5 Days On, 2 Days Off',
        '6_on_1_off' => '6 Days On, 1 Day Off',
        'alternating' => 'Alternating Days (Mon, Wed, Fri)',
    ];

    public $fitnessLevels = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
    ];

    public $goalTypes = [
        'strength' => 'Strength & Power',
        'muscle_gain' => 'Muscle Gain',
        'endurance' => 'Endurance',
        'weight_loss' => 'Weight Loss',
        'general_fitness' => 'General Fitness',
        'sports_performance' => 'Sports Performance',
        'rehabilitation' => 'Rehabilitation',
    ];

    public $equipmentTypes = [
        'gym' => 'Full Gym',
        'home_gym' => 'Home Gym',
        'bodyweight' => 'Bodyweight Only',
        'minimal' => 'Minimal Equipment',
    ];

    public $daysOfWeek = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];

    public function mount()
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            session()->flash('error', 'Please log in to generate AI workout plans.');
            $this->redirect(route('login'));
            return;
        }
    }

    /**
     * Generate workout plan using AI
     */
    public function generateWorkout()
    {
        $this->validate();

        $this->isGenerating = true;
        $this->error = null;
        $this->generatedPlan = null;

        try {
            $geminiService = app(GeminiApiService::class);
            
            $parameters = [
                'weeks_duration' => $this->weeks_duration,
                'split_type' => $this->split_type,
                'fitness_level' => $this->fitness_level,
                'goals' => $this->goals,
                'equipment' => $this->equipment,
                'time_per_workout' => $this->time_per_workout,
            ];

            $generatedPlan = $geminiService->generateWorkoutPlan($parameters);

            if ($generatedPlan) {
                $this->generatedPlan = $generatedPlan;
                $this->showPreview = true;
                session()->flash('message', 'Workout plan generated successfully!');
            } else {
                $this->error = 'Failed to generate workout plan. Please try again.';
            }

        } catch (\Exception $e) {
            Log::error('AI workout generation error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            $this->error = 'An error occurred while generating the workout plan. Please try again.';
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Save the generated workout plan
     */
    public function saveWorkoutPlan()
    {
        if (!$this->generatedPlan) {
            $this->error = 'No workout plan to save.';
            return;
        }

        try {
            DB::beginTransaction();

            // Deactivate existing active plan
            WorkoutPlan::where('user_id', auth()->id())
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new workout plan
            $workoutPlan = WorkoutPlan::create([
                'user_id' => auth()->id(),
                'name' => $this->generatedPlan['name'],
                'description' => $this->generatedPlan['description'],
                'weeks_duration' => $this->generatedPlan['weeks_duration'],
                'is_active' => true,
            ]);

            // Get existing exercises for matching
            $existingExercises = Exercise::where('user_id', auth()->id())
                ->orWhereNull('user_id')
                ->get()
                ->keyBy(function($exercise) {
                    return strtolower(trim($exercise->name));
                });

            // Create schedule items
            foreach ($this->generatedPlan['schedule'] as $weekNumber => $weekDays) {
                foreach ($weekDays as $dayNumber => $exercises) {
                    foreach ($exercises as $index => $exerciseData) {
                        // Try to find matching exercise
                        $exerciseName = strtolower(trim($exerciseData['exercise_name']));
                        $exercise = $existingExercises->get($exerciseName);

                        // If no exact match, try partial match
                        if (!$exercise) {
                            $exercise = $existingExercises->first(function($ex) use ($exerciseName) {
                                return str_contains(strtolower($ex->name), $exerciseName) ||
                                       str_contains($exerciseName, strtolower($ex->name));
                            });
                        }

                        // If still no match, create a new exercise
                        if (!$exercise) {
                            $exercise = Exercise::create([
                                'user_id' => auth()->id(),
                                'name' => $exerciseData['exercise_name'],
                                'description' => $exerciseData['notes'] ?? 'AI generated exercise',
                                'category' => $exerciseData['category'] ?? 'general',
                                'equipment' => $exerciseData['equipment'] ?? 'bodyweight',
                            ]);
                        }

                        // Parse reps to get default reps
                        $reps = $this->parseReps($exerciseData['reps']);

                        // Create schedule item
                        WorkoutPlanSchedule::create([
                            'workout_plan_id' => $workoutPlan->id,
                            'exercise_id' => $exercise->id,
                            'week_number' => $weekNumber,
                            'day_of_week' => (string)$dayNumber,
                            'order_in_day' => $index,
                            'sets' => $exerciseData['sets'] ?? 3,
                            'reps' => $reps,
                            'weight' => null,
                            'time_in_seconds' => null,
                            'is_time_based' => false,
                            'notes' => $exerciseData['notes'] ?? '',
                            'set_details' => json_encode([
                                'exercise_config' => [
                                    'sets' => $exerciseData['sets'] ?? 3,
                                    'reps' => $reps,
                                    'has_warmup' => false,
                                    'warmup_sets' => 0,
                                    'notes' => $exerciseData['notes'] ?? '',
                                    'is_time_based' => false,
                                ],
                                'sets' => $this->generateSetDetails($exerciseData['sets'] ?? 3, $reps)
                            ])
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('message', 'AI workout plan saved successfully!');
            $this->redirect(route('workout.planner'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving AI workout plan', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            $this->error = 'Failed to save workout plan. Please try again.';
        }
    }

    /**
     * Parse reps string to get default reps
     */
    private function parseReps(string $repsString): int
    {
        // Handle various rep formats: "8-12", "10", "5-8", etc.
        if (preg_match('/(\d+)/', $repsString, $matches)) {
            return (int) $matches[1];
        }
        return 10; // Default
    }

    /**
     * Generate set details for the exercise
     */
    private function generateSetDetails(int $sets, int $reps): array
    {
        $setDetails = [];
        for ($i = 1; $i <= $sets; $i++) {
            $setDetails[] = [
                'set_number' => $i,
                'reps' => $reps,
                'weight' => null,
                'is_warmup' => false,
                'notes' => '',
            ];
        }
        return $setDetails;
    }

    /**
     * Close the preview modal
     */
    public function closePreview()
    {
        $this->showPreview = false;
        $this->generatedPlan = null;
    }

    public function render()
    {
        return view('livewire.ai-workout-generator');
    }
} 