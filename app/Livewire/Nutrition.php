<?php

namespace App\Livewire;

use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Services\ChompApiService;
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
    public $viewMode = 'daily'; // 'daily' or 'weekly'
    public $selectedWeekStart;

    protected $queryString = ['selectedDate', 'viewMode'];

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedWeekStart = $this->getWeekStartDate($this->selectedDate);
    }

    public function render()
    {
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
            
            $dayLogs = $weeklyLogs->where('consumed_date', $date);
            
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
            $this->showSearchResults = false;
            return;
        }

        $chompService = app(ChompApiService::class);
        $this->searchResults = $chompService->searchByName($this->searchQuery);
        $this->showSearchResults = true;
    }

    public function updatedSearchQuery()
    {
        // Only search if query is at least 2 characters long
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            return;
        }

        // Add a small delay to prevent rapid API calls
        // This works in conjunction with the server-side delay in ChompApiService
        $this->searchFood();
    }

    public function searchByBarcode()
    {
        if (empty($this->barcode)) {
            return;
        }

        $chompService = app(ChompApiService::class);
        $foodData = $chompService->searchByBarcode($this->barcode);

        if ($foodData) {
            $this->addFoodFromChomp($foodData);
        } else {
            session()->flash('error', 'Food item not found for this barcode.');
        }
    }

    public function selectFoodItem($chompData)
    {
        $this->selectedFoodItem = $chompData;
        $this->showAddFoodModal = true;
        $this->showSearchResults = false;
    }

    public function addFoodFromChomp($chompData)
    {
        $chompService = app(ChompApiService::class);
        $nutritionData = $chompService->parseNutritionData($chompData);

        // Check if food item already exists
        $foodItem = FoodItem::where('chomp_id', $nutritionData['chomp_id'])
            ->orWhere('barcode', $nutritionData['barcode'])
            ->first();

        if (!$foodItem) {
            $foodItem = FoodItem::create($nutritionData);
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
    }

    public function deleteFoodLog($logId)
    {
        $foodLog = FoodLog::where('user_id', auth()->id())->findOrFail($logId);
        $foodLog->delete();
        session()->flash('success', 'Food item removed from your log.');
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
    }

    public function changeDate($date)
    {
        $this->selectedDate = $date;
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