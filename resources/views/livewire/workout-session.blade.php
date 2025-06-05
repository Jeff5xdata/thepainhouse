<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Current Workout</h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Week {{ $workoutSession->week_number }}, {{ $workoutSession->day_name }}
            </div>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        <div class="space-y-6">
            @foreach ($exercises as $exercise)
                <div class="border dark:border-gray-700 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $exercise->name }}</h3>
                    <div class="space-y-4">
                        @foreach ($exerciseSets[$exercise->id] as $set)
                            <div class="flex items-center space-x-4 {{ $set->completed ? 'bg-green-50 dark:bg-green-900/20' : ($set->is_warmup ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-gray-50 dark:bg-gray-700') }} p-4 rounded-lg">
                                <div class="w-20">
                                    <span class="text-sm {{ $set->is_warmup ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $set->is_warmup ? 'Warmup' : 'Set' }} {{ $set->set_number }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400">Weight (lb)</label>
                                            <input type="number" wire:model="weight" step="0.5"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="{{ $set->weight ?? 'Enter weight' }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400">Reps</label>
                                            <input type="number" wire:model="reps"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="{{ $set->reps ?? 'Enter reps' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <button wire:click="toggleSetCompletion({{ $set->id }})"
                                    class="{{ $set->completed ? 'bg-green-600' : 'bg-gray-200 dark:bg-gray-600' }} relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    <span class="{{ $set->completed ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-end">
            <button wire:click="completeWorkout"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                Complete Workout
            </button>
        </div>
    </div>
</div>
