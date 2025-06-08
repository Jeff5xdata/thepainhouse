<div>
    @if($workoutPlan)
    <div class="page-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">{{ $workoutPlan->name }}</h2>
                <p class="section-description">{{ $workoutPlan->description }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <button wire:click="togglePrintModal" class="secondary-button custom-tooltip" data-tooltip="Print workout plan">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
                @php
                    $hasWorkouts = false;
                    foreach($weekSchedule as $daySchedule) {
                        if(count($daySchedule) > 0) {
                            $hasWorkouts = true;
                            break;
                        }
                    }
                @endphp
                <a href="{{ $hasWorkouts ? route('workout.session') : route('workout.planner') }}" class="primary-button custom-tooltip" data-tooltip="{{ $hasWorkouts ? 'Begin your workout session' : 'Create a new workout' }}">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <div class="space-y-8">
            <div class="border dark:border-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Week {{ $currentWeek }}</h3>
                    <div class="flex space-x-2">
                        <button wire:click="previousWeek" class="icon-button custom-tooltip-left" data-tooltip="Previous week" {{ $currentWeek <= 1 ? 'disabled' : '' }}>
                            <svg class="h-5 w-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button wire:click="nextWeek" class="icon-button custom-tooltip-right" data-tooltip="Next week" {{ $currentWeek >= $workoutPlan->weeks_duration ? 'disabled' : '' }}>
                            <svg class="h-5 w-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="space-y-6">
                    @foreach($daysOfWeek as $day => $dayName)
                        @if(isset($weekSchedule[$day]) && count($weekSchedule[$day]) > 0)                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <h1 class="font-bold text-gray-800 dark:text-gray-200">{{ $dayName }}</h1>
                                    <button wire:click="copyWorkoutModal('{{ $day }}')" class="icon-button custom-tooltip-left" data-tooltip="Copy this day's workout to another day">
                                        <svg class="h-5 w-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                                <hr class="dark:border-gray-700" />
                                <div class="mt-4 space-y-4">
                                    @foreach($weekSchedule[$day] as $scheduleItem)
                                        <div class="content-card" wire:key="exercise-{{ $scheduleItem->id }}">
                                            <div class="flex justify-between items-start">
                                                <div class="flex items-center">
                                                <div class="flex flex-col mr-3 text-gray-400">
                                        <button class="icon-button custom-tooltip-right" data-tooltip="Move exercise up" wire:click="moveExercise('{{ $day }}', {{ $scheduleItem->id }}, 'up')" wire:loading.class="opacity-50" wire:target="moveExercise">
                                            <svg class="h-4 w-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button class="icon-button custom-tooltip-right" data-tooltip="Move exercise down" wire:click="moveExercise('{{ $day }}', {{ $scheduleItem->id }}, 'down')" wire:loading.class="opacity-50" wire:target="moveExercise">
                                            <svg class="h-4 w-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                                    </div>
                                                    <div>
                                                        <h4 class="exercise-title">{{ $scheduleItem->exercise->name }}</h4>
                                                        <div class="exercise-details">
                                                            @if($scheduleItem->is_time_based)
                                                                {{ $scheduleItem->time_in_seconds }} seconds
                                                            @else
                                                                {{ $scheduleItem->sets }} sets × {{ $scheduleItem->reps }} reps
                                                            @endif
                                                            
                                                            @if($scheduleItem->has_warmup)
                                                                <span class="warmup-text">
                                                                    (Warmup: {{ $scheduleItem->warmup_sets }}×{{ $scheduleItem->warmup_reps }} @ {{ $scheduleItem->warmup_weight_percentage }}%)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="content-card text-center">
        <p class="section-description">No workout plan found. Create one to get started!</p>
        <a href="{{ route('workout.planner') }}" class="primary-button mt-4">
            Create Workout Plan
        </a>
    </div>
    @endif

    @if($showPrintModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-opacity-90 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Print Workout Plan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Choose what you want to include in the printed version:
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="primary-button sm:ml-3 sm:w-auto sm:text-sm">
                            Print
                        </button>
                        <button wire:click="togglePrintModal" type="button" class="secondary-button mt-3 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showCopyModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-opacity-90 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Copy Workout
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="targetWeek" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Target Week
                                        </label>
                                        <select wire:model="targetWeek" id="targetWeek" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            @for($i = 1; $i <= $workoutPlan->weeks_duration; $i++)
                                                <option value="{{ $i }}">Week {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label for="targetDay" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Target Day
                                        </label>
                                        <select wire:model="targetDay" id="targetDay" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            @foreach($daysOfWeek as $day => $dayName)
                                                <option value="{{ $day }}">{{ $dayName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="copyWorkout" type="button" class="primary-button sm:ml-3 sm:w-auto sm:text-sm">
                            Copy
                        </button>
                        <button wire:click="toggleCopyModal" type="button" class="secondary-button mt-3 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('exerciseReordered', () => {
                // Show success notification
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { 
                        type: 'success', 
                        message: 'Exercise order updated successfully' 
                    } 
                }));
            });
        });
    </script>
</div> 