<div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                <a href="{{ route('workplan.session') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
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

                <a href="{{ route('progress') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-500 rounded-full">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Track Progress</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">View your workout history and stats</p>
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
                            <a href="{{ route('workout.view', $plan) }}" class="block border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</h4>
                                        @if($plan->description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                                        @endif
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $plan->weeks_duration }} week{{ $plan->weeks_duration > 1 ? 's' : '' }} •
                                            Created {{ $plan->created_at->format('F j, Y') }}
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
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
                        <a href="{{ route('workplan.session') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Start New Session</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($recentSessions as $session)
                            <a href="{{ route('workplan.session.view', $session) }}" class="block border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $session->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $session->workoutPlan->name }}</p>
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $session->exerciseSets->count() }} exercises • 
                                            {{ $session->created_at->format('F j, Y g:i A') }}
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-gray-500 dark:text-gray-400">No workout sessions recorded yet. Start your fitness journey today!</p>
                                <a href="{{ route('workplan.session') }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
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

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Personal Records</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $recentSessions->flatMap->exerciseSets->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 