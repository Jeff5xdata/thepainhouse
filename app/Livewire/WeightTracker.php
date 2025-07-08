<?php

namespace App\Livewire;

use App\Models\WeightMeasurement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class WeightTracker extends Component
{
    use WithPagination;

    public $weight = '';
    public $unit = 'kg';
    public $measurement_date;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;
    public $confirmingDelete = false;
    public $deleteId = null;

    protected $rules = [
        'weight' => 'required|numeric|min:0|max:999.99',
        'unit' => 'required|in:kg,lbs',
        'measurement_date' => 'required|date|before_or_equal:today',
        'notes' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'weight.required' => 'Weight is required.',
        'weight.numeric' => 'Weight must be a number.',
        'weight.min' => 'Weight must be positive.',
        'weight.max' => 'Weight cannot exceed 999.99.',
        'measurement_date.required' => 'Measurement date is required.',
        'measurement_date.date' => 'Please enter a valid date.',
        'measurement_date.before_or_equal' => 'Measurement date cannot be in the future.',
    ];

    public function mount()
    {
        $this->measurement_date = now()->format('Y-m-d');
        $this->unit = auth()->user()->getPreferredWeightUnit();
    }

    public function render()
    {
        $measurements = WeightMeasurement::where('user_id', Auth::id())
            ->orderBy('measurement_date', 'desc')
            ->paginate(10);

        $latestMeasurement = WeightMeasurement::where('user_id', Auth::id())
            ->latest('measurement_date')
            ->first();

        $stats = $this->getStats();

        return view('livewire.weight-tracker', [
            'measurements' => $measurements,
            'latestMeasurement' => $latestMeasurement,
            'stats' => $stats,
        ]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'weight' => $this->weight,
            'unit' => $this->unit,
            'measurement_date' => $this->measurement_date,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            WeightMeasurement::find($this->editingId)->update($data);
            session()->flash('message', 'Weight measurement updated successfully!');
        } else {
            WeightMeasurement::create($data);
            session()->flash('message', 'Weight measurement added successfully!');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function edit($id)
    {
        $measurement = WeightMeasurement::find($id);
        if ($measurement && $measurement->user_id === Auth::id()) {
            $this->editingId = $id;
            $this->weight = $measurement->weight;
            $this->unit = $measurement->unit;
            $this->measurement_date = $measurement->measurement_date->format('Y-m-d');
            $this->notes = $measurement->notes;
            $this->showForm = true;
        }
    }

    public function delete($id)
    {
        $measurement = WeightMeasurement::find($id);
        if ($measurement && $measurement->user_id === Auth::id()) {
            $measurement->delete();
            session()->flash('message', 'Weight measurement deleted successfully!');
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function deleteConfirmed()
    {
        if ($this->deleteId) {
            $this->delete($this->deleteId);
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->weight = '';
        $this->unit = auth()->user()->getPreferredWeightUnit();
        $this->measurement_date = now()->format('Y-m-d');
        $this->notes = '';
    }

    public function showAddForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function hideForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function getStats()
    {
        $measurements = WeightMeasurement::where('user_id', Auth::id())
            ->orderBy('measurement_date', 'asc')
            ->get();

        if ($measurements->isEmpty()) {
            return [
                'total_measurements' => 0,
                'current_weight' => null,
                'starting_weight' => null,
                'weight_change' => null,
                'average_weight' => null,
                'min_weight' => null,
                'max_weight' => null,
            ];
        }

        $userPreferredUnit = auth()->user()->getPreferredWeightUnit();
        
        // Get weights in kg for calculations
        $currentWeightKg = $measurements->last()->weight_in_kg;
        $startingWeightKg = $measurements->first()->weight_in_kg;
        $weightChangeKg = $currentWeightKg - $startingWeightKg;
        $averageWeightKg = $measurements->avg(function ($m) {
            return $m->weight_in_kg;
        });
        $minWeightKg = $measurements->min(function ($m) {
            return $m->weight_in_kg;
        });
        $maxWeightKg = $measurements->max(function ($m) {
            return $m->weight_in_kg;
        });

        // Convert to user's preferred unit
        if ($userPreferredUnit === 'lbs') {
            $currentWeight = $currentWeightKg * 2.20462;
            $startingWeight = $startingWeightKg * 2.20462;
            $weightChange = $weightChangeKg * 2.20462;
            $averageWeight = $averageWeightKg * 2.20462;
            $minWeight = $minWeightKg * 2.20462;
            $maxWeight = $maxWeightKg * 2.20462;
        } else {
            $currentWeight = $currentWeightKg;
            $startingWeight = $startingWeightKg;
            $weightChange = $weightChangeKg;
            $averageWeight = $averageWeightKg;
            $minWeight = $minWeightKg;
            $maxWeight = $maxWeightKg;
        }

        return [
            'total_measurements' => $measurements->count(),
            'current_weight' => $currentWeight,
            'starting_weight' => $startingWeight,
            'weight_change' => $weightChange,
            'average_weight' => $averageWeight,
            'min_weight' => $minWeight,
            'max_weight' => $maxWeight,
        ];
    }
}
