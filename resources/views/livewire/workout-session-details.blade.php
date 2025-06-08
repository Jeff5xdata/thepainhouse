<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $session->workoutPlan->name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $session->completed_at ? $session->completed_at->format('F j, Y g:i A') : $session->created_at->format('F j, Y g:i A') }}
                    </p>
                </div>
                <a href="{{ route('workout.history') }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to History
                </a>
            </div>
        </div>

        <!-- Session Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Exercises</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalExercises }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sets</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalSets }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Reps</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalReps }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Volume</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalVolume) }} lb</div>
            </div>
        </div>

        <!-- Exercise Details -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Exercise Details</h2>
                
                <div class="space-y-8">
                    @foreach($exerciseGroups as $group)
                        <div class="border dark:border-gray-700 rounded-lg p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $group['exercise']->name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $group['working_sets']->count() }} sets • 
                                        {{ $group['total_reps'] }} total reps • 
                                        Max weight: {{ $group['max_weight'] }} lb • 
                                        Volume: {{ number_format($group['total_volume']) }} lb
                                    </p>
                                </div>
                            </div>

                            <!-- Warmup Sets -->
                            @if($group['warmup_sets']->isNotEmpty())
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-yellow-600 dark:text-yellow-400 mb-2">Warmup Sets</h4>
                                    <div class="grid grid-cols-4 gap-4 text-sm">
                                        @foreach($group['warmup_sets'] as $set)
                                            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded p-2">
                                                <span class="text-yellow-800 dark:text-yellow-200">Set {{ $set->set_number }}</span>
                                                <div class="text-yellow-600 dark:text-yellow-400">
                                                    {{ $set->reps }} reps @ {{ $set->weight }} lb
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Working Sets -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Working Sets</h4>
                                <div class="grid grid-cols-4 gap-4 text-sm">
                                    @foreach($group['working_sets'] as $set)
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                            <span class="text-gray-800 dark:text-gray-200">Set {{ $set->set_number }}</span>
                                            <div class="text-gray-600 dark:text-gray-400">
                                                {{ $set->reps }} reps @ {{ $set->weight }} lb
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div> 