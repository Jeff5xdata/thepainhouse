<div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-8">
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Food Tracker</h1>
                <p class="mt-1 sm:mt-2 text-gray-600 dark:text-gray-400 text-sm sm:text-base">Track your daily nutrition and scan barcodes to find food information</p>
            </div>
            <a href="{{ route('food-items.index') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Food Database
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm sm:text-base">
            {{ session('error') }}
        </div>
    @endif

    <!-- View Mode Toggle -->
    <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1 w-full sm:w-auto">
                <button wire:click="$set('viewMode', 'daily')" 
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'daily' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Daily View
                </button>
                <button wire:click="$set('viewMode', 'weekly')" 
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'weekly' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Weekly Summary
                </button>
            </div>
            
            @if($viewMode === 'daily')
                <!-- Date Picker for Daily View -->
                <div class="flex items-center space-x-2 w-full sm:w-auto">
                    <input type="date" wire:model="selectedDate" 
                           class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            @else
                <!-- Week Navigation for Weekly View -->
                <div class="flex items-center justify-between sm:justify-start space-x-4 w-full sm:w-auto">
                    <button wire:click="changeWeek('prev')" 
                            class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $weekRange['start'] }} - {{ $weekRange['end'] }}
                    </span>
                    <button wire:click="changeWeek('next')" 
                            class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($viewMode === 'weekly')
        <!-- Weekly Summary -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">Weekly Nutrition Summary</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-indigo-600">{{ number_format($dailyTotals['calories']) }}</div>
                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Total Calories</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-green-600">{{ number_format($dailyTotals['protein'], 1) }}g</div>
                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Total Protein</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-yellow-600">{{ number_format($dailyTotals['carbohydrates'], 1) }}g</div>
                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Total Carbs</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-red-600">{{ number_format($dailyTotals['fat'], 1) }}g</div>
                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Total Fat</div>
                </div>
            </div>

            <!-- Weekly Breakdown Chart -->
            <div class="mt-4 sm:mt-6">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white mb-2 sm:mb-4">Daily Breakdown</h3>
                <div class="grid grid-cols-3 sm:grid-cols-7 gap-2">
                    @foreach($weeklyBreakdown as $date => $dayData)
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $dayData['day_name'] }}</div>
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-2 {{ $dayData['has_data'] ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format($dayData['totals']['calories']) }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">cal</div>
                                @if($dayData['has_data'])
                                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                        {{ number_format($dayData['totals']['protein'], 1) }}g protein
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Weekly Food Log -->
        <div class="space-y-4 sm:space-y-6">
            @foreach($foodLogs as $date => $dayLogs)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                        {{ Carbon\Carbon::parse($date)->format('l, M j, Y') }}
                    </h3>
                    @foreach($mealTypes as $mealType => $mealLabel)
                        @if(isset($dayLogs[$mealType]) && $dayLogs[$mealType]->count() > 0)
                            <div class="mb-3 sm:mb-4">
                                <h4 class="text-sm sm:text-md font-medium text-gray-700 dark:text-gray-300 mb-1 sm:mb-2">{{ $mealLabel }}</h4>
                                <div class="space-y-1 sm:space-y-2">
                                    @foreach($dayLogs[$mealType] as $log)
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white text-xs sm:text-sm">{{ $log->foodItem->display_name }}</div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ $log->quantity }}x serving
                                                    @if($log->notes)
                                                        • {{ $log->notes }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right mt-1 sm:mt-0">
                                                <div class="font-medium text-gray-900 dark:text-white text-xs sm:text-sm">
                                                    {{ number_format($log->getNutritionValues()['calories']) }} cal
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        <!-- Daily View Content -->
        <!-- Search and Add Food Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">Add Food</h2>
            
            <!-- Search by Name -->
            <div class="mb-4 sm:mb-6">
                <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 sm:mb-2">Search by Food Name</label>
                <div class="relative">
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <input type="text" id="search" wire:model="searchQuery" 
                               placeholder="Type a food name to search..." 
                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <button wire:click="searchFood" 
                                class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 ml-2">
                            Search
                        </button>
                    </div>
                    
                    @if($showSearchResults && count($searchResults) > 0)
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto">
                            @foreach($searchResults as $result)
                                <button wire:click="selectFoodItem('{{ $result['id'] }}')" 
                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $result['name'] ?? 'Unknown' }}</div>
                                            @if(isset($result['brand']))
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $result['brand'] }}</div>
                                            @endif
                                        </div>
                                        @if(isset($result['is_local']) && $result['is_local'])
                                            <span class="text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-2 py-1 rounded-full">
                                                Local
                                            </span>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Barcode Scanner -->
            <div class="mb-6">
                <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search by Barcode</label>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <input type="text" id="barcode" wire:model="barcode" 
                           placeholder="Enter barcode or scan..." 
                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <button wire:click="toggleBarcodeScanner" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 ml-2">
                        &nbsp;&nbsp;Scan&nbsp;
                    </button>
                    <!-- <button wire:click="searchByBarcode" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Search
                    </button> -->
                </div>
            </div>
        </div>

        <!-- Add Food Modal -->
        @if($showAddFoodModal && $selectedFoodItem)
            @php
                $modalFood = collect($searchResultsRaw)->first(function ($item) use ($selectedFoodItem) {
                    return ($item['food_id'] ?? $item['id'] ?? null) == $selectedFoodItem;
                });
            @endphp
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add Food Item</h3>
                    <div class="mb-4">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $modalFood['food_name'] ?? 'Unknown' }}</div>
                        @if(isset($modalFood['brand_name']))
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $modalFood['brand_name'] }}</div>
                        @endif
                        @if(isset($modalFood['food_description']))
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $modalFood['food_description'] }}</div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="meal_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meal Type</label>
                            <select id="meal_type" wire:model="selectedMealType" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @foreach($mealTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity (servings)</label>
                            <input type="number" id="quantity" wire:model="quantity" step="0.1" min="0.1" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                            <textarea id="notes" wire:model="notes" rows="2" 
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-2">
                        <button wire:click="$set('showAddFoodModal', false)" 
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button wire:click="addFoodFromFatSecret('{{ $selectedFoodItem }}')" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Add to Log
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit Food Modal -->
        @if($showEditFoodModal && $editingFoodLog)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Food Item</h3>
                    <div class="mb-4">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $editingFoodLog->foodItem->display_name }}</div>
                        @if($editingFoodLog->foodItem->brand)
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $editingFoodLog->foodItem->brand }}</div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="edit_meal_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meal Type</label>
                            <select id="edit_meal_type" wire:model="editingMealType" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @foreach($mealTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity (servings)</label>
                            <input type="number" id="edit_quantity" wire:model="editingQuantity" step="0.1" min="0.1" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label for="edit_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                            <textarea id="edit_notes" wire:model="editingNotes" rows="2" 
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <div>
                            <label for="edit_consumed_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time Consumed</label>
                            <input type="time" id="edit_consumed_time" wire:model="editingConsumedTime" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-2">
                        <button wire:click="cancelEdit" 
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Cancel
                        </button>
                        <button wire:click="updateFoodLog" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Update
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Daily Nutrition Summary -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Daily Nutrition Summary</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($dailyTotals['calories']) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Calories</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($dailyTotals['protein'], 1) }}g</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Protein</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ number_format($dailyTotals['carbohydrates'], 1) }}g</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Carbs</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($dailyTotals['fat'], 1) }}g</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Fat</div>
                </div>
            </div>
        </div>

        <!-- Food Log by Meal Type -->
        <div class="space-y-6">
            @foreach($mealTypes as $mealType => $mealLabel)
                @if(isset($foodLogs[$mealType]) && $foodLogs[$mealType]->count() > 0)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $mealLabel }}</h3>
                        <div class="space-y-3">
                            @foreach($foodLogs[$mealType] as $log)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $log->foodItem->display_name }}</div>
                                            <a href="{{ route('food-items.show', $log->foodItem) }}" 
                                               class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 ml-2">
                                                View Details
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $log->quantity }}x serving
                                            @if($log->notes)
                                                • {{ $log->notes }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $log->consumed_time ? $log->consumed_time->format('H:i') : '' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ number_format($log->getNutritionValues()['calories']) }} cal
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ number_format($log->getNutritionValues()['protein'], 1) }}g protein
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4">
                                        <button wire:click="editFoodLog({{ $log->id }})" 
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Edit item">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="addItemAgain({{ $log->foodItem->id }}, '{{ $mealType }}')" 
                                                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Add another serving">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteFoodLog({{ $log->id }})" 
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                title="Delete item">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Barcode Scanner Modal -->
    @if($showBarcodeScanner)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Scan Barcode</h3>
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Point your camera at a barcode to scan it automatically.
                </div>
                <video id="barcode-scanner" class="w-full h-64 bg-gray-100 dark:bg-gray-700 rounded" autoplay></video>
                <div id="scanner-status" class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
                    Initializing camera...
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button wire:click="toggleBarcodeScanner" 
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        let barcodeScanner = null;
        
        function updateScannerStatus(message) {
            const statusElement = document.getElementById('scanner-status');
            if (statusElement) {
                statusElement.textContent = message;
            }
        }
        
        Livewire.on('toggleBarcodeScanner', (show) => {
            if (show && !barcodeScanner) {
                updateScannerStatus('Loading barcode scanner...');
                
                // Initialize scanner
                barcodeScanner = new BarcodeScanner('barcode-scanner', (barcode) => {
                    updateScannerStatus('Barcode detected: ' + barcode);
                    @this.onBarcodeScanned(barcode);
                });
                
                barcodeScanner.init().then((success) => {
                    if (success) {
                        updateScannerStatus('Starting camera...');
                        barcodeScanner.startScanning().then((scanningStarted) => {
                            if (scanningStarted) {
                                updateScannerStatus('Camera ready - point at a barcode');
                            } else {
                                updateScannerStatus('Failed to start camera. Please check permissions.');
                                console.error('Failed to start barcode scanning');
                            }
                        });
                    } else {
                        updateScannerStatus('Failed to initialize scanner. Please try again.');
                        console.error('Failed to initialize barcode scanner');
                    }
                }).catch((error) => {
                    updateScannerStatus('Error loading scanner. Please try again.');
                    console.error('Barcode scanner initialization error:', error);
                });
            } else if (barcodeScanner) {
                barcodeScanner.destroy();
                barcodeScanner = null;
                updateScannerStatus('');
            }
        });
    });
</script> 