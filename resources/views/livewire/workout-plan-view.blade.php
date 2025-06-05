<div>
    @if($workoutPlan)
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $workoutPlan->name }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $workoutPlan->description }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <button wire:click="togglePrintModal" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <a href="{{ $hasWorkouts ? route('workplan.session') : route('workout.planner') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Week {{ $currentWeek }}</h3>
                <div class="space-y-6">
                    @foreach($daysOfWeek as $day => $dayName)
                        @if(isset($weekSchedule[$day]) && count($weekSchedule[$day]) > 0)                            
                            <div>
                                <h1 class="font-bold text-gray-800 dark:text-gray-200 mb-2">{{ $dayName }}</h1>
                                <hr class="dark:border-gray-700" />
                                <div class="mt-4 space-y-4">
                                    @foreach($weekSchedule[$day] as $scheduleItem)
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $scheduleItem->exercise->name }}</h4>
                                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                        @if($scheduleItem->is_time_based)
                                                            {{ $scheduleItem->time_in_seconds }} seconds
                                                        @else
                                                            {{ $scheduleItem->sets }} sets × {{ $scheduleItem->reps }} reps
                                                        @endif
                                                        
                                                        @if($scheduleItem->has_warmup)
                                                            <span class="ml-2 text-yellow-600 dark:text-yellow-400">
                                                                (Warmup: {{ $scheduleItem->warmup_sets }}×{{ $scheduleItem->warmup_reps }} @ {{ $scheduleItem->warmup_weight_percentage }}%)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Order: {{ $scheduleItem->order_in_day + 1 }}
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">No workout plan found. Create one to get started!</p>
        <a href="{{ route('workout.planner') }}" class="inline-flex items-center px-4 py-2 mt-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
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
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Print
                        </button>
                        <button wire:click="togglePrintModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 