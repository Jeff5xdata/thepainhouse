<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 relative">
        <!-- Floating Add Exercise Button -->
        <div style="position: absolute; right: 0; top: 50%; transform: translate(50%, -50%);" class="z-[100]">
            <button type="button" wire:click="toggleExerciseModal"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-full shadow-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105 hover:translate-x-[-8px]">
                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Exercise
            </button>
        </div>

        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Create Workout Plan</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
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

            <div class="border dark:border-gray-700 rounded-lg p-4">
                <div class="flex space-x-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Week</label>
                        <select wire:model.live="currentWeek"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @for ($week = 1; $week <= $weeks_duration; $week++)
                                <option value="{{ $week }}">Week {{ $week }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Day</label>
                        <select wire:model.live="currentDay"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($daysOfWeek as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Exercises for Week {{ $currentWeek }}, {{ $daysOfWeek[$currentDay] }}</h3>
                    </div>

                    <div class="border dark:border-gray-700 rounded-lg p-4">
                        @if (isset($schedule[$currentWeek][$currentDay]) && count($schedule[$currentWeek][$currentDay]) > 0)
                            <div class="space-y-4">
                                @foreach ($schedule[$currentWeek][$currentDay] as $index => $exerciseData)
                                    <div class="border dark:border-gray-700 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $exercises->firstWhere('id', $exerciseData['exercise_id'])->name }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $categories[$exercises->firstWhere('id', $exerciseData['exercise_id'])->category] ?? $exercises->firstWhere('id', $exerciseData['exercise_id'])->category }}
                                                    @if($exercises->firstWhere('id', $exerciseData['exercise_id'])->equipment)
                                                        • {{ $exercises->firstWhere('id', $exerciseData['exercise_id'])->equipment }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <button type="button" 
                                                    class="text-sm {{ $exerciseData['has_warmup'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }} hover:text-indigo-900 dark:hover:text-indigo-300"
                                                    wire:click="toggleWarmup({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})">
                                                    <span class="flex items-center">
                                                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                        </svg>
                                                        Warmup
                                                    </span>
                                                </button>
                                                <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                    wire:click="removeExercise({{ $currentWeek }}, '{{ $currentDay }}', {{ $index }})">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @if($exerciseData['has_warmup'])
                                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md">
                                            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warmup Sets</h5>
                                            <div class="grid grid-cols-3 gap-4">
                                                @if(!$exerciseData['is_time_based'])
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Sets</label>
                                                    <input type="number" min="1" max="5"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.warmup_sets"
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Reps</label>
                                                    <input type="number" min="1" max="30"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.warmup_reps"
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                                @else
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Time (seconds)</label>
                                                    <input type="number" min="1" max="3600"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.warmup_time"
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
                                        <div class="mt-4 space-y-4">
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
                                                    {{ $exerciseData['is_time_based'] ? 'Switch to Sets/Reps' : 'Switch to Time' }}
                                                </button>
                                            </div>
                                            @if($exerciseData['is_time_based'])
                                            <div>
                                                <label class="text-xs text-gray-500 dark:text-gray-400">Time (seconds)</label>
                                                <input type="number" min="1" max="3600"
                                                    wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.time"
                                                    class="mt-1 block w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>
                                            @else
                                            <div class="flex space-x-4">
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Working Sets</label>
                                                    <input type="number" min="1" max="10"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.sets"
                                                        class="mt-1 block w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Working Reps</label>
                                                    <input type="number" min="1" max="100"
                                                        wire:model.live="schedule.{{ $currentWeek }}.{{ $currentDay }}.{{ $index }}.reps"
                                                        class="mt-1 block w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
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

            <div class="flex justify-end space-x-4">
                @if($existingPlan)
                <button type="button" wire:click="toggleDeleteConfirmModal"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                    Delete Plan
                </button>
                @endif
                <button type="button" wire:click="confirmSave"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    {{ $existingPlan ? 'Update Plan' : 'Create Plan' }}
                </button>
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
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto sm:text-sm">
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
                                                                            Duration: {{ $this->formatDuration($exercise['time']) }}
                                                                        @else
                                                                            {{ $exercise['sets'] }} sets × {{ $exercise['reps'] }} reps
                                                                        @endif
                                                                        @if($exercise['has_warmup'])
                                                                            <br>
                                                                            <span class="text-gray-500 dark:text-gray-500">
                                                                                Warmup: 
                                                                                @if($exercise['is_time_based'])
                                                                                    {{ $this->formatDuration($exercise['warmup_time']) }}
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
    @if($showConfirmModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Replace Existing Plan
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    You already have a workout plan ("{{ $existingPlan?->name }}"). Creating a new plan will replace your existing one. Are you sure you want to continue?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="save"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm">
                        Replace Plan
                    </button>
                    <button type="button" wire:click="toggleConfirmModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m6.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Delete Workout Plan
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to delete your workout plan? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="deletePlan"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete Plan
                    </button>
                    <button type="button" wire:click="toggleDeleteConfirmModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
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
