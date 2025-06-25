<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 sm:p-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                            @if($isNewSession)
                                Today's Workout
                            @else
                                Update Workout
                            @endif
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Week {{ $currentWeek }} - {{ $currentDay }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">{{ $sessionDate }}</span>
                        @if(!$isNewSession)
                            <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span class="hidden sm:inline">Updating Existing Session</span>
                                <span class="sm:hidden">Updating</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Rest Timer Modal -->
                <div id="restTimerModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden items-center justify-center z-50 p-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg px-6 sm:px-8 py-6 max-w-sm w-full mx-auto">
                        <div class="text-center">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Rest Timer</h3>
                            <div class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6" id="timerDisplay">
                                {{ auth()->user()->workoutSettings?->default_rest_timer ?? 60 }}
                            </div>
                            <button type="button" 
                                onclick="closeRestTimer()"
                                class="inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Exercise Completion Modal -->
                @if($showNotesModal)
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg px-4 sm:px-6 py-6 mx-4 max-w-md w-full">
                        <div class="mb-4">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                Complete Exercise
                                @if($currentExerciseId)
                                    @php
                                        $exercise = $todayExercises->firstWhere('exercise_id', $currentExerciseId);
                                    @endphp
                                    @if($exercise)
                                        <span class="text-base sm:text-lg text-indigo-600 dark:text-indigo-400 block mt-1">{{ $exercise->exercise->name }}</span>
                                    @endif
                                @endif
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Add any notes about your performance or how you felt during this exercise.</p>
                        </div>
                        <div class="mb-4">
                            <textarea
                                wire:model="exerciseNotes"
                                rows="4"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Enter your notes here..."
                            ></textarea>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                            <button
                                wire:click="closeNotesModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 order-2 sm:order-1"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="saveExerciseCompletion"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 order-1 sm:order-2"
                            >
                                Complete Exercise
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                @if($isNewSession)
                    <!-- NEW SESSION FORM -->
                    @if(count($todayExercises) > 0)
                        <form wire:submit.prevent="completeWorkout" class="space-y-6">
                            @if ($errors->any())
                                <div class="bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400 p-4 mb-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                                There were errors with your submission
                                            </h3>
                                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Session Notes -->
                            <div class="mb-4 sm:mb-6">
                                <label for="sessionNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Session Notes</label>
                                <textarea
                                    wire:model="sessionNotes"
                                    id="sessionNotes"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Add any notes about your workout session..."
                                ></textarea>
                            </div>

                            <div class="grid gap-6">
                                @foreach($todayExercises as $scheduleItem)
                                    @php
                                        $isCompleted = \App\Models\WorkoutPlanSchedule::where('workout_plan_id', $workoutPlan->id)
                                            ->where('exercise_id', $scheduleItem->exercise_id)
                                            ->where('week_number', $currentWeek)
                                            ->where('day_of_week', strtolower($currentDay))
                                            ->value('complete') ?? false;
                                    @endphp
                                    <div class="rounded-lg shadow-sm border dark:border-gray-700 p-4 sm:p-6 transition-colors duration-200 {{ $isCompleted ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-white dark:bg-gray-800' }}">
                                        <!-- Exercise Header -->
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                                            <div class="flex-1">
                                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                                    {{ $scheduleItem->exercise->name }}
                                                    @if($isCompleted)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ml-2">
                                                            Completed
                                                        </span>
                                                    @endif
                                                </h3>
                                                
                                                <!-- Progression Report -->
                                                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 text-sm">
                                                    @if(isset($lastWorkouts[$scheduleItem->exercise_id]))
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-gray-600 dark:text-gray-400">Last:</span>
                                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                                {{ number_format($lastWorkouts[$scheduleItem->exercise_id]['weight'], 1) }} lb × {{ $lastWorkouts[$scheduleItem->exercise_id]['reps'] }} reps
                                                            </span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Timer Button -->
                                                    <button type="button" 
                                                        onclick="openRestTimer()"
                                                        class="flex items-center justify-center sm:justify-start p-2 bg-green-600 border border-transparent rounded-lg text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Timer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Warmup Sets Section -->
                                        @if($scheduleItem->has_warmup)
                                            <div class="mb-4 sm:mb-6">
                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 space-y-2 sm:space-y-0">
                                                    <h4 class="text-base sm:text-lg font-semibold text-yellow-600 dark:text-yellow-400">Warmup Sets</h4>
                                                    <button type="button" 
                                                        wire:click="toggleProgress({{ $scheduleItem->exercise_id }})"
                                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 self-start sm:self-auto">
                                                        {{ $showProgress[$scheduleItem->exercise_id] ?? false ? 'Hide' : 'Show' }} Summary
                                                    </button>
                                                </div>
                                                
                                                @if($showProgress[$scheduleItem->exercise_id] ?? false)
                                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 sm:p-4 mb-4">
                                                        <div class="grid grid-cols-3 gap-2 sm:gap-4 text-sm">
                                                            <div class="text-center">
                                                                <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Sets</span>
                                                                <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->warmup_sets }}</span>
                                                            </div>
                                                            <div class="text-center">
                                                                <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Reps</span>
                                                                <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->warmup_reps }}</span>
                                                            </div>
                                                            <div class="text-center">
                                                                <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Weight</span>
                                                                <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->warmup_weight_percentage }}%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Warmup Set Inputs -->
                                                <div class="space-y-3">
                                                    @for($i = 1; $i <= $scheduleItem->warmup_sets; $i++)
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                                            <div>
                                                                <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">Set {{ $i }}</label>
                                                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reps</label>
                                                                <input type="number"
                                                                    wire:model="setReps.{{ $scheduleItem->exercise_id }}.warmup.{{ $i }}"
                                                                    min="1"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Reps">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                                                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (lb)</label>
                                                                <input type="number"
                                                                    wire:model="setWeights.{{ $scheduleItem->exercise_id }}.warmup.{{ $i }}"
                                                                    step="0.5"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Weight">
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Working Sets Section -->
                                        <div class="mb-4 sm:mb-6">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 space-y-2 sm:space-y-0">
                                                <h4 class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">Working Sets</h4>
                                                <button type="button" 
                                                    wire:click="toggleProgress({{ $scheduleItem->exercise_id }})"
                                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 self-start sm:self-auto">
                                                    {{ $showProgress[$scheduleItem->exercise_id] ?? false ? 'Hide' : 'Show' }} Summary
                                                </button>
                                            </div>
                                            
                                            @if($showProgress[$scheduleItem->exercise_id] ?? false)
                                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4 mb-4">
                                                    <div class="grid grid-cols-2 gap-2 sm:gap-4 text-sm">
                                                        <div class="text-center">
                                                            <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Sets</span>
                                                            <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->sets }}</span>
                                                        </div>
                                                        <div class="text-center">
                                                            <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                                                                @if($scheduleItem->is_time_based)
                                                                    Time
                                                                @else
                                                                    Reps
                                                                @endif
                                                            </span>
                                                            <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                                                                @if($scheduleItem->is_time_based)
                                                                    {{ $scheduleItem->time_in_seconds }}s
                                                                @else
                                                                    {{ $scheduleItem->reps }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Working Set Inputs -->
                                            <div class="space-y-3 sm:space-y-4">
                                                @for($i = 1; $i <= $scheduleItem->sets; $i++)
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                                        <div>
                                                            <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">Set {{ $i }}</label>
                                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                @if($scheduleItem->is_time_based)
                                                                    Time (seconds)
                                                                @else
                                                                    Reps
                                                                @endif
                                                            </label>
                                                            <input type="number"
                                                                wire:model="setReps.{{ $scheduleItem->exercise_id }}.working.{{ $i }}"
                                                                min="1"
                                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                placeholder="{{ $scheduleItem->is_time_based ? 'Time in seconds' : 'Reps' }}">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                @if($scheduleItem->is_time_based)
                                                                    Notes
                                                                @else
                                                                    Weight (lb)
                                                                @endif
                                                            </label>
                                                            @if($scheduleItem->is_time_based)
                                                                <input type="text"
                                                                    wire:model="setNotes.{{ $scheduleItem->exercise_id }}.working.{{ $i }}"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Notes">
                                                            @else
                                                                <input type="number"
                                                                    wire:model="setWeights.{{ $scheduleItem->exercise_id }}.working.{{ $i }}"
                                                                    step="0.5"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Weight">
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <!-- Exercise Controls -->
                                        <div class="flex grid-cols-2 sm:flex-row sm:justify-between sm:items-center pt-4 border-t border-gray-200 dark:border-gray-600 space-y-3 sm:space-y-0">
                                            <div class="flex items-center">
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                        wire:model="useProgression.{{ $scheduleItem->exercise_id }}"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Progression Toggle</span>
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <button type="button"
                                                    @if($isCompleted) disabled @endif
                                                    wire:click="completeExercise({{ $scheduleItem->exercise_id }})"
                                                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 w-full sm:w-auto {{ $isCompleted ? 'bg-green-600 text-white cursor-not-allowed' : 'text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' }}">
                                                    @if($isCompleted)
                                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Completed
                                                    @else
                                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Complete Exercise
                                                    @endif
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 sm:mt-8 flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Workout
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="mb-4 sm:mb-6">
                                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No exercises scheduled</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a workout plan</p>
                            </div>
                            <a href="{{ route('workout.planner', ['week' => $currentWeek, 'day' => strtolower($currentDay)]) }}" class="inline-flex items-center justify-center px-4 sm:px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Workout Plan
                            </a>
                        </div>
                    @endif
                @else
                    <!-- EXISTING SESSION EDIT FORM -->
                    @if(count($todayExercises) > 0)
                        <form wire:submit.prevent="completeWorkout" class="space-y-6">
                            @if ($errors->any())
                                <div class="bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400 p-4 mb-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                                There were errors with your submission
                                            </h3>
                                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid gap-6">
                                @foreach($todayExercises as $scheduleItem)
                                    @php
                                        $isCompleted = \App\Models\WorkoutPlanSchedule::where('workout_plan_id', $workoutPlan->id)
                                            ->where('exercise_id', $scheduleItem->exercise_id)
                                            ->where('week_number', $currentWeek)
                                            ->where('day_of_week', strtolower($currentDay))
                                            ->value('complete') ?? false;
                                    @endphp
                                    <div class="rounded-lg shadow-sm border dark:border-gray-700 p-4 sm:p-6 transition-colors duration-200 {{ $isCompleted ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-white dark:bg-gray-800' }}">
                                        <!-- Exercise Header -->
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                                            <div class="flex-1">
                                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                                    {{ $scheduleItem->exercise->name }}
                                                    @if($isCompleted)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 ml-2">
                                                            Completed
                                                        </span>
                                                    @endif
                                                </h3>
                                                
                                                <!-- Progression Report -->
                                                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 text-sm">
                                                    @if(isset($lastWorkouts[$scheduleItem->exercise_id]))
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-gray-600 dark:text-gray-400">Last:</span>
                                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                                {{ number_format($lastWorkouts[$scheduleItem->exercise_id]['weight'], 1) }} lb × {{ $lastWorkouts[$scheduleItem->exercise_id]['reps'] }} reps
                                                            </span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Timer Button -->
                                                    <button type="button" 
                                                        onclick="openRestTimer()"
                                                        class="flex items-center justify-center sm:justify-start p-2 bg-green-600 border border-transparent rounded-lg text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Timer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Warmup Sets Section -->
                                        @if($scheduleItem->has_warmup)
                                            <div class="mb-4 sm:mb-6">
                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 space-y-2 sm:space-y-0">
                                                    <h4 class="text-base sm:text-lg font-semibold text-yellow-600 dark:text-yellow-400">Warmup Sets</h4>
                                                    <button type="button" 
                                                        wire:click="toggleProgress({{ $scheduleItem->exercise_id }})"
                                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 self-start sm:self-auto">
                                                        {{ $showProgress[$scheduleItem->exercise_id] ?? false ? 'Hide' : 'Show' }} Summary
                                                    </button>
                                                </div>
                                                
                                                @if($showProgress[$scheduleItem->exercise_id] ?? false)
                                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 sm:p-4 mb-4">
                                                        <div class="grid grid-cols-3 gap-2 sm:gap-4 text-sm">
                                                            <div class="text-center">
                                                                <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Sets</span>
                                                                <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->warmup_sets }}</span>
                                                            </div>
                                                            <div class="text-center">
                                                                <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Reps</span>
                                                                <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->warmup_reps }}</span>
                                                            </div>
                                                            <div class="text-center">
                                                                <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Weight</span>
                                                                <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->warmup_weight_percentage }}%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Warmup Set Inputs -->
                                                <div class="space-y-3">
                                                    @for($i = 1; $i <= $scheduleItem->warmup_sets; $i++)
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                                            <div>
                                                                <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">Set {{ $i }}</label>
                                                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reps</label>
                                                                <input type="number"
                                                                    wire:model="setReps.{{ $scheduleItem->exercise_id }}.warmup.{{ $i }}"
                                                                    min="1"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Reps">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                                                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (lb)</label>
                                                                <input type="number"
                                                                    wire:model="setWeights.{{ $scheduleItem->exercise_id }}.warmup.{{ $i }}"
                                                                    step="0.5"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Weight">
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Working Sets Section -->
                                        <div class="mb-4 sm:mb-6">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 space-y-2 sm:space-y-0">
                                                <h4 class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">Working Sets</h4>
                                                <button type="button" 
                                                    wire:click="toggleProgress({{ $scheduleItem->exercise_id }})"
                                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 self-start sm:self-auto">
                                                    {{ $showProgress[$scheduleItem->exercise_id] ?? false ? 'Hide' : 'Show' }} Summary
                                                </button>
                                            </div>
                                            
                                            @if($showProgress[$scheduleItem->exercise_id] ?? false)
                                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4 mb-4">
                                                    <div class="grid grid-cols-2 gap-2 sm:gap-4 text-sm">
                                                        <div class="text-center">
                                                            <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Sets</span>
                                                            <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->sets }}</span>
                                                        </div>
                                                        <div class="text-center">
                                                            <span class="block text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                                                                @if($scheduleItem->is_time_based)
                                                                    Time
                                                                @else
                                                                    Reps
                                                                @endif
                                                            </span>
                                                            <span class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                                                                @if($scheduleItem->is_time_based)
                                                                    {{ $scheduleItem->time_in_seconds }}s
                                                                @else
                                                                    {{ $scheduleItem->reps }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Working Set Inputs -->
                                            <div class="space-y-3 sm:space-y-4">
                                                @for($i = 1; $i <= $scheduleItem->sets; $i++)
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                                        <div>
                                                            <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">Set {{ $i }}</label>
                                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                @if($scheduleItem->is_time_based)
                                                                    Time (seconds)
                                                                @else
                                                                    Reps
                                                                @endif
                                                            </label>
                                                            <input type="number"
                                                                wire:model="setReps.{{ $scheduleItem->exercise_id }}.working.{{ $i }}"
                                                                min="1"
                                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                placeholder="{{ $scheduleItem->is_time_based ? 'Time in seconds' : 'Reps' }}">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                @if($scheduleItem->is_time_based)
                                                                    Notes
                                                                @else
                                                                    Weight (lb)
                                                                @endif
                                                            </label>
                                                            @if($scheduleItem->is_time_based)
                                                                <input type="text"
                                                                    wire:model="setNotes.{{ $scheduleItem->exercise_id }}.working.{{ $i }}"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Notes">
                                                            @else
                                                                <input type="number"
                                                                    wire:model="setWeights.{{ $scheduleItem->exercise_id }}.working.{{ $i }}"
                                                                    step="0.5"
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Weight">
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <!-- Exercise Controls -->
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center pt-4 border-t border-gray-200 dark:border-gray-600 space-y-3 sm:space-y-0">
                                            <div class="flex items-center">
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                        wire:model="useProgression.{{ $scheduleItem->exercise_id }}"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Progression Toggle</span>
                                                </label>
                                            </div>
                                            <button type="button"
                                                @if($isCompleted) disabled @endif
                                                wire:click="completeExercise({{ $scheduleItem->exercise_id }})"
                                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 w-full sm:w-auto {{ $isCompleted ? 'bg-green-600 text-white cursor-not-allowed' : 'text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' }}">
                                                @if($isCompleted)
                                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Completed
                                                @else
                                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Complete Exercise
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 sm:mt-8 flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Workout
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="mb-4 sm:mb-6">
                                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No exercises found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This workout session doesn't have any exercises.</p>
                            </div>
                            <a href="{{ route('workout.history') }}" class="inline-flex items-center justify-center px-4 sm:px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to History
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    let timer;
    let timeLeft;

    // Debug form submission
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[wire\\:submit\\.prevent]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                console.log('Form submit event triggered');
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
            });
        });

        // Improve mobile input experience
        const inputs = document.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            // Prevent zoom on iOS when focusing on inputs
            input.addEventListener('focus', function() {
                if (window.innerWidth <= 768) {
                    this.style.fontSize = '16px';
                }
            });
            
            input.addEventListener('blur', function() {
                this.style.fontSize = '';
            });
        });
    });

    function openRestTimer() {
        const modal = document.getElementById('restTimerModal');
        const timerDisplay = document.getElementById('timerDisplay');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        timeLeft = {{ auth()->user()->workoutSettings?->default_rest_timer ?? 60 }};
        timerDisplay.textContent = timeLeft;
        
        timer = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                closeRestTimer();
            }
        }, 1000);
    }

    function closeRestTimer() {
        const modal = document.getElementById('restTimerModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        clearInterval(timer);
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        const restTimerModal = document.getElementById('restTimerModal');
        if (e.target === restTimerModal) {
            closeRestTimer();
        }
    });
</script>