<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use App\Models\ExerciseSet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkplanSessionController extends Controller
{
    public function show()
    {
        // First try to find an active workout plan with eager loaded relationships
        $workoutPlan = WorkoutPlan::with([
            'exercises' => function($query) {
                $query->withPivot([
                    'default_sets',
                    'default_reps',
                    'default_weight',
                    'has_warmup',
                    'warmup_sets',
                    'warmup_reps',
                    'warmup_weight_percentage'
                ]);
            },
            'scheduleItems.exercise'
        ])
        ->where('user_id', auth()->id())
        ->where('is_active', true)
        ->first();

        // If no active plan found, get the most recent plan with eager loaded relationships
        if (!$workoutPlan) {
            $workoutPlan = WorkoutPlan::with([
                'exercises' => function($query) {
                    $query->withPivot([
                        'default_sets',
                        'default_reps',
                        'default_weight',
                        'has_warmup',
                        'warmup_sets',
                        'warmup_reps',
                        'warmup_weight_percentage'
                    ]);
                },
                'scheduleItems.exercise'
            ])
            ->where('user_id', auth()->id())
            ->latest()
            ->first();
        }

        if (!$workoutPlan) {
            return redirect()->route('workout.planner')
                ->with('error', 'No workout plan found. Please create a workout plan first.');
        }

        // Get current week number (1-based)
        $currentWeek = 1; // Default to week 1
        $totalWeeks = $workoutPlan->weeks_duration;
        if ($totalWeeks > 1) {
            // Calculate which week we're in based on the plan start date
            $startDate = Carbon::parse($workoutPlan->created_at)->startOfDay();
            $currentWeekNumber = Carbon::now()->startOfDay()->diffInDays($startDate) / 7;
            $currentWeekNumber = ceil($currentWeekNumber);
            $currentWeek = min(max(1, $currentWeekNumber), $totalWeeks);
        }

        // Get current day of week (always lowercase)
        $today = strtolower(Carbon::now()->format('l'));

        // Get today's exercises from the schedule
        $todayExercises = $workoutPlan->getScheduleForDay($currentWeek, $today);

        \Log::info('Final Today\'s Workout:', [
            'week' => $currentWeek,
            'day' => $today,
            'exercises' => $todayExercises,
            'exercises_count' => count($todayExercises)
        ]);

        // Get the last workout session for each exercise with optimized query
        $lastWorkouts = WorkoutSession::with(['exerciseSets' => function($query) {
                $query->where('is_warmup', false)
                    ->orderBy('set_number', 'desc')
                    ->latest();
            }])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->select('id', 'user_id', 'created_at')
            ->latest()
            ->take(10) // Limit to last 10 sessions for performance
            ->get()
            ->pluck('exerciseSets')
            ->flatten()
            ->groupBy('exercise_id')
            ->map(function($sets) {
                return $sets->first();
            });

        return view('workout-plan.session', [
            'workoutPlan' => $workoutPlan,
            'lastWorkouts' => $lastWorkouts,
            'sessionDate' => Carbon::now()->format('Y-m-d'),
            'currentWeek' => $currentWeek,
            'currentDay' => ucfirst($today),
            'todayExercises' => $todayExercises
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_name' => 'required|string|max:255',
            'sets' => 'required|array',
            'reps' => 'array',
            'time' => 'array',
            'weight' => 'required|array',
            'notes' => 'array',
            'session_notes' => 'nullable|string',
            'has_warmup' => 'array',
            'warmup_sets' => 'array',
            'warmup_reps' => 'array',
            'warmup_weight_percentage' => 'array',
            'week_number' => 'required|integer|min:1',
            'day_of_week' => 'required|string',
            'use_progression' => 'array',
        ]);

        // Start a database transaction to ensure data consistency
        \DB::beginTransaction();

        try {
            // Log the workout plan ID
            \Log::info('Creating workout session with plan ID:', ['workout_plan_id' => $request->workout_plan_id]);

            $session = WorkoutSession::create([
                'name' => $request->session_name,
                'workout_plan_id' => $request->workout_plan_id,
                'notes' => $request->session_notes,
                'user_id' => auth()->id(),
                'date' => Carbon::now(),
                'week_number' => $request->week_number,
                'day_of_week' => $request->day_of_week,
                'status' => 'completed',
            ]);

            // Log the created session
            \Log::info('Created workout session:', ['session_id' => $session->id]);

            // Get the workout plan to check for time-based exercises
            $workoutPlan = WorkoutPlan::with('scheduleItems')
                ->findOrFail($request->workout_plan_id);

            // Get schedule items for the current day
            $scheduleItems = $workoutPlan->getScheduleForDay($request->week_number, $request->day_of_week)
                ->keyBy('exercise_id');

            // Log the schedule items
            \Log::info('Schedule items found:', ['count' => $scheduleItems->count()]);

            // Prepare exercise sets for bulk insertion
            $exerciseSets = [];
            $now = now();

            foreach ($request->sets as $exerciseId => $sets) {
                // Log each exercise being processed
                \Log::info('Processing exercise:', [
                    'exercise_id' => $exerciseId,
                    'sets' => $sets,
                    'has_warmup' => isset($request->has_warmup[$exerciseId]),
                ]);

                // Create warmup sets if enabled for this exercise
                if (isset($request->has_warmup[$exerciseId]) && 
                    isset($request->warmup_sets[$exerciseId]) && 
                    $request->warmup_sets[$exerciseId] > 0) {
                    
                    $warmupSets = $request->warmup_sets[$exerciseId];
                    $warmupReps = $request->warmup_reps[$exerciseId];
                    $warmupPercentage = $request->warmup_weight_percentage[$exerciseId];
                    $workingWeight = $request->weight[$exerciseId] ?? 0;
                    
                    // Log warmup set details
                    \Log::info('Creating warmup sets:', [
                        'exercise_id' => $exerciseId,
                        'warmup_sets' => $warmupSets,
                        'warmup_reps' => $warmupReps,
                        'warmup_percentage' => $warmupPercentage,
                    ]);
                    
                    for ($i = 1; $i <= $warmupSets; $i++) {
                        $exerciseSets[] = [
                            'workout_session_id' => $session->id,
                            'exercise_id' => $exerciseId,
                            'set_number' => $i,
                            'is_warmup' => true,
                            'reps' => $warmupReps,
                            'time_in_seconds' => null,
                            'weight' => ($workingWeight * $warmupPercentage / 100),
                            'notes' => "Warmup set at {$warmupPercentage}% of working weight",
                            'completed' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'used_progression' => false,
                        ];
                    }
                }

                // Create working sets
                if ($sets > 0) {
                    $scheduleItem = $scheduleItems[$exerciseId] ?? null;
                    $isTimeBased = $scheduleItem ? $scheduleItem->is_time_based : false;

                    // Log working set details
                    \Log::info('Creating working sets:', [
                        'exercise_id' => $exerciseId,
                        'sets' => $sets,
                        'is_time_based' => $isTimeBased,
                        'reps' => $request->reps[$exerciseId] ?? 0,
                        'time' => $request->time[$exerciseId] ?? 0,
                    ]);

                    for ($i = 1; $i <= $sets; $i++) {
                        $exerciseSets[] = [
                            'workout_session_id' => $session->id,
                            'exercise_id' => $exerciseId,
                            'set_number' => $i,
                            'is_warmup' => false,
                            'reps' => $isTimeBased ? 0 : ($request->reps[$exerciseId] ?? 0),
                            'time_in_seconds' => $isTimeBased ? ($request->time[$exerciseId] ?? 0) : null,
                            'weight' => $request->weight[$exerciseId] ?? 0,
                            'notes' => $request->notes[$exerciseId] ?? null,
                            'completed' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'used_progression' => isset($request->use_progression[$exerciseId]),
                        ];
                    }
                }
            }

            // Log the total number of sets to be created
            \Log::info('Total exercise sets to create:', ['count' => count($exerciseSets)]);

            // Bulk insert all exercise sets
            if (!empty($exerciseSets)) {
                ExerciseSet::insert($exerciseSets);
                \Log::info('Successfully inserted exercise sets');
            }

            \DB::commit();
            \Log::info('Successfully committed transaction');

            return redirect()->route('workplan.session.view', $session->id)
                ->with('success', 'Workout session created successfully!');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating workout session:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to create workout session. Please try again.');
        }
    }

    public function view(WorkoutSession $session)
    {
        $session->load(['workoutPlan', 'exerciseSets.exercise']);
        
        return view('workout-plan.session', [
            'session' => $session,
            'workoutPlan' => $session->workoutPlan,
            'currentWeek' => $session->week_number,
            'currentDay' => ucfirst($session->day_of_week),
            'sessionDate' => $session->date->format('Y-m-d'),
            'todayExercises' => $session->workoutPlan->getScheduleForDay($session->week_number, $session->day_of_week),
            'lastWorkouts' => WorkoutSession::with(['exerciseSets' => function($query) {
                    $query->where('is_warmup', false)
                        ->orderBy('set_number', 'desc')
                        ->latest();
                }])
                ->where('user_id', auth()->id())
                ->where('status', 'completed')
                ->select('id', 'user_id', 'created_at')
                ->latest()
                ->take(10)
                ->get()
                ->pluck('exerciseSets')
                ->flatten()
                ->groupBy('exercise_id')
                ->map(function($sets) {
                    return $sets->first();
                })
        ]);
    }

    public function edit(WorkoutSession $session)
    {
        // Check if the session belongs to the authenticated user
        if ($session->user_id !== auth()->id()) {
            return redirect()->route('workplan.session')
                ->with('error', 'You are not authorized to edit this workout session.');
        }

        $session->load(['workoutPlan', 'exerciseSets.exercise']);
        
        // Get the last workouts before this session
        $lastWorkouts = WorkoutSession::with(['exerciseSets' => function($query) {
                $query->where('is_warmup', false)
                    ->orderBy('set_number', 'desc')
                    ->latest();
            }])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->where('id', '<', $session->id)
            ->select('id', 'user_id', 'created_at')
            ->latest()
            ->take(10)
            ->get()
            ->pluck('exerciseSets')
            ->flatten()
            ->groupBy('exercise_id')
            ->map(function($sets) {
                return $sets->first();
            });

        return view('workout-plan.session', [
            'session' => $session,
            'workoutPlan' => $session->workoutPlan,
            'currentWeek' => $session->week_number,
            'currentDay' => ucfirst($session->day_of_week),
            'sessionDate' => $session->date->format('Y-m-d'),
            'todayExercises' => $session->workoutPlan->getScheduleForDay($session->week_number, $session->day_of_week),
            'lastWorkouts' => $lastWorkouts,
            'isEditing' => true
        ]);
    }

    public function update(Request $request, WorkoutSession $session)
    {
        // Check if the session belongs to the authenticated user
        if ($session->user_id !== auth()->id()) {
            return redirect()->route('workplan.session')
                ->with('error', 'You are not authorized to edit this workout session.');
        }

        $request->validate([
            'sets' => 'required|array',
            'reps' => 'array',
            'time' => 'array',
            'weight' => 'required|array',
            'notes' => 'array',
            'session_notes' => 'nullable|string',
            'use_progression' => 'array',
        ]);

        \DB::beginTransaction();

        try {
            // Update session notes if provided
            if ($request->has('session_notes')) {
                $session->update(['notes' => $request->session_notes]);
            }

            // Get all exercise sets grouped by exercise
            $existingSets = $session->exerciseSets()
                ->get()
                ->groupBy('exercise_id');

            foreach ($request->sets as $exerciseId => $sets) {
                $exerciseSets = $existingSets->get($exerciseId, collect());
                
                // Update or create working sets
                for ($i = 1; $i <= $sets; $i++) {
                    $set = $exerciseSets->where('set_number', $i)
                        ->where('is_warmup', false)
                        ->first();

                    $setData = [
                        'exercise_id' => $exerciseId,
                        'set_number' => $i,
                        'is_warmup' => false,
                        'reps' => $request->reps[$exerciseId] ?? 0,
                        'time_in_seconds' => $request->time[$exerciseId] ?? null,
                        'weight' => $request->weight[$exerciseId] ?? 0,
                        'notes' => $request->notes[$exerciseId] ?? null,
                        'completed' => true,
                        'used_progression' => isset($request->use_progression[$exerciseId]),
                    ];

                    if ($set) {
                        $set->update($setData);
                    } else {
                        $session->exerciseSets()->create($setData);
                    }
                }

                // Remove any extra sets
                $session->exerciseSets()
                    ->where('exercise_id', $exerciseId)
                    ->where('set_number', '>', $sets)
                    ->where('is_warmup', false)
                    ->delete();
            }

            \DB::commit();
            return redirect()->route('workplan.session.view', $session->id)
                ->with('success', 'Workout session updated successfully!');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating workout session:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to update workout session. Please try again.');
        }
    }
} 