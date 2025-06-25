<x-guest>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                @guest
                        <!-- Call to Action Card -->
                        <div class="bg-white dark:bg-gray-600  rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 mb-4">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-200 mb-2">Want to create your own workout plans?</h3>
                                <p class="text-gray-500 dark:text-gray-300 mb-4">Join The Pain House today and get access to all features!</p>
                                <div class="flex space-x-4">
                                    <a href="{{ route('register') }}" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Create Free Account
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endguest
            <!-- Main Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <!-- Header Section -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold mb-2 text-gray-900 dark:text-gray-100">{{ $workoutPlan->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-400">Created by {{ $workoutPlan->user->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">This shared link expires on {{ $shareLink->expires_at->format('F j, Y') }}</p>
                    </div>

                    <!-- Workout Schedule -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Workout Schedule</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            @endphp

                            @for($week = 1; $week <= $workoutPlan->weeks_duration; $week++)
                                <!-- Week Card -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm overflow-hidden">
                                    <!-- Week Header -->
                                    <div class="bg-gray-100 dark:bg-gray-600 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Week {{ $week }}</h4>
                                    </div>
                                    <!-- Week Content -->
                                    <div class="p-4">
                                        @foreach($daysOfWeek as $day)
                                            @php
                                                $scheduleItems = $workoutPlan->scheduleItems()
                                                    ->where('week_number', $week)
                                                    ->where('day_of_week', $day)
                                                    ->orderBy('order_in_day')
                                                    ->with('exercise')
                                                    ->get();
                                            @endphp
                                            @if($scheduleItems->isNotEmpty())
                                                <div class="mb-4 last:mb-0">
                                                    <h5 class="text-md font-medium mb-2 text-gray-800 dark:text-gray-200 capitalize">{{ $day }}</h5>
                                                    <ul class="space-y-2">
                                                        @php
                                                            $grouped = $scheduleItems->groupBy('exercise_id');
                                                        @endphp
                                                        @foreach($grouped as $exerciseId => $items)
                                                            @php
                                                                $exercise = $items->first()->exercise;
                                                                $totalSets = $items->sum('sets');
                                                                $totalReps = $items->sum('reps');
                                                                $isTimeBased = $items->first()->is_time_based;
                                                                $totalTime = $items->sum('time_in_seconds');
                                                            @endphp
                                                            <li class="text-sm text-gray-600 dark:text-gray-300 flex justify-between items-center">
                                                                <span>{{ $exercise->name }}</span>
                                                                <span class="text-gray-500 dark:text-gray-400 ml-2">
                                                                    @if($isTimeBased)
                                                                        {{ $totalSets }}x{{ $totalTime }}s
                                                                    @else
                                                                        {{ $totalSets }}x{{ $totalReps }}
                                                                    @endif
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Exercises List -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">All Exercises</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $allItems = $workoutPlan->scheduleItems()->with('exercise')->get();
                                $groupedExercises = $allItems->groupBy('exercise_id');
                            @endphp
                            @foreach($groupedExercises as $exerciseId => $items)
                                @php
                                    $exercise = $items->first()->exercise;
                                    $totalSets = $items->sum('sets');
                                    $totalReps = $items->sum('reps');
                                    $isTimeBased = $items->first()->is_time_based;
                                    $totalTime = $items->sum('time_in_seconds');
                                @endphp
                                <!-- Exercise Card -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm overflow-hidden">
                                    <!-- Exercise Header -->
                                    <div class="bg-gray-100 dark:bg-gray-600 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">{{ $exercise->name }}</h4>
                                    </div>
                                    <!-- Exercise Content -->
                                    <div class="p-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-300">

                                            @if($items->first()->has_warmup)
                                                <p class="font-medium text-gray-700 dark:text-gray-200 mb-2">Warmup</p>
                                                    <p class="flex justify-between">
                                                        <span>Sets x Reps:</span>
                                                        <span class="text-gray-500 dark:text-gray-400">{{ $items->first()->warmup_sets }}x{{ $items->first()->warmup_reps }}</span>
                                                    </p>
                                                    @if($items->first()->warmup_weight_percentage)
                                                        <p class="flex justify-between">
                                                            <span>Weight:</span>
                                                            <span class="text-gray-500 dark:text-gray-400">{{ $items->first()->warmup_weight_percentage }}% of working weight</span>
                                                        </p>
                                                    @endif
                                            @endif
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                <p class="font-medium text-gray-700 dark:text-gray-200 mb-2">Working Sets</p>
                                                @if($isTimeBased)
                                                    <p class="flex justify-between">
                                                        <span>Sets:</span>
                                                        <span class="text-gray-500 dark:text-gray-400">{{ $totalSets }}</span>
                                                    </p>
                                                    <p class="flex justify-between">
                                                        <span>Time:</span>
                                                        <span class="text-gray-500 dark:text-gray-400">{{ $totalTime }} seconds</span>
                                                    </p>
                                                @else
                                                    <p class="flex justify-between">
                                                        <span>Sets:</span>
                                                        <span class="text-gray-500 dark:text-gray-400">{{ $totalSets }}</span>
                                                    </p>
                                                    <p class="flex justify-between">
                                                        <span>Reps:</span>
                                                        <span class="text-gray-500 dark:text-gray-400">{{ $totalReps }}</span>
                                                    </p>
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
    </div>
</x-guest> 