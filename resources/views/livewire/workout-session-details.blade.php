<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="ml-2">
            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-2  space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 flex justify-start">
                        {{ $session->workoutPlan->name }}
                    </h1>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ $session->completed_at ? $session->completed_at->format('F j, Y g:i A') : ($session->date ? $session->date->format('F j, Y g:i A') : 'Date not set') }}
                    </p>
                </div>
                <div class="flex justify-end">
                <a href="{{ route('workout.history') }}" 
                    class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to History
                </a>
                </div>
            </div>
        </div>

        <!-- Session Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-all duration-200 border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Exercises</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalExercises }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-all duration-200 border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sets</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalSets }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-all duration-200 border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Reps</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalReps }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-all duration-200 border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Volume</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totalVolume) }} lb</div>
            </div>
        </div>

        <!-- Exercise Details -->
        <div class="mt-5">
                <div class="space-y-10">
                    @foreach($exerciseGroups as $group)
                        <div class="mb-4 border dark:border-gray-700 rounded-lg p-8 hover:shadow-md transition-all duration-200 bg-gray-50/50 dark:bg-gray-800/50
                        bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-2">
                                <!-- Left Column -->
                                <div class="space-y-8 mb-4 mt-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 ml-2">{{ $group['exercise']->name }}</h3>
                                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400  ml-2">
                                            {{ $group['working_sets']->count() }} sets • 
                                            @if($group['total_time'] > 0)
                                                {{ $group['total_time'] >= 60 ? floor($group['total_time'] / 60) . ' min ' . ($group['total_time'] % 60) . ' sec' : $group['total_time'] . ' sec' }} total time
                                            @else
                                                {{ $group['total_reps'] }} total reps
                                            @endif
                                            @if($group['max_weight'])
                                                • Max weight: {{ $group['max_weight'] }} lb
                                            @endif
                                            @if($group['total_volume'] > 0)
                                                • Volume: {{ number_format($group['total_volume']) }} lb
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Warmup Sets -->
                                    @if($group['warmup_sets']->isNotEmpty())
                                        <div>
                                            <h4 class="text-sm font-semibold text-yellow-600 dark:text-yellow-400 mb-4 ml-2">Warmup Sets</h4>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm ml-2">
                                                @foreach($group['warmup_sets'] as $set)
                                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 hover:shadow-sm transition-all duration-200 border border-yellow-100 dark:border-yellow-900/30">
                                                        <span class="block text-yellow-800 dark:text-yellow-200 font-semibold">Set {{ $set->set_number }}</span>
                                                        <div class="mt-2 text-yellow-600 dark:text-yellow-400">
                                                            @if($set->time_in_seconds)
                                                                {{ $set->formatted_time }}
                                                            @else
                                                                {{ $set->reps }} reps @ {{ $set->weight }} lb
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Working Sets -->
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 ml-2">Working Sets</h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm ml-2">
                                            @foreach($group['working_sets'] as $set)
                                                <div class="bg-white dark:bg-gray-700 rounded-lg p-4 hover:shadow-sm transition-all duration-200 border border-gray-100 dark:border-gray-600">
                                                    <span class="block text-gray-800 dark:text-gray-200 font-semibold">Set {{ $set->set_number }}</span>
                                                    <div class="mt-2 text-gray-600 dark:text-gray-400">
                                                        @if($set->time_in_seconds)
                                                            {{ $set->formatted_time }}
                                                        @else
                                                            {{ $set->reps }} reps @ {{ $set->weight }} lb
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="border-l dark:border-gray-700 pl-10 mb-4 mt-4
                                bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 mt-2">Notes</h4>
                                        @php
                                            $allNotes = $group['working_sets']->pluck('notes')->filter()->unique();
                                            $hasTimeBasedSets = $group['working_sets']->whereNotNull('time_in_seconds')->isNotEmpty();
                                        @endphp
                                        
                                        @if($allNotes->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach($allNotes as $note)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap leading-relaxed">{{ $note }}</p>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">No notes available.</p>
                                        @endif
                                        
                                        @if($hasTimeBasedSets)
                                            <div class="mt-4">
                                                <h5 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Time-based sets:</h5>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                                                    @foreach($group['working_sets']->whereNotNull('time_in_seconds') as $timeSet)
                                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded p-2 border border-blue-100 dark:border-blue-900/30">
                                                            <span class="text-blue-800 dark:text-blue-200 font-medium">Set {{ $timeSet->set_number }}</span>
                                                            <div class="text-blue-600 dark:text-blue-400">{{ $timeSet->formatted_time }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div> 
