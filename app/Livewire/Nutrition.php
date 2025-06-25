<?php

namespace App\Livewire;

use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Services\ChompApiService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Nutrition extends Component
{
    use WithPagination;

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

    protected $queryString = ['selectedDate'];

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function render()
    {
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
            'mealTypes' => FoodLog::getMealTypeOptions(),
        ]);
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