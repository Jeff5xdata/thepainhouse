<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Food Tracker</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Track your daily nutrition and scan barcodes to find food information</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Date Selector -->
    <div class="mb-6">
        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Date</label>
        <input type="date" id="date" wire:model.live="selectedDate" 
               class="block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>

    <!-- Add Food Section -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Add Food Item</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Search by Name -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search by Name</label>
                <div class="relative">
                    <input type="text" id="search" wire:model.live.debounce.300ms="searchQuery" 
                           placeholder="Search for food items..." 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <!-- Search Button -->
                <button wire:click="searchFood" 
                        class="mt-2 w-1/2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    Search Food
                </button>
                
                <!-- Search Results -->
                @if($showSearchResults && count($searchResults) > 0)
                    <div class="mt-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto">
                        @foreach($searchResults as $result)
                            <div wire:click="selectFoodItem({{ json_encode($result) }})" 
                                 class="p-3 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $result['name'] ?? 'Unknown' }}</div>
                                @if(isset($result['brand']))
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $result['brand'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Barcode Scanner -->
            <div>
                <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Scan Barcode</label>
                <div class="flex space-x-2">
                    <input type="text" id="barcode" wire:model="barcode" 
                           placeholder="Enter barcode or scan" 
                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <button wire:click="toggleBarcodeScanner" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                    </button>
                    <button wire:click="searchByBarcode" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Search
                    </button>
                </div>
            </div>
        </div>

        <!-- Barcode Scanner Modal -->
        @if($showBarcodeScanner)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Scan Barcode</h3>
                    <div id="barcode-scanner" class="w-full h-64 bg-gray-100 dark:bg-gray-700 rounded"></div>
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

    <!-- Add Food Modal -->
    @if($showAddFoodModal && $selectedFoodItem)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add Food Item</h3>
                
                <div class="mb-4">
                    <div class="font-medium text-gray-900 dark:text-white">{{ $selectedFoodItem['name'] ?? 'Unknown' }}</div>
                    @if(isset($selectedFoodItem['brand']))
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedFoodItem['brand'] }}</div>
                    @endif
                    @if(isset($selectedFoodItem['nutrition']['serving_size']))
                        <div class="text-sm text-gray-600 dark:text-gray-400">Serving: {{ $selectedFoodItem['nutrition']['serving_size'] }}</div>
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
                    <button wire:click="addFoodFromChomp({{ json_encode($selectedFoodItem) }})" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Add to Log
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
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $log->foodItem->display_name }}</div>
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
                                <button wire:click="deleteFoodLog({{ $log->id }})" 
                                        class="ml-4 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        let barcodeScanner = null;
        
        Livewire.on('toggleBarcodeScanner', (show) => {
            if (show && !barcodeScanner) {
                barcodeScanner = new BarcodeScanner('barcode-scanner', (barcode) => {
                    @this.onBarcodeScanned(barcode);
                });
                barcodeScanner.init().then(() => {
                    barcodeScanner.startScanning();
                });
            } else if (barcodeScanner) {
                barcodeScanner.destroy();
                barcodeScanner = null;
            }
        });
    });
</script> 