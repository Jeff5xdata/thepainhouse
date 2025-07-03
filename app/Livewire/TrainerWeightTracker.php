<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\WeightMeasurement;
use Carbon\Carbon;

class TrainerWeightTracker extends Component
{
    use WithPagination;

    public $clientId;
    public $client;
    public $weight = '';
    public $unit = 'kg';
    public $date;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;
    public $confirmingDelete = false;
    public $deleteId = null;
    public $timeRange = '30';
    public $search = '';

    protected $rules = [
        'weight' => 'required|numeric|min:0|max:1000',
        'unit' => 'required|in:kg,lbs',
        'date' => 'required|date|before_or_equal:today',
        'notes' => 'nullable|string|max:500',
    ];

    public function mount($clientId = null)
    {
        $this->clientId = $clientId;
        $this->date = now()->format('Y-m-d');
        
        // Check if user is a trainer
        if (!auth()->user()->isTrainer()) {
            abort(403, 'Access denied. Only trainers can view client data.');
        }
        
        if ($this->clientId) {
            $this->client = User::findOrFail($this->clientId);
            
            // Check if the client belongs to this trainer
            if (!$this->client->trainer || $this->client->trainer->id !== auth()->id()) {
                abort(403, 'Access denied. You can only view your own clients.');
            }
        }
    }

    public function updatedTimeRange()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function showAddForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function showEditForm($id)
    {
        $measurement = WeightMeasurement::findOrFail($id);
        $this->editingId = $id;
        $this->weight = $measurement->weight;
        $this->unit = $measurement->unit;
        $this->date = $measurement->measurement_date->format('Y-m-d');
        $this->notes = $measurement->notes ?? '';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->weight = '';
        $this->unit = 'kg';
        $this->date = now()->format('Y-m-d');
        $this->notes = '';
        $this->showForm = false;
    }

    public function save()
    {
        $this->validate();

        $weightInKg = $this->unit === 'lbs' ? $this->weight * 0.453592 : $this->weight;

        if ($this->editingId) {
            $measurement = WeightMeasurement::findOrFail($this->editingId);
            $measurement->update([
                'user_id' => $this->client->id,
                'weight' => $this->weight,
                'unit' => $this->unit,
                'measurement_date' => $this->date,
                'notes' => $this->notes,
            ]);
            session()->flash('message', 'Weight measurement updated successfully.');
        } else {
            WeightMeasurement::create([
                'user_id' => $this->client->id,
                'weight' => $this->weight,
                'unit' => $this->unit,
                'measurement_date' => $this->date,
                'notes' => $this->notes,
            ]);
            session()->flash('message', 'Weight measurement added successfully.');
        }

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            WeightMeasurement::findOrFail($this->deleteId)->delete();
            session()->flash('message', 'Weight measurement deleted successfully.');
            $this->confirmingDelete = false;
            $this->deleteId = null;
        }
    }

    public function getWeightStatsProperty()
    {
        if (!$this->client) return [];

        $measurements = $this->client->weightMeasurements()
            ->when($this->timeRange !== 'all', function ($query) {
                $days = $this->timeRange === '7' ? 7 : ($this->timeRange === '30' ? 30 : 90);
                return $query->where('measurement_date', '>=', now()->subDays($days));
            })
            ->orderBy('measurement_date')
            ->get();

        if ($measurements->isEmpty()) {
            return [
                'total_measurements' => 0,
                'current_weight' => null,
                'starting_weight' => null,
                'total_change' => null,
                'average_weight' => null,
                'min_weight' => null,
                'max_weight' => null,
            ];
        }

        $currentWeight = $measurements->last()->weight_in_kg;
        $startingWeight = $measurements->first()->weight_in_kg;
        $totalChange = $currentWeight - $startingWeight;
        $averageWeight = $measurements->avg('weight_in_kg');
        $minWeight = $measurements->min('weight_in_kg');
        $maxWeight = $measurements->max('weight_in_kg');

        return [
            'total_measurements' => $measurements->count(),
            'current_weight' => $currentWeight,
            'starting_weight' => $startingWeight,
            'total_change' => $totalChange,
            'average_weight' => $averageWeight,
            'min_weight' => $minWeight,
            'max_weight' => $maxWeight,
        ];
    }

    public function render()
    {
        if (!$this->client) {
            return view('livewire.trainer-weight-tracker', [
                'measurements' => collect(),
                'weightStats' => [],
            ]);
        }

        $measurements = $this->client->weightMeasurements()
            ->when($this->timeRange !== 'all', function ($query) {
                $days = $this->timeRange === '7' ? 7 : ($this->timeRange === '30' ? 30 : 90);
                return $query->where('measurement_date', '>=', now()->subDays($days));
            })
            ->when($this->search, function ($query) {
                return $query->where('notes', 'like', '%' . $this->search . '%');
            })
            ->orderBy('measurement_date', 'desc')
            ->paginate(10);

        return view('livewire.trainer-weight-tracker', [
            'measurements' => $measurements,
            'weightStats' => $this->weightStats,
        ]);
    }
} 