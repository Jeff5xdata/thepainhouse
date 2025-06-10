<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Today's Workout</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Week {{ $currentWeek }} - {{ $currentDay }}</p>
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">{{ $sessionDate }}</span>
                </div>

                <!-- Rest Timer Modal -->
                <div id="restTimerModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg px-8 py-6 max-w-sm mx-auto">
                        <div class="text-center">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Rest Timer</h3>
                            <div class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6" id="timerDisplay">
                                {{ auth()->user()->workoutSettings?->default_rest_timer ?? 60 }}
                            </div>
                            <button type="button" 
                                onclick="closeRestTimer()"
                                class="inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    let timer;
                    let timeLeft;

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
                </script>

                @if($isNewSession)
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
                                @if(count($todayExercises) > 0)
                                    @foreach($todayExercises as $scheduleItem)
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">

                                        <!-- Floating Rest Timer Button -->
                                        <div class="flex justify-between z-50">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $scheduleItem->exercise->name }}</h3>
                                            <button type="button" 
                                                        onclick="openRestTimer()"
                                                        data-tooltip="Rest Timer"
                                                        class="flex items-center p-3 bg-green-600 border border-transparent rounded-lg shadow-xl text-white hover:bg-green-700 
                                                        focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 custom-tooltip-left">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex justify-between z-50">
                                            &nbsp;
                                        </div>

                                            @if($scheduleItem->has_warmup)
                                                <div class="mb-6">
                                                    <h4 class="text-sm font-medium text-yellow-600 dark:text-yellow-400 mb-3">Warmup Sets</h4>
                                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                                        <div>
                                                            <span class="text-gray-600 dark:text-gray-400">Sets: {{ $scheduleItem->warmup_sets }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-600 dark:text-gray-400">Reps: {{ $scheduleItem->warmup_reps }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-600 dark:text-gray-400">Weight: {{ $scheduleItem->warmup_weight_percentage }}% of working weight</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="grid gap-4">
                                                <div class="grid grid-cols-2 gap-4 mb-2">
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Planned</h4>
                                                        <div class="mt-1 grid grid-cols-2 gap-2 text-sm">
                                                            <div class="text-gray-600 dark:text-gray-400">
                                                                Sets: {{ $scheduleItem->sets }}
                                                            </div>
                                                            <div class="text-gray-600 dark:text-gray-400">
                                                                @if($scheduleItem->is_time_based)
                                                                    Time: {{ $scheduleItem->time_in_seconds }}s
                                                                @else
                                                                    Reps: {{ $scheduleItem->reps }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(isset($lastWorkouts[$scheduleItem->exercise_id]))
                                                        <div>
                                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Workout</h4>
                                                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                                Weight: {{ number_format($lastWorkouts[$scheduleItem->exercise_id]->weight, 1) }} lb
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="mt-4 grid grid-cols-3 gap-4">
                                                    <div>
                                                        <label class="block text-sm text-gray-700 dark:text-gray-300">Sets</label>
                                                        <input type="number" 
                                                            wire:model="sets.{{ $scheduleItem->exercise_id }}"
                                                            min="1"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        @error('sets.' . $scheduleItem->exercise_id)
                                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    @if(!$scheduleItem->is_time_based)
                                                        <div>
                                                            <label class="block text-sm text-gray-700 dark:text-gray-300">Reps</label>
                                                            <input type="number"
                                                                wire:model="reps.{{ $scheduleItem->exercise_id }}"
                                                                min="1"
                                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                            @error('reps.' . $scheduleItem->exercise_id)
                                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <label class="block text-sm text-gray-700 dark:text-gray-300">Weight (lb)</label>
                                                        <input type="number"
                                                            wire:model="weight.{{ $scheduleItem->exercise_id }}"
                                                            step="0.5"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        @error('weight.' . $scheduleItem->exercise_id)
                                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-8 flex justify-between items-center">
                                                <div class="flex items-center">
                                                        <label for="useProgression{{ $scheduleItem->exercise_id }}" class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">Progression</label>
                                                        <button wire:click="toggleProgression({{ $scheduleItem->exercise_id }})"
                                                            class="{{ ($useProgression[$scheduleItem->exercise_id] ?? false) ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600' }} relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                            <span class="{{ ($useProgression[$scheduleItem->exercise_id] ?? false) ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                                        </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-gray-600 dark:text-gray-400">
                                        No exercises scheduled for today.
                                    </div>
                                @endif
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Complete Workout
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-12">
                            <div class="mb-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No exercises scheduled</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a workout plan</p>
                            </div>
                            <a href="{{ route('workout.planner', ['week' => $currentWeek, 'day' => strtolower($currentDay)]) }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Workout Plan
                            </a>
                        </div>
                    @endif
                @else
                    <div class="space-y-6">
                        @foreach ($exercises as $exercise)
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $exercise->name }}</h3>
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center">
                                            <label for="useProgression{{ $exercise->id }}" class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">Use Previous Values</label>
                                            <button wire:click="toggleProgression({{ $exercise->id }})"
                                                class="{{ ($useProgression[$exercise->id] ?? false) ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600' }} relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                <span class="{{ ($useProgression[$exercise->id] ?? false) ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                            </button>
                                        </div>
                                        <button wire:click="toggleProgress({{ $exercise->id }})"
                                            class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Show Progress
                                        </button>
                                    </div>
                                </div>

                                @if($showProgress[$exercise->id] ?? false)
                                    <div class="mb-4 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        @if(isset($lastWorkouts[$exercise->id]))
                                            <div class="grid grid-cols-3 gap-4 text-sm">
                                                <div>
                                                    <span class="block text-gray-500 dark:text-gray-400">Last Weight</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ number_format($lastWorkouts[$exercise->id]->weight, 1) }} lb</span>
                                                </div>
                                                <div>
                                                    <span class="block text-gray-500 dark:text-gray-400">Last Reps</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $lastWorkouts[$exercise->id]->reps }}</span>
                                                </div>
                                                <div>
                                                    <span class="block text-gray-500 dark:text-gray-400">Last Volume</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ number_format($lastWorkouts[$exercise->id]->weight * $lastWorkouts[$exercise->id]->reps) }} lb</span>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 dark:text-gray-400">No previous workout data available.</p>
                                        @endif
                                    </div>
                                @endif

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
                                                        <input type="number" wire:model="weight.{{ $exercise->id }}" step="0.5"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                            placeholder="{{ $set->weight ?? 'Enter weight' }}">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Reps</label>
                                                        <input type="number" wire:model="reps.{{ $exercise->id }}"
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
                        <button type="button"
                            wire:click="completeWorkout"
                            class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Complete Workout
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
