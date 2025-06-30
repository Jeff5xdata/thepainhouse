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

            <!-- Week Navigation -->
            @if($workoutPlan && $this->weekHasData($currentWeek))
                <div class="border dark:border-gray-700 rounded-lg p-4 mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium dark:text-white">Week {{ $currentWeek }}</h3>
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
                        <p class="text-gray-500 dark:text-gray-400">No workout data found for Week {{ $currentWeek }}</p>
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
        });
    </script>
</div> 