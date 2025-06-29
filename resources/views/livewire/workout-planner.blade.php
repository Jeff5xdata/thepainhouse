<div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 sm:p-6 relative">
        <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-900 dark:text-gray-100">
            {{ $workoutPlan ? 'Edit Workout Plan' : 'Create Workout Plan' }}
        </h2>

        @if (session()->has('message'))
            <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        <form class="space-y-4 sm:space-y-6">
            <input type="hidden" wire:model="schedule">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan Name</label>
                <input type="text" wire:model.live="name" id="name"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea wire:model.live="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                @error('description') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="weeks_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (weeks)</label>
                <input type="number" wire:model.live="weeks_duration" id="weeks_duration" min="1" max="52"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('weeks_duration') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="border dark:border-gray-700 rounded-lg p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Week</label>
                        <div class="mb-4 sm:mb-6">
                            <select wire:model.live="currentWeek" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @for ($i = 1; $i <= $weeks_duration; $i++)
                                    <option value="{{ $i }}">Week {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Day</label>
                        <div class="mb-4 sm:mb-6">
                            <select wire:model.live="currentDay" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($daysOfWeek as $day => $dayName)
                                    <option value="{{ $day }}">{{ $dayName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">Exercises for Week {{ $currentWeek }}, {{ $daysOfWeek[$currentDay] }}</h3>
                    </div>

                    <div class="border dark:border-gray-700 rounded-lg p-3 sm:p-4">
                        @if (isset($schedule[$currentWeek][$currentDay]) && count($schedule[$currentWeek][$currentDay]) > 0)
                            <div class="space-y-3 sm:space-y-4">
                                @foreach ($schedule[$currentWeek][$currentDay] as $index => $exerciseData)
                                    <div class="border dark:border-gray-700 rounded-lg p-3 sm:p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $exercises->firstWhere('id', $exerciseData['exercise_id'])->name }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $categories[$exercises->firstWhere('id', $exerciseData['exercise_id'])->category] ?? $exercises->firstWhere('id', $exerciseData['exercise_id'])->category }}
                                                    @if($exercises->firstWhere('id', $exerciseData['exercise_id'])->equipment)
                                                        • {{ $exercises->firstWhere('id', $exerciseData['exercise_id'])->equipment }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2 sm:space-x-4">
                                                <button type="button" 
                                                    class="text-sm {{ $exerciseData['has_warmup'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }} hover:text-indigo-900 dark:hover:text-indigo-300 custom-tooltip-right"
                                                    data-tooltip="Add warmup sets to this exercise"
                                                    wire:click="toggleWarmup({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})">
                                                    <span class="flex items-center">
                                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                        </svg>
                                                        <span class="hidden sm:inline">Warmup</span>
                                                    </span>
                                                </button>
                                                <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 custom-tooltip-right"
                                                    data-tooltip="Remove this exercise"
                                                    wire:click="removeExercise({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})">
                                                    <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @if($exerciseData['has_warmup'])
                                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md">
                                            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warmup Sets</h5>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Warmup Sets</label>
                                                    <div class="flex items-center space-x-2 mt-1">
                                                        <button type="button" 
                                                            wire:click="removeWarmupSet({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                            class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                                            {{ ($exerciseData['warmup_sets'] ?? 2) <= 1 ? 'disabled' : '' }}>
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[2rem] text-center">
                                                            {{ $exerciseData['warmup_sets'] ?? 2 }}
                                                        </span>
                                                        <button type="button" 
                                                            wire:click="addWarmupSet({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                            class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                @if(!$exerciseData['is_time_based'])
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Warmup Reps</label>
                                                    <input type="number" min="1" max="30"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.warmup_reps"
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                                @else
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Warmup Time (seconds)</label>
                                                    <input type="number" min="1" max="3600"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.warmup_time_in_seconds"
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                                @endif
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Weight %</label>
                                                    <input type="number" min="0" max="100"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.warmup_weight_percentage"
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="mt-4 space-y-3 sm:space-y-4">
                                            <div class="flex items-center">
                                                <button type="button" wire:click="toggleTimeBased({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($exerciseData['is_time_based'])
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                        @endif
                                                    </svg>
                                                    <span class="hidden sm:inline">{{ $exerciseData['is_time_based'] ? 'Switch to Sets/Reps' : 'Switch to Time' }}</span>
                                                    <span class="sm:hidden">{{ $exerciseData['is_time_based'] ? 'Sets/Reps' : 'Time' }}</span>
                                                </button>
                                            </div>
                                            
                                            @if($exerciseData['is_time_based'])
                                            <div class="space-y-3 sm:space-y-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4">
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Time (seconds)</label>
                                                        <input type="number" min="1" max="3600"
                                                            wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.time_in_seconds"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Working Sets</label>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <button type="button" 
                                                                wire:click="removeSet({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                                class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                                                {{ ($exerciseData['sets'] ?? 1) <= 1 ? 'disabled' : '' }}>
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                                </svg>
                                                            </button>
                                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[2rem] text-center">
                                                                {{ $exerciseData['sets'] ?? 1 }}
                                                            </span>
                                                            <button type="button" 
                                                                wire:click="addSet({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                                class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Default Reps</label>
                                                        <input type="number" min="1" max="100"
                                                            wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.reps"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Default Weight</label>
                                                        <input type="number" min="0" step="0.5"
                                                            wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.weight"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                </div>
                                                
                                                <!-- Individual Sets Display for Time-based Exercises -->
                                                @if(isset($exerciseData['set_details']) && count($exerciseData['set_details']) > 0)
                                                <div class="mt-4">
                                                    <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Individual Sets</h6>
                                                    <div class="space-y-2">
                                                        @foreach($exerciseData['set_details'] as $setIndex => $set)
                                                        <div class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                                            <span class="text-xs text-gray-500 dark:text-gray-400 w-8">
                                                                Set {{ $set['set_number'] }}
                                                                @if($set['is_warmup'])
                                                                    <span class="text-orange-500">(W)</span>
                                                                @endif
                                                            </span>
                                                            <div class="flex-1 flex space-x-2">
                                                                <input type="number" min="1" max="3600"
                                                                    wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.time_in_seconds"
                                                                    class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                    placeholder="Time">
                                                                <input type="number" min="1" max="100"
                                                                    wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.reps"
                                                                    class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                    placeholder="Reps">
                                                                <input type="number" min="0" step="0.5"
                                                                    wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.weight"
                                                                    class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                    placeholder="Weight">
                                                            </div>
                                                            <input type="text"
                                                                wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.notes"
                                                                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                placeholder="Notes">
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @else
                                            <div class="space-y-3 sm:space-y-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Working Sets</label>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <button type="button" 
                                                                wire:click="removeSet({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                                class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                                                {{ ($exerciseData['sets'] ?? 1) <= 1 ? 'disabled' : '' }}>
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                                </svg>
                                                            </button>
                                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[2rem] text-center">
                                                                {{ $exerciseData['sets'] ?? 1 }}
                                                            </span>
                                                            <button type="button" 
                                                                wire:click="addSet({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})"
                                                                class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Default Reps</label>
                                                        <input type="number" min="1" max="100"
                                                            wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.reps"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500 dark:text-gray-400">Default Weight</label>
                                                        <input type="number" min="0" step="0.5"
                                                            wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.weight"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    </div>
                                                </div>
                                                
                                                <!-- Individual Sets Display -->
                                                @if(isset($exerciseData['set_details']) && count($exerciseData['set_details']) > 0)
                                                <div class="mt-4">
                                                    <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Individual Sets</h6>
                                                    <div class="space-y-2">
                                                        @foreach($exerciseData['set_details'] as $setIndex => $set)
                                                        <div class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                                            <span class="text-xs text-gray-500 dark:text-gray-400 w-8">
                                                                Set {{ $set['set_number'] }}
                                                                @if($set['is_warmup'])
                                                                    <span class="text-orange-500">(W)</span>
                                                                @endif
                                                            </span>
                                                            <div class="flex-1 flex space-x-2">
                                                                <input type="number" min="1" max="100"
                                                                    wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.reps"
                                                                    class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                    placeholder="Reps">
                                                                <input type="number" min="0" step="0.5"
                                                                    wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.weight"
                                                                    class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                    placeholder="Weight">
                                                            </div>
                                                            <input type="text"
                                                                wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.set_details.{{ $setIndex }}.notes"
                                                                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                                placeholder="Notes">
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No exercises added for this day.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center space-x-4">
                <button type="button" wire:click="toggleExerciseModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Exercise
                </button>
                <div class="flex space-x-4">
                    @if($workoutPlan)
                    <button type="button" wire:click="toggleDeleteConfirmModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Plan
                    </button>
                    @endif
                    <div class="flex justify-end">
                        <button 
                            type="button"
                            wire:click="{{ $workoutPlan ? 'confirmSave' : 'save' }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            wire:loading.class="opacity-50"
                            wire:loading.attr="disabled"
                        >
                            <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>
                                {{ $workoutPlan ? 'Update Plan' : 'Save Plan' }}
                            </span>
                            <span wire:loading>
                                {{ $workoutPlan ? 'Updating...' : 'Saving...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Exercise Selection Modal -->
    @if($showExerciseModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Add Exercise</h3>
                            <div class="mb-4">
                                <input type="text" wire:model.live="search" placeholder="Search exercises..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    autocomplete="off">
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @forelse ($filteredExercises as $exercise)
                                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b dark:border-gray-700 last:border-b-0"
                                        wire:click.prevent="addExerciseToDay({{ $exercise->id }})">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $exercise->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="capitalize">{{ $categories[$exercise->category] ?? $exercise->category }}</span>
                                                @if($exercise->equipment)
                                                    • {{ $exercise->equipment }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-indigo-600 dark:text-indigo-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                        No exercises found matching your search.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="toggleExerciseModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Print Modal -->
    @if($showPrintModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $name }}
                                </h3>
                                <button onclick="window.print()" class="print:hidden inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print
                                </button>
                            </div>
                            @if($description)
                            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $description }}</p>
                            @endif
                            <div class="space-y-8">
                                @for($week = 1; $week <= $weeks_duration; $week++)
                                    <div class="border dark:border-gray-700 rounded-lg p-4">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Week {{ $week }}</h4>
                                        <div class="space-y-6">
                                            @foreach($daysOfWeek as $day => $dayName)
                                                @if(isset($schedule[$week][$day]) && count($schedule[$week][$day]) > 0)
                                                    <div>
                                                        <h5 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">{{ $dayName }}</h5>
                                                        <div class="space-y-3">
                                                            @foreach($schedule[$week][$day] as $exercise)
                                                                <div class="pl-4 border-l-2 border-indigo-500 dark:border-indigo-400">
                                                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $this->getExerciseName($exercise['exercise_id']) }}
                                                                    </div>
                                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                                        @if($exercise['is_time_based'])
                                                                            Duration: {{ $this->formatDuration($exercise['time_in_seconds']) }}
                                                                        @else
                                                                            {{ $exercise['sets'] }} sets × {{ $exercise['reps'] }} reps
                                                                        @endif
                                                                        @if($exercise['has_warmup'])
                                                                            <br>
                                                                            <span class="text-gray-500 dark:text-gray-500">
                                                                                Warmup: 
                                                                                @if($exercise['is_time_based'])
                                                                                    {{ $this->formatDuration($exercise['warmup_time_in_seconds']) }}
                                                                                @else
                                                                                    {{ $exercise['warmup_sets'] }} × {{ $exercise['warmup_reps'] }}
                                                                                @endif
                                                                                ({{ $exercise['warmup_weight_percentage'] }}% weight)
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
                <div class="print:hidden bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="togglePrintModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Confirmation Modal -->
    <div x-data="{ show: false }" x-show="show" x-on:open-confirm-modal.window="show = true" x-on:close-confirm-modal.window="show = false" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div 
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-opacity-90 transition-opacity"
                aria-hidden="true"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Update Workout Plan
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to update your workout plan?
                                <br>
                                <font color="red">This action cannot be undone.</font>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <div class="flex justify-between">
                    <div>
                    <button 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                        x-on:click="show = false"
                    >
                        Cancel
                    </button>
                    </div>
                    <div>
                    <button 
                        type="button" 
                        wire:click="save"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm dark:bg-indigo-500 dark:hover:bg-indigo-400"
                        x-on:click="show = false"
                    >
                        Update Plan
                    </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-opacity-90 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Delete Workout Plan
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to delete this workout plan?
                                <br>
                                <font color="red">This action cannot be undone.</font>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <div class="flex justify-between">
                        <div>
                            <button 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                                wire:click="toggleDeleteConfirmModal"
                            >
                                Cancel
                            </button>
                        </div>
                        <div>
                            <button 
                                type="button" 
                                wire:click="deletePlan"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm dark:bg-red-500 dark:hover:bg-red-400"
                                wire:loading.class="opacity-50"
                                wire:loading.attr="disabled"
                            >
                                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove>Delete Plan</span>
                                <span wire:loading>Deleting...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .modal, .modal * {
                visibility: visible;
            }
            .modal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white !important;
                color: black !important;
            }
            .modal button {
                display: none;
            }
            .dark\:text-gray-100,
            .dark\:text-gray-200,
            .dark\:text-gray-300,
            .dark\:text-gray-400,
            .dark\:text-gray-500 {
                color: #1a202c !important;
            }
            .dark\:bg-gray-800,
            .dark\:bg-gray-700 {
                background-color: white !important;
            }
            .dark\:border-gray-700 {
                border-color: #e2e8f0 !important;
            }
        }
    </style>
</div>
