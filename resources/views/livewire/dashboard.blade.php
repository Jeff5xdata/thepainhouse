<div>
    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Workouts</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalWorkouts }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Streak</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $activeStreak }} days</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Weight Lifted</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalWeight) }} lb</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-{{ $workoutPlans->isEmpty() ? '3' : '2' }} gap-6">
                @if($workoutPlans->isEmpty())
                    <a href="{{ route('workout.planner') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-indigo-500 rounded-full">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Workout Plan</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Design a new workout routine</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @endif

                <a href="{{ route('workout.session') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-500 rounded-full">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Start Workout</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Record a new workout session</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('workout.exercises') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-500 rounded-full">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Exercise Library</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Browse and manage exercises</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Workout Plans -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Your Workout Plans</h3>
                        <a href="{{ route('workout.planner') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Create New Plan</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($workoutPlans as $plan)
                            <div class="block border dark:border-gray-700 rounded-lg p-4 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</div>
                                        @if($plan->description)
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</div>
                                        @endif
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Created {{ $plan->created_at->format('F j, Y') }}
                                        </div>
                                    </div>

                                    <a href="{{ route('workout.planner', ['plan_id' => $plan->id]) }}" 
                                    class="inline-flex items-center p-2 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition custom-tooltip-right"
                                    data-tooltip="Edit Plan">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-gray-500 dark:text-gray-400">No workout plans created yet. Start by creating your first plan!</p>
                                <a href="{{ route('workout.planner') }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Create Your First Plan
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Workout Sessions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Workout Sessions</h3>
                        <a href="{{ route('workout.session') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Start New Session</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($recentSessions as $session)
                            <div class="block border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $session->workoutPlan->name }}</h4>
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $session->exerciseSets->count() }} exercises • 
                                            {{ $session->created_at->format('F j, Y g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-gray-500 dark:text-gray-400">No workout sessions recorded yet. Start your fitness journey today!</p>
                                <a href="{{ route('workout.session') }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Start Your First Workout
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 