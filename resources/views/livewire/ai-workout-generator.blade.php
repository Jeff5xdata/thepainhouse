<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                        AI Workout Generator
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Generate personalized workout plans using AI based on your preferences, fitness level, and goals.
                    </p>
                </div>

                <!-- Error Message -->
                @if($error)
                    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-800 dark:text-red-200">{{ $error }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Success Message -->
                @if(session('message'))
                    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Generator Form -->
                <form wire:submit="generateWorkout" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Week Duration -->
                        <div>
                            <label for="weeks_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Plan Duration (Weeks)
                            </label>
                            <input type="number" 
                                   id="weeks_duration" 
                                   wire:model="weeks_duration" 
                                   min="1" 
                                   max="52"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('weeks_duration') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Split Type -->
                        <div>
                            <label for="split_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Workout Split
                            </label>
                            <select id="split_type" 
                                    wire:model="split_type"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                @foreach($splitTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('split_type') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fitness Level -->
                        <div>
                            <label for="fitness_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fitness Level
                            </label>
                            <select id="fitness_level" 
                                    wire:model="fitness_level"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                @foreach($fitnessLevels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('fitness_level') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Goals -->
                        <div>
                            <label for="goals" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Primary Goal
                            </label>
                            <select id="goals" 
                                    wire:model="goals"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                @foreach($goalTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('goals') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Equipment -->
                        <div>
                            <label for="equipment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Available Equipment
                            </label>
                            <select id="equipment" 
                                    wire:model="equipment"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                @foreach($equipmentTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('equipment') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Time per Workout -->
                        <div>
                            <label for="time_per_workout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Time per Workout (Minutes)
                            </label>
                            <input type="number" 
                                   id="time_per_workout" 
                                   wire:model="time_per_workout" 
                                   min="30" 
                                   max="180"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('time_per_workout') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="flex justify-center">
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="generateWorkout" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ $isGenerating ? 'Generating...' : 'Generate Workout Plan' }}
                        </button>
                    </div>
                </form>

                <!-- Preview Modal -->
                @if($showPreview && $generatedPlan)
                    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closePreview">
                        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                            <div class="mt-3">
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ $generatedPlan['name'] }}
                                    </h3>
                                    <button wire:click="closePreview" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Plan Description -->
                                <div class="mb-6">
                                    <p class="text-gray-600 dark:text-gray-400">{{ $generatedPlan['description'] }}</p>
                                </div>

                                <!-- Workout Schedule Preview -->
                                <div class="space-y-6 max-h-96 overflow-y-auto">
                                    @foreach($generatedPlan['schedule'] as $weekNumber => $weekDays)
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                                Week {{ $weekNumber }}
                                            </h4>
                                            <div class="space-y-4">
                                                @foreach($weekDays as $dayNumber => $exercises)
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-3">
                                                            {{ $daysOfWeek[$dayNumber] ?? 'Day ' . $dayNumber }}
                                                            @if(isset($exercises[0]['workout_type']))
                                                                <span class="text-sm text-blue-600 dark:text-blue-400 ml-2">
                                                                    ({{ $exercises[0]['workout_type'] }})
                                                                </span>
                                                            @endif
                                                        </h5>
                                                        <div class="space-y-2">
                                                            @foreach($exercises as $exercise)
                                                                <div class="flex justify-between items-center text-sm">
                                                                    <span class="text-gray-700 dark:text-gray-300">
                                                                        {{ $exercise['exercise_name'] }}
                                                                    </span>
                                                                    <span class="text-gray-500 dark:text-gray-400">
                                                                        {{ $exercise['sets'] }} sets × {{ $exercise['reps'] }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button wire:click="closePreview" 
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </button>
                                    <button wire:click="saveWorkoutPlan" 
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Save Workout Plan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div> 