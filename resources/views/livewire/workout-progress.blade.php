<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Workout Progress</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="exercise" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Exercise</label>
                <select wire:model.live="selectedExercise" id="exercise"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Choose an exercise</option>
                    @foreach ($exercises as $exercise)
                        <option value="{{ $exercise->id }}">{{ $exercise->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="timeframe" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timeframe</label>
                <select wire:model.live="timeframe" id="timeframe"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
        </div>

        @if ($selectedExercise && !empty($progressData))
            <div class="space-y-8">
                <!-- Progress Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 mb-2">Max Weight</h3>
                        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ number_format(max(array_column($progressData, 'max_weight')) * 2.20462, 1) }} lb
                        </p>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-green-900 dark:text-green-100 mb-2">Total Volume</h3>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ array_sum(array_column($progressData, 'total_volume')) }} kg
                        </p>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-purple-900 dark:text-purple-100 mb-2">Total Reps</h3>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ array_sum(array_column($progressData, 'total_reps')) }}
                        </p>
                    </div>
                </div>

                <!-- Progress Table -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Detailed Progress</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Max Weight (lb)
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total Volume (kg)
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total Reps
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($progressData as $date => $data)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ $data['max_weight'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ $data['total_volume'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ $data['total_reps'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">Select an exercise to view progress data.</p>
            </div>
        @endif
    </div>
</div>
