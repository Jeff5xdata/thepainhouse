<?php

namespace App\Livewire;

use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Services\FatSecretApiService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Carbon\Carbon;

class Nutrition extends Component
{
    use WithPagination;

    #[Rule('string|max:100')]
    public $searchQuery = '';
    public $barcode = '';
    public $selectedDate;
    public $selectedMealType = 'snack';
    public $quantity = 1.0;
    public $notes = '';
    public $showBarcodeScanner = false;
    public $showSearchResults = false;
    public $searchResults = [];
    public $selectedFoodItem = null;
    public $showAddFoodModal = false;
    public $showEditFoodModal = false;
    public $viewMode = 'daily'; // 'daily' or 'weekly'
    public $selectedWeekStart;
    public $searchResultsRaw = [];
    public $isSearching = false;
    
    // Edit food log properties
    public $editingFoodLog = null;
    public $editingMealType = 'snack';
    public $editingQuantity = 1.0;
    public $editingNotes = '';
    public $editingConsumedTime = '';

    protected $queryString = ['selectedDate', 'viewMode'];

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedWeekStart = $this->getWeekStartDate($this->selectedDate);
    }

    public function render()
    {
        // Log render call for debugging
        \Log::info('Nutrition component render called', [
            'viewMode' => $this->viewMode,
            'selectedDate' => $this->selectedDate,
            'user_id' => auth()->id()
        ]);
        
        if ($this->viewMode === 'weekly') {
            $weeklyData = $this->getWeeklyData();
            return view('livewire.nutrition', [
                'foodLogs' => $weeklyData['foodLogs'],
                'dailyTotals' => $weeklyData['weeklyTotals'],
                'weeklyBreakdown' => $weeklyData['weeklyBreakdown'],
                'mealTypes' => FoodLog::getMealTypeOptions(),
                'viewMode' => $this->viewMode,
                'weekRange' => $weeklyData['weekRange'],
            ]);
        } else {
            $foodLogs = FoodLog::where('user_id', auth()->id())
                ->where('consumed_date', $this->selectedDate)
                ->with('foodItem')
                ->orderBy('consumed_time', 'desc')
                ->get()
                ->groupBy('meal_type');

            $dailyTotals = $this->calculateDailyTotals($foodLogs);

            return view('livewire.nutrition', [
                'foodLogs' => $foodLogs,
                'dailyTotals' => $dailyTotals,
                'weeklyBreakdown' => null,
                'mealTypes' => FoodLog::getMealTypeOptions(),
                'viewMode' => $this->viewMode,
                'weekRange' => null,
            ]);
        }
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'daily' ? 'weekly' : 'daily';
        if ($this->viewMode === 'weekly') {
            $this->selectedWeekStart = $this->getWeekStartDate($this->selectedDate);
        }
    }

    public function changeWeek($direction)
    {
        $currentWeekStart = Carbon::parse($this->selectedWeekStart);
        
        if ($direction === 'next') {
            $this->selectedWeekStart = $currentWeekStart->addWeek()->format('Y-m-d');
        } else {
            $this->selectedWeekStart = $currentWeekStart->subWeek()->format('Y-m-d');
        }
    }

    private function getWeekStartDate($date)
    {
        return Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    private function getWeeklyData()
    {
        $weekStart = Carbon::parse($this->selectedWeekStart);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        
        $weekRange = [
            'start' => $weekStart->format('M j'),
            'end' => $weekEnd->format('M j, Y'),
            'full_start' => $weekStart->format('Y-m-d'),
            'full_end' => $weekEnd->format('Y-m-d'),
        ];

        // Get all food logs for the week
        $weeklyLogs = FoodLog::where('user_id', auth()->id())
            ->whereBetween('consumed_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->with('foodItem')
            ->orderBy('consumed_date', 'desc')
            ->orderBy('consumed_time', 'desc')
            ->get();



        // Group by date and meal type
        $foodLogs = $weeklyLogs->groupBy('consumed_date')->map(function ($dayLogs) {
            return $dayLogs->groupBy('meal_type');
        });

        // Calculate weekly totals
        $weeklyTotals = $this->calculateWeeklyTotals($weeklyLogs);

        // Calculate daily breakdown
        $weeklyBreakdown = $this->calculateWeeklyBreakdown($weeklyLogs);

        return [
            'foodLogs' => $foodLogs,
            'weeklyTotals' => $weeklyTotals,
            'weeklyBreakdown' => $weeklyBreakdown,
            'weekRange' => $weekRange,
        ];
    }

    private function calculateWeeklyTotals($weeklyLogs)
    {
        $totals = [
            'calories' => 0,
            'protein' => 0,
            'carbohydrates' => 0,
            'fat' => 0,
            'fiber' => 0,
            'sugar' => 0,
            'sodium' => 0,
            'cholesterol' => 0,
        ];

        foreach ($weeklyLogs as $log) {
            $nutrition = $log->getNutritionValues();
            foreach ($totals as $key => $value) {
                $totals[$key] += $nutrition[$key];
            }
        }

        return $totals;
    }

    private function calculateWeeklyBreakdown($weeklyLogs)
    {
        $breakdown = [];
        

        
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::parse($this->selectedWeekStart)->addDays($i)->format('Y-m-d');
            $dayName = Carbon::parse($this->selectedWeekStart)->addDays($i)->format('D');
            
            // Use filter instead of where for better collection filtering
            $dayLogs = $weeklyLogs->filter(function ($log) use ($date) {
                return $log->consumed_date->format('Y-m-d') === $date;
            });
            
            $dayTotals = [
                'calories' => 0,
                'protein' => 0,
                'carbohydrates' => 0,
                'fat' => 0,
            ];

            foreach ($dayLogs as $log) {
                $nutrition = $log->getNutritionValues();
                foreach ($dayTotals as $key => $value) {
                    $dayTotals[$key] += $nutrition[$key];
                }
            }



            $breakdown[$date] = [
                'day_name' => $dayName,
                'date' => $date,
                'totals' => $dayTotals,
                'has_data' => $dayLogs->count() > 0,
            ];
        }

        return $breakdown;
    }

    public function searchFood()
    {
        if (empty($this->searchQuery)) {
            $this->searchResults = [];
            $this->searchResultsRaw = [];
            $this->showSearchResults = false;
            $this->isSearching = false;
            return;
        }

        $this->isSearching = true;

        // First, search the local database for existing food items
        $localResults = \App\Models\FoodItem::where('name', 'like', '%' . $this->searchQuery . '%')
            ->orWhere('brand', 'like', '%' . $this->searchQuery . '%')
            ->limit(5)
            ->get();

        $results = [];
        $this->searchResultsRaw = [];

        // Add local database results first
        foreach ($localResults as $foodItem) {
            $localResult = [
                'food_name' => $foodItem->name,
                'brand_name' => $foodItem->brand,
                'food_id' => $foodItem->fatsecret_id ?: 'local_' . $foodItem->id,
                'food_description' => $foodItem->serving_size ? "Per {$foodItem->serving_size} - Calories: {$foodItem->calories}kcal | Protein: {$foodItem->protein}g | Carbs: {$foodItem->carbohydrates}g | Fat: {$foodItem->fat}g" : '',
                'is_local' => true,
                'local_id' => $foodItem->id,
            ];
            
            $results[] = $localResult;
            $this->searchResultsRaw[] = $localResult;
        }

        // If we have less than 5 results, search the API for more
        if (count($results) < 5) {
            $fatSecretService = app(FatSecretApiService::class);
            $apiResults = $fatSecretService->searchByName($this->searchQuery);
            
            // Filter out API results that we already have locally
            foreach ($apiResults as $apiResult) {
                $fatsecretId = $apiResult['food_id'] ?? null;
                $alreadyExists = $localResults->where('fatsecret_id', $fatsecretId)->count() > 0;
                
                if (!$alreadyExists && count($results) < 10) {
                    $apiResult['is_local'] = false;
                    $results[] = $apiResult;
                    $this->searchResultsRaw[] = $apiResult;
                }
            }
        }

        \Log::info('Combined search results', [
            'query' => $this->searchQuery,
            'local_count' => $localResults->count(),
            'total_count' => count($results)
        ]);

        // Map results to expected keys for UI
        $this->searchResults = array_map(function ($item) {
            return [
                'name' => $item['food_name'] ?? 'Unknown',
                'brand' => $item['brand_name'] ?? '',
                'id' => $item['food_id'] ?? '',
                'description' => $item['food_description'] ?? '',
                'is_local' => $item['is_local'] ?? false,
                'local_id' => $item['local_id'] ?? null,
            ];
        }, $results);
        
        $this->showSearchResults = true;
        $this->isSearching = false;
    }

    public function updatedSearchQuery()
    {
        // Only search if query is at least 2 characters long
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            $this->isSearching = false;
            return;
        }

        // The debounce will handle the delay, so we can call searchFood directly
        $this->searchFood();
    }

    public function searchByBarcode()
    {
        if (empty($this->barcode)) {
            return;
        }

        // First, check if we have this barcode in our local database
        $localFoodItem = \App\Models\FoodItem::where('barcode', $this->barcode)->first();
        
        if ($localFoodItem) {
            \Log::info('Found barcode in local database', ['barcode' => $this->barcode, 'food_item' => $localFoodItem->name]);
            $this->addFoodToLog($localFoodItem->id);
            session()->flash('success', 'Food item found in database: ' . $localFoodItem->name);
            return;
        }

        // If not found locally, search the API
        $fatSecretService = app(FatSecretApiService::class);
        $foodData = $fatSecretService->searchByBarcode($this->barcode);

        if ($foodData) {
            // Process the food data directly for barcode searches
            $this->processBarcodeFoodData($foodData);
        } else {
            session()->flash('error', 'Food item not found for this barcode.');
        }
    }

    public function selectFoodItem($foodId)
    {
        \Log::info('selectFoodItem called', ['foodId' => $foodId]);
        $this->selectedFoodItem = $foodId;
        $this->showAddFoodModal = true;
        $this->showSearchResults = false;
    }

    public function processBarcodeFoodData($foodData)
    {
        \Log::info('processBarcodeFoodData called', [
            'foodData' => $foodData,
            'foodData_type' => gettype($foodData),
            'foodData_keys' => is_array($foodData) ? array_keys($foodData) : 'not_array'
        ]);
        
        if (empty($foodData) || !is_array($foodData)) {
            \Log::warning('processBarcodeFoodData called with empty or invalid data', ['data' => $foodData]);
            session()->flash('error', 'No food data received from barcode search.');
            return;
        }

        // The foodData from barcode search should already contain the full food details
        // since searchByBarcode() calls getFoodDetails() internally
        $fatSecretService = app(\App\Services\FatSecretApiService::class);
        $nutritionData = $fatSecretService->parseNutritionData($foodData);
        \Log::info('Parsed nutrition data from barcode search', ['nutritionData' => $nutritionData]);
        
        // Validate required fields
        if (empty($nutritionData['name'])) {
            \Log::error('Nutrition data missing required name field', ['nutritionData' => $nutritionData]);
            session()->flash('error', 'Invalid nutrition data: missing food name.');
            return;
        }

        // Check if food item already exists
        $foodItem = \App\Models\FoodItem::where('fatsecret_id', $nutritionData['fatsecret_id'])
            ->orWhere(function($query) use ($nutritionData) {
                if (!empty($nutritionData['barcode'])) {
                    $query->where('barcode', $nutritionData['barcode']);
                }
            })
            ->first();

        if (!$foodItem) {
            \Log::info('Attempting to create FoodItem from barcode search', ['data' => $nutritionData]);
            try {
                $foodItem = \App\Models\FoodItem::create($nutritionData);
                \Log::info('Created FoodItem from barcode search', ['foodItem' => $foodItem]);
            } catch (\Exception $e) {
                \Log::error('FoodItem insert failed from barcode search', ['error' => $e->getMessage(), 'data' => $nutritionData]);
                session()->flash('error', 'Failed to save food item: ' . $e->getMessage());
                return;
            }
        } else {
            \Log::info('FoodItem already exists from barcode search', ['foodItem' => $foodItem]);
        }

        $this->addFoodToLog($foodItem->id);
    }

    public function addFoodFromFatSecret($foodId)
    {
        \Log::info('addFoodFromFatSecret called', ['foodId' => $foodId]);
        \Log::info('searchResultsRaw at add', ['results' => $this->searchResultsRaw]);
        
        // Find the raw data for this foodId
        $fatSecretData = collect($this->searchResultsRaw)->first(function ($item) use ($foodId) {
            return ($item['food_id'] ?? $item['id'] ?? null) == $foodId;
        });
        \Log::info('fatSecretData found', ['data' => $fatSecretData]);

        if (empty($fatSecretData) || !is_array($fatSecretData)) {
            \Log::warning('addFoodFromFatSecret called with empty or invalid data', ['data' => $fatSecretData]);
            session()->flash('error', 'No food data selected.');
            return;
        }

        // Check if this is a local food item
        if (isset($fatSecretData['is_local']) && $fatSecretData['is_local']) {
            \Log::info('Using local food item', ['local_id' => $fatSecretData['local_id']]);
            $foodItem = \App\Models\FoodItem::find($fatSecretData['local_id']);
            if (!$foodItem) {
                session()->flash('error', 'Local food item not found.');
                return;
            }
        } else {
            // This is an API food item, fetch full details and create/update
            $fatSecretService = app(\App\Services\FatSecretApiService::class);

            // Fetch full food details using food_id
            $fullData = $fatSecretService->getFoodDetails($foodId);
            \Log::info('Full food details fetched', ['fullData' => $fullData]);
            if (!$fullData) {
                session()->flash('error', 'Could not fetch full nutrition data for this food.');
                return;
            }

            $nutritionData = $fatSecretService->parseNutritionData($fullData);
            \Log::info('Parsed nutrition data', ['nutritionData' => $nutritionData]);
            
            // Validate required fields
            if (empty($nutritionData['name'])) {
                \Log::error('Nutrition data missing required name field', ['nutritionData' => $nutritionData]);
                session()->flash('error', 'Invalid nutrition data: missing food name.');
                return;
            }

            // Check if food item already exists
            $foodItem = \App\Models\FoodItem::where('fatsecret_id', $nutritionData['fatsecret_id'])
                ->orWhere(function($query) use ($nutritionData) {
                    if (!empty($nutritionData['barcode'])) {
                        $query->where('barcode', $nutritionData['barcode']);
                    }
                })
                ->first();

            if (!$foodItem) {
                \Log::info('Attempting to create FoodItem', ['data' => $nutritionData]);
                try {
                    $foodItem = \App\Models\FoodItem::create($nutritionData);
                    \Log::info('Created FoodItem', ['foodItem' => $foodItem]);
                } catch (\Exception $e) {
                    \Log::error('FoodItem insert failed', ['error' => $e->getMessage(), 'data' => $nutritionData]);
                    session()->flash('error', 'Failed to save food item: ' . $e->getMessage());
                    return;
                }
            } else {
                \Log::info('FoodItem already exists', ['foodItem' => $foodItem]);
            }
        }

        $this->addFoodToLog($foodItem->id);
    }

    public function addFoodToLog($foodItemId)
    {
        FoodLog::create([
            'user_id' => auth()->id(),
            'food_item_id' => $foodItemId,
            'meal_type' => $this->selectedMealType,
            'quantity' => $this->quantity,
            'consumed_date' => $this->selectedDate,
            'consumed_time' => now(),
            'notes' => $this->notes,
        ]);

        $this->reset(['quantity', 'notes', 'selectedFoodItem', 'showAddFoodModal']);
        session()->flash('success', 'Food item added to your log.');
        
        // Refresh the page after successful save
        $this->dispatch('refresh-page');
    }

    public function deleteFoodLog($logId)
    {
        $foodLog = FoodLog::where('user_id', auth()->id())->findOrFail($logId);
        $foodLog->delete();
        session()->flash('success', 'Food item removed from your log.');
        
        // Refresh the page after successful delete
        $this->dispatch('refresh-page');
    }

    public function addItemAgain($foodItemId, $mealType)
    {
        // Find the food item
        $foodItem = FoodItem::findOrFail($foodItemId);
        
        // Create a new food log entry with the same food item and meal type
        FoodLog::create([
            'user_id' => auth()->id(),
            'food_item_id' => $foodItemId,
            'meal_type' => $mealType,
            'quantity' => 1.0, // Default to 1 serving
            'consumed_date' => $this->selectedDate,
            'consumed_time' => now(),
            'notes' => '', // No notes for quick add
        ]);

        session()->flash('success', 'Added another serving of ' . $foodItem->display_name);
        
        // Refresh the page after successful save
        $this->dispatch('refresh-page');
    }

    public function editFoodLog($logId)
    {
        $this->editingFoodLog = FoodLog::where('user_id', auth()->id())
            ->with('foodItem')
            ->findOrFail($logId);
        
        $this->editingMealType = $this->editingFoodLog->meal_type;
        $this->editingQuantity = $this->editingFoodLog->quantity;
        $this->editingNotes = $this->editingFoodLog->notes ?? '';
        $this->editingConsumedTime = $this->editingFoodLog->consumed_time ? $this->editingFoodLog->consumed_time->format('H:i') : '';
        
        $this->showEditFoodModal = true;
    }

    public function updateFoodLog()
    {
        $this->validate([
            'editingMealType' => 'required|string',
            'editingQuantity' => 'required|numeric|min:0.1',
            'editingNotes' => 'nullable|string|max:500',
            'editingConsumedTime' => 'nullable|date_format:H:i',
        ]);

        $this->editingFoodLog->update([
            'meal_type' => $this->editingMealType,
            'quantity' => $this->editingQuantity,
            'notes' => $this->editingNotes,
            'consumed_time' => $this->editingConsumedTime ? $this->selectedDate . ' ' . $this->editingConsumedTime : null,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Food item updated successfully.');
        
        // Refresh the page after successful update
        $this->dispatch('refresh-page');
    }

    public function cancelEdit()
    {
        $this->showEditFoodModal = false;
        $this->editingFoodLog = null;
        $this->editingMealType = 'snack';
        $this->editingQuantity = 1.0;
        $this->editingNotes = '';
        $this->editingConsumedTime = '';
    }

    public function toggleBarcodeScanner()
    {
        $this->showBarcodeScanner = !$this->showBarcodeScanner;
        $this->dispatch('toggleBarcodeScanner', $this->showBarcodeScanner);
    }

    public function onBarcodeScanned($barcode)
    {
        $this->barcode = $barcode;
        $this->showBarcodeScanner = false;
        $this->searchByBarcode();
    }

    public function testBarcodeScanner()
    {
        // Test with a sample barcode
        $this->barcode = '049000006000'; // Sample barcode
        $this->searchByBarcode();
        session()->flash('success', 'Test barcode processed: ' . $this->barcode);
        
        // Refresh the page after successful test
        $this->dispatch('refresh-page');
    }

    public function changeDate($date)
    {
        $this->selectedDate = $date;
    }

    public function updatedSelectedDate()
    {
        // Clear search results when date changes
        $this->searchResults = [];
        $this->searchResultsRaw = [];
        $this->showSearchResults = false;
        $this->searchQuery = '';
        
        // Log the date change for debugging
        \Log::info('Date changed in Nutrition component', [
            'new_date' => $this->selectedDate,
            'user_id' => auth()->id()
        ]);
    }

    private function calculateDailyTotals($foodLogs)
    {
        $totals = [
            'calories' => 0,
            'protein' => 0,
            'carbohydrates' => 0,
            'fat' => 0,
            'fiber' => 0,
            'sugar' => 0,
            'sodium' => 0,
            'cholesterol' => 0,
        ];

        foreach ($foodLogs as $mealLogs) {
            foreach ($mealLogs as $log) {
                $nutrition = $log->getNutritionValues();
                foreach ($totals as $key => $value) {
                    $totals[$key] += $nutrition[$key];
                }
            }
        }

        return $totals;
    }
} 