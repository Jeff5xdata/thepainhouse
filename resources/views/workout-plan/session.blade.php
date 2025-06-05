@php
    $isNewSession = !isset($session);
    $workoutPlan = $isNewSession ? $workoutPlan : $session->workoutPlan;
    $currentWeek = $isNewSession ? $currentWeek : $session->week_number;
    $currentDay = $isNewSession ? $currentDay : ucfirst($session->day_of_week);
    $sessionDate = $isNewSession ? $sessionDate : $session->date->format('Y-m-d');
    $todayExercises = $isNewSession ? $todayExercises : $workoutPlan->getScheduleForDay($session->week_number, $session->day_of_week);
@endphp

<x-app-layout>

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

                    <!-- Floating Rest Timer Button -->
                    <div class="fixed right-0 top-1/3 -mr-16" style="z-index: 999;">
                        <button type="button" 
                            onclick="openRestTimer()"
                            class="flex items-center px-4 py-3 bg-green-600 border border-transparent rounded-lg shadow-xl text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Rest Timer</span>
                        </button>
                    </div>

                    <!-- Rest Timer Modal -->
                    <div id="restTimerModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden items-center justify-center z-50">
                        <div class="bg-white dark:bg-gray-800 rounded-lg px-8 py-6 max-w-sm mx-auto">
                            <div class="text-center">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Rest Timer</h3>
                                <div class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6" id="timerDisplay">
                                    60
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
                            
                            timeLeft = 60;
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
                        <form method="POST" action="{{ route('workplan.save-session') }}" class="space-y-6">
                            @csrf
                            <input type="hidden" name="workout_plan_id" value="{{ $workoutPlan->id }}">
                            <input type="hidden" name="session_name" value="{{ $workoutPlan->name }} - {{ $sessionDate }}">
                            <input type="hidden" name="week_number" value="{{ $currentWeek }}">
                            <input type="hidden" name="day_of_week" value="{{ strtolower($currentDay) }}">

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

                            @if (session('error'))
                                <div class="bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400 p-4 mb-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid gap-6">
                                @if(count($todayExercises) > 0)
                                    @foreach($todayExercises as $scheduleItem)
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $scheduleItem->exercise->name }}</h3>

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

                                                <input type="hidden" name="has_warmup[{{ $scheduleItem->exercise_id }}]" value="1">
                                                <input type="hidden" name="warmup_sets[{{ $scheduleItem->exercise_id }}]" value="{{ $scheduleItem->warmup_sets }}">
                                                <input type="hidden" name="warmup_reps[{{ $scheduleItem->exercise_id }}]" value="{{ $scheduleItem->warmup_reps }}">
                                                <input type="hidden" name="warmup_weight_percentage[{{ $scheduleItem->exercise_id }}]" value="{{ $scheduleItem->warmup_weight_percentage }}">
                                            @endif

                                            @if($isNewSession || (isset($isEditing) && $isEditing))
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
                                                                name="sets[{{ $scheduleItem->exercise_id }}]" 
                                                                value="{{ old('sets.' . $scheduleItem->exercise_id, isset($session) ? count($session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id)->where('is_warmup', false)) : $scheduleItem->sets) }}"
                                                                min="1"
                                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('sets.' . $scheduleItem->exercise_id) border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                                            @error('sets.' . $scheduleItem->exercise_id)
                                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                        @if(!$scheduleItem->is_time_based)
                                                            <div>
                                                                <label class="block text-sm text-gray-700 dark:text-gray-300">Reps</label>
                                                                <input type="number" 
                                                                    name="reps[{{ $scheduleItem->exercise_id }}]" 
                                                                    value="{{ old('reps.' . $scheduleItem->exercise_id, isset($session) ? ($session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id)->where('is_warmup', false)->first()->reps ?? $scheduleItem->reps) : $scheduleItem->reps) }}"
                                                                    min="1"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('reps.' . $scheduleItem->exercise_id) border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                                                @error('reps.' . $scheduleItem->exercise_id)
                                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                        @else
                                                            <div>
                                                                <label class="block text-sm text-gray-700 dark:text-gray-300">Time (seconds)</label>
                                                                <input type="number" 
                                                                    name="time[{{ $scheduleItem->exercise_id }}]" 
                                                                    value="{{ old('time.' . $scheduleItem->exercise_id, isset($session) ? ($session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id)->where('is_warmup', false)->first()->time_in_seconds ?? $scheduleItem->time_in_seconds) : $scheduleItem->time_in_seconds) }}"
                                                                    min="1"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('time.' . $scheduleItem->exercise_id) border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                                                @error('time.' . $scheduleItem->exercise_id)
                                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="flex items-center justify-between mb-2">
                                                                <label class="block text-sm text-gray-700 dark:text-gray-300">Weight (lb)</label>
                                                                <div class="flex items-center">
                                                                    <label class="text-sm text-gray-600 dark:text-gray-400 mr-2">Mark as Progression</label>
                                                                    <input type="checkbox" 
                                                                        name="use_progression[{{ $scheduleItem->exercise_id }}]"
                                                                        {{ isset($session) && $session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id)->where('is_warmup', false)->first()->used_progression ? 'checked' : '' }}
                                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                                </div>
                                                            </div>
                                                            <input type="number" 
                                                                step="0.5" 
                                                                name="weight[{{ $scheduleItem->exercise_id }}]" 
                                                                value="{{ old('weight.' . $scheduleItem->exercise_id, 
                                                                    isset($session) 
                                                                        ? ($session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id)->where('is_warmup', false)->first()->weight ?? '')
                                                                        : (isset($lastWorkouts[$scheduleItem->exercise_id]) && $lastWorkouts[$scheduleItem->exercise_id]->used_progression 
                                                                            ? $lastWorkouts[$scheduleItem->exercise_id]->weight + 10 
                                                                            : ($lastWorkouts[$scheduleItem->exercise_id]->weight ?? ''))) }}"
                                                                min="0"
                                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('weight.' . $scheduleItem->exercise_id) border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                                            @error('weight.' . $scheduleItem->exercise_id)
                                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="mt-4">
                                                        <label class="block text-sm text-gray-700 dark:text-gray-300">Notes</label>
                                                        <textarea 
                                                            name="notes[{{ $scheduleItem->exercise_id }}]" 
                                                            rows="2"
                                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes.' . $scheduleItem->exercise_id) border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('notes.' . $scheduleItem->exercise_id, isset($session) ? ($session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id)->where('is_warmup', false)->first()->notes ?? '') : '') }}</textarea>
                                                        @error('notes.' . $scheduleItem->exercise_id)
                                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-4">
                                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Completed Sets</h4>
                                                    @foreach($session->exerciseSets->where('exercise_id', $scheduleItem->exercise_id) as $set)
                                                        <div class="mt-2 grid grid-cols-4 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                                            <div>Set {{ $set->set_number }}</div>
                                                            <div>{{ $set->is_warmup ? 'Warmup' : 'Working' }}</div>
                                                            <div>
                                                                @if($scheduleItem->is_time_based)
                                                                    {{ $set->time_in_seconds }}s
                                                                @else
                                                                    {{ $set->reps }} reps
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center">
                                                                {{ number_format($set->weight, 1) }} lb
                                                                @if($set->used_progression)
                                                                    <span class="ml-2 text-green-600 dark:text-green-400 text-xs">(Progression)</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if($set->notes)
                                                            <div class="mt-1 text-sm text-gray-500">{{ $set->notes }}</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-8">
                                        <p class="text-gray-600 dark:text-gray-400">No exercises scheduled for today.</p>
                                    </div>
                                @endif
                            </div>

                            @if($isNewSession || (isset($isEditing) && $isEditing))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Session Notes</label>
                                    <textarea 
                                        name="session_notes" 
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('session_notes') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('session_notes', isset($session) ? $session->notes : '') }}</textarea>
                                    @error('session_notes')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <button type="submit" 
                                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                        {{ $isNewSession ? 'Save and Complete Workout' : 'Update Workout' }}
                                    </button>
                                </div>
                            @else
                                <div class="mt-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Session Notes</h3>
                                    @if($session->notes)
                                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $session->notes }}</p>
                                    @else
                                        <p class="mt-2 text-gray-500 italic">No notes for this session.</p>
                                    @endif
                                </div>
                            @endif
                        </form>
                    @elseif(isset($isEditing) && $isEditing)
                        <form method="POST" action="{{ route('workplan.session.update', $session->id) }}" class="space-y-6">
                            @csrf
                    @else
                        <div class="grid gap-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Completed Workout</h3>
                                <a href="{{ route('workplan.session.edit', $session->id) }}" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Modify Workout
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 