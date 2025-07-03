<div>
    @if($workoutPlan)
        <div class="page-container">
            <!-- Header -->
            <div class="section-header flex justify-between items-center">
                <div>
                    <h2 class="section-title">{{ $workoutPlan->name }}</h2>
                    <p class="section-description">{{ $workoutPlan->description }}</p>
                    @if($isTrainer)
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Trainer View
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex items-center space-x-4">
                    <x-share-modal :workout-plan="$workoutPlan" />
                    <button wire:click="togglePrintModal" class="secondary-button custom-tooltip" data-tooltip="Print workout plan">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    @php
                        $hasWorkouts = $workoutPlan && $this->weekHasData($currentWeek);
                    @endphp
                    <a href="{{ $hasWorkouts ? route('workout.session') : route('workout.planner') }}" class="primary-button custom-tooltip" data-tooltip="{{ $hasWorkouts ? 'Begin your workout session' : 'Create a new workout' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($hasWorkouts)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            @endif
                        </svg>
                        {{ $hasWorkouts ? 'Start Workout' : 'Create Workout' }}
                    </a>
                </div>
            </div>

            <!-- Incomplete Workouts Section -->
            <div class="border dark:border-gray-700 rounded-lg p-4 mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium dark:text-white">Incomplete Workouts</h3>
                    <div class="flex items-center space-x-2">
                        @if(count($incompleteWorkouts) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                {{ count($incompleteWorkouts) }} pending
                            </span>
                        @endif
                        <button wire:click="refreshIncompleteWorkouts" class="icon-button dark:text-white" title="Refresh incomplete workouts">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                @if(count($incompleteWorkouts) > 0)
                    <div class="space-y-4">
                        @foreach($incompleteWorkouts as $workout)
                            <div class="border rounded-lg p-4 {{ $workout['status'] === 'skipped' ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            {{ $workout['day_name'] }} - Week {{ $workout['week_number'] }}
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($workout['date'])->format('M j, Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $workout['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                               ($workout['status'] === 'planned' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : 
                                               'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200') }}">
                                            {{ ucfirst(str_replace('_', ' ', $workout['status'])) }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if(count($workout['incomplete_exercises']) > 0)
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">{{ $workout['completed_exercises'] }}/{{ $workout['total_exercises'] }}</span> exercises completed
                                        </p>
                                        <div class="space-y-1">
                                            @foreach($workout['incomplete_exercises'] as $exercise)
                                                <div class="flex justify-between items-center text-sm">
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $exercise['exercise_name'] }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        {{ $exercise['completed_sets'] }}/{{ $exercise['sets_count'] }} sets
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="mt-4 flex space-x-3">
                                    @if($workout['status'] === 'skipped')
                                        <button wire:click="resumeWorkout({{ $workout['id'] }})" 
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Resume Workout
                                        </button>
                                    @else
                                        <a href="{{ route('workout.session', $workout['id']) }}" 
                                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Continue Workout
                                        </a>
                                    @endif
                                    
                                    @if($workout['status'] !== 'skipped')
                                        <button wire:click="skipWorkout({{ $workout['id'] }})" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Skip
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">All caught up!</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No incomplete workouts found.</p>
                        <div class="mt-6">
                            <a href="{{ route('workout.session') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Start New Workout
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Week Navigation -->
            @if($workoutPlan && $this->weekHasData($currentWeek))
                <div class="border dark:border-gray-700 rounded-lg p-4 mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium dark:text-white">ISO Week {{ $currentWeek }}</h3>
                        <div class="flex space-x-2">
                            <button wire:click="previousWeek" class="icon-button dark:text-white" {{ !$this->hasPreviousWeek() ? 'disabled' : '' }}>←</button>
                            <button wire:click="nextWeek" class="icon-button dark:text-white" {{ !$this->hasNextWeek() ? 'disabled' : '' }}>→</button>
                        </div>
                    </div>
                    <div class="space-y-8">
                        @foreach($daysOfWeek as $day => $dayName)
                            @if(isset($weekSchedule[$day]) && count($weekSchedule[$day]) > 0)
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-bold dark:text-white">{{ $dayName }}</h4>
                                        <button wire:click="copyWorkoutModal('{{ $day }}')" class="icon-button custom-tooltip-left dark:text-white" data-tooltip="{{ $isTrainer ? 'Copy this day\'s workout to another day or to a client' : 'Copy this day\'s workout to another day' }}">
                                            <svg class="h-5 w-5 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="space-y-4">
                                        @foreach(collect($weekSchedule[$day])->groupBy('exercise_id') as $exerciseId => $items)
                                            <div class="content-card">
                                                <div class="flex justify-between items-center">
                                                    <h5 class="exercise-title">{{ $items->first()->exercise->name }}</h5>
                                                    <div>
                                                        <button class="icon-button dark:text-white" wire:click="moveExerciseCard('{{ $day }}', {{ $exerciseId }}, 'up')">↑</button>
                                                        <button class="icon-button dark:text-white" wire:click="moveExerciseCard('{{ $day }}', {{ $exerciseId }}, 'down')">↓</button>
                                                    </div>
                                                </div>
                                                <div class="exercise-details">
                                                    @foreach($items->sortBy('order_in_day') as $scheduleItem)
                                                        @php
                                                            $setDetails = $scheduleItem->formatted_set_details;
                                                            $warmupSets = collect($setDetails)->where('is_warmup', true);
                                                            $workingSets = collect($setDetails)->where('is_warmup', false);
                                                        @endphp
                                                        <div class="flex items-center mb-1">
                                                            @if($warmupSets->count() > 0)
                                                                <span class="text-yellow-400 mr-2">
                                                                    Warmup: {{ $warmupSets->count() }}×{{ $warmupSets->first()['reps'] ?? 0 }} reps
                                                                </span>
                                                            @endif
                                                            <span>
                                                                {{ $workingSets->count() }} sets × {{ $workingSets->first()['reps'] ?? 0 }} reps
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @elseif($workoutPlan)
                <div class="border dark:border-gray-700 rounded-lg p-4 mt-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">No workout data found for ISO Week {{ $currentWeek }}</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Create a workout plan to get started!</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="content-card text-center">
            <p class="section-description">No workout plan found. Create one to get started!</p>
            <a href="{{ route('workout.planner') }}" class="primary-button mt-4">
                Create Workout Plan
            </a>
        </div>
    @endif

    <!-- Print Modal -->
    @if($showPrintModal)
        @include('livewire.partials.print-modal')
    @endif

    <!-- Copy Workout Modal -->
    @if($showCopyModal)
        @include('livewire.partials.copy-modal')
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('exerciseReordered', () => {
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { type: 'success', message: 'Exercise order updated successfully' } 
                }));
            });
            
            // Listen for workout completion events
            @this.on('workoutCompleted', () => {
                // Refresh the incomplete workouts list
                @this.refreshIncompleteWorkouts();
            });
        });
    </script>
</div> 