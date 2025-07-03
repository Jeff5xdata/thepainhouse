<div class="py-4 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 sm:p-6">
                <!-- Filters -->
                <div class="mb-6 sm:mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Search -->
                    <div class="space-y-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input type="text" wire:model.live="search" id="search" 
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Search workouts...">
                    </div>

                    <!-- Date Range Filter -->
                    <div class="space-y-2">
                        <label for="dateRange" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                        <select wire:model.live="dateRange" id="dateRange" 
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">Last Week</option>
                            <option value="month">Last Month</option>
                            <option value="year">Last Year</option>
                            <option value="custom">Custom Date</option>
                        </select>
                        @if($dateRange === 'custom')
                            <input type="date" wire:model.live="selectedDate"
                                class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @endif
                    </div>

                    <!-- Workout Plan Filter -->
                    <div class="space-y-2 sm:col-span-2 lg:col-span-1">
                        <label for="selectedPlan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Workout Plan</label>
                        <select wire:model.live="selectedPlan" id="selectedPlan" 
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Plans</option>
                            @foreach($workoutPlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden">
                    <div class="space-y-4">
                        @forelse($sessions as $session)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="space-y-3">
                                    <!-- Header -->
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $session->workoutPlan->name }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $session->completed_at ? $session->completed_at->format('F j, Y g:i A') : ($session->date ? $session->date->format('F j, Y g:i A') : 'Date not set') }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ number_format($session->total_volume) }} lb
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $session->exerciseSets->count() }} sets
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Exercises with Individual Sets -->
                                    <div class="space-y-4">
                                        @foreach($session->exerciseSets->groupBy('exercise_id') as $exerciseSets)
                                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                                                <div class="mb-2">
                                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $exerciseSets->first()->exercise->name }}
                                                    </h4>
                                                </div>
                                                
                                                <!-- Warmup Sets -->
                                                @php
                                                    $warmupSets = $exerciseSets->where('is_warmup', true)->sortBy('set_number');
                                                    $workingSets = $exerciseSets->where('is_warmup', false)->sortBy('set_number');
                                                @endphp
                                                
                                                @if($warmupSets->count() > 0)
                                                    <div class="mb-2">
                                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Warmup Sets</p>
                                                        <div class="space-y-1">
                                                            @foreach($warmupSets as $set)
                                                                <div class="flex justify-between text-xs">
                                                                    <span class="text-gray-600 dark:text-gray-400">Set {{ $set->set_number }}</span>
                                                                    <span class="text-gray-900 dark:text-gray-100">
                                                                        @if($set->time_in_seconds)
                                                                            {{ $set->time_in_seconds }}s
                                                                        @else
                                                                            {{ $set->weight }} lb × {{ $set->reps }} reps
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- Working Sets -->
                                                @if($workingSets->count() > 0)
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Working Sets</p>
                                                        <div class="space-y-1">
                                                            @foreach($workingSets as $set)
                                                                <div class="flex justify-between text-xs">
                                                                    <span class="text-gray-600 dark:text-gray-400">Set {{ $set->set_number }}</span>
                                                                    <span class="text-gray-900 dark:text-gray-100">
                                                                        @if($set->time_in_seconds)
                                                                            {{ $set->time_in_seconds }}s
                                                                        @else
                                                                            {{ $set->weight }} lb × {{ $set->reps }} reps
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($exerciseSets->first()->notes)
                                                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 italic">
                                                        {{ $exerciseSets->first()->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex space-x-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('workout.history.details', ['workoutSession' => $session->id]) }}" 
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                            View Details
                                        </a>
                                        <a href="{{ route('workout.session.edit', ['workoutSession' => $session->id]) }}" 
                                            class="text-sm text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 font-medium">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No workout sessions found.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto rounded-lg border dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-150" wire:click="sortBy('completed_at')">
                                    <div class="flex items-center space-x-1">
                                        <span>Date</span>
                                        @if($sortField === 'completed_at')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Workout Plan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Exercises & Sets
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Total Volume
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($sessions as $session)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $session->completed_at ? $session->completed_at->format('F j, Y') : ($session->date ? $session->date->format('F j, Y') : 'Date not set') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $session->workoutPlan->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="space-y-4">
                                            @foreach($session->exerciseSets->groupBy('exercise_id') as $exerciseSets)
                                                <div class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-b-0 last:pb-0">
                                                    <div class="mb-2">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $exerciseSets->first()->exercise->name }}
                                                        </h4>
                                                    </div>
                                                    
                                                    @php
                                                        $warmupSets = $exerciseSets->where('is_warmup', true)->sortBy('set_number');
                                                        $workingSets = $exerciseSets->where('is_warmup', false)->sortBy('set_number');
                                                    @endphp
                                                    
                                                    <!-- Warmup Sets -->
                                                    @if($warmupSets->count() > 0)
                                                        <div class="mb-2">
                                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Warmup Sets</p>
                                                            <div class="grid grid-cols-3 gap-2 text-xs">
                                                                @foreach($warmupSets as $set)
                                                                    <div class="bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded">
                                                                        <div class="text-gray-600 dark:text-gray-400">Set {{ $set->set_number }}</div>
                                                                        <div class="text-gray-900 dark:text-gray-100 font-medium">
                                                                            @if($set->time_in_seconds)
                                                                                {{ $set->time_in_seconds }}s
                                                                            @else
                                                                                {{ $set->weight }} lb × {{ $set->reps }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Working Sets -->
                                                    @if($workingSets->count() > 0)
                                                        <div>
                                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Working Sets</p>
                                                            <div class="grid grid-cols-3 gap-2 text-xs">
                                                                @foreach($workingSets as $set)
                                                                    <div class="bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                                                                        <div class="text-gray-600 dark:text-gray-400">Set {{ $set->set_number }}</div>
                                                                        <div class="text-gray-900 dark:text-gray-100 font-medium">
                                                                            @if($set->time_in_seconds)
                                                                                {{ $set->time_in_seconds }}s
                                                                            @else
                                                                                {{ $set->weight }} lb × {{ $set->reps }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($exerciseSets->first()->notes)
                                                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 italic">
                                                            {{ $exerciseSets->first()->notes }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format($session->total_volume) }} lb
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex space-x-3">
                                            <a href="{{ route('workout.history.details', ['workoutSession' => $session->id]) }}" 
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                                View Details
                                            </a>
                                            <a href="{{ route('workout.session.edit', ['workoutSession' => $session->id]) }}" 
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 font-medium">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No workout sessions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 sm:mt-6">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>
    </div>
</div> 