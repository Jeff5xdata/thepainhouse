<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\BodyMeasurement;
use Carbon\Carbon;

class TrainerBodyMeasurementTracker extends Component
{
    use WithPagination;

    public $clientId;
    public $client;
    public $chest = '';
    public $waist = '';
    public $hips = '';
    public $biceps = '';
    public $thighs = '';
    public $calves = '';
    public $neck = '';
    public $shoulders = '';
    public $forearms = '';
    public $height = '';
    public $bodyFatPercentage = '';
    public $muscleMass = '';
    public $unit = 'cm';
    public $date;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;
    public $confirmingDelete = false;
    public $deleteId = null;
    public $timeRange = '30';
    public $search = '';

    protected $rules = [
        'chest' => 'nullable|numeric|min:0|max:500',
        'waist' => 'nullable|numeric|min:0|max:500',
        'hips' => 'nullable|numeric|min:0|max:500',
        'biceps' => 'nullable|numeric|min:0|max:200',
        'thighs' => 'nullable|numeric|min:0|max:200',
        'calves' => 'nullable|numeric|min:0|max:200',
        'neck' => 'nullable|numeric|min:0|max:100',
        'shoulders' => 'nullable|numeric|min:0|max:500',
        'forearms' => 'nullable|numeric|min:0|max:200',
        'height' => 'nullable|numeric|min:50|max:300',
        'bodyFatPercentage' => 'nullable|numeric|min:0|max:100',
        'muscleMass' => 'nullable|numeric|min:0|max:1000',
        'unit' => 'required|in:cm,inches',
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
        $measurement = BodyMeasurement::findOrFail($id);
        $this->editingId = $id;
        $this->chest = $measurement->chest;
        $this->waist = $measurement->waist;
        $this->hips = $measurement->hips;
        $this->biceps = $measurement->biceps;
        $this->thighs = $measurement->thighs;
        $this->calves = $measurement->calves;
        $this->neck = $measurement->neck;
        $this->shoulders = $measurement->shoulders;
        $this->forearms = $measurement->forearms;
        $this->height = $measurement->height;
        $this->bodyFatPercentage = $measurement->body_fat_percentage;
        $this->muscleMass = $measurement->muscle_mass;

        $this->date = $measurement->measurement_date->format('Y-m-d');
        $this->notes = $measurement->notes ?? '';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->chest = '';
        $this->waist = '';
        $this->hips = '';
        $this->biceps = '';
        $this->thighs = '';
        $this->calves = '';
        $this->neck = '';
        $this->shoulders = '';
        $this->forearms = '';
        $this->height = '';
        $this->bodyFatPercentage = '';
        $this->muscleMass = '';
        $this->unit = 'cm';
        $this->date = now()->format('Y-m-d');
        $this->notes = '';
        $this->showForm = false;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => $this->client->id,
            'chest' => $this->chest ?: null,
            'waist' => $this->waist ?: null,
            'hips' => $this->hips ?: null,
            'biceps' => $this->biceps ?: null,
            'thighs' => $this->thighs ?: null,
            'calves' => $this->calves ?: null,
            'neck' => $this->neck ?: null,
            'shoulders' => $this->shoulders ?: null,
            'forearms' => $this->forearms ?: null,
            'height' => $this->height ?: null,
            'body_fat_percentage' => $this->bodyFatPercentage ?: null,
            'muscle_mass' => $this->muscleMass ?: null,
            'measurement_date' => $this->date,
            'notes' => $this->notes,
        ];

        // Calculate BMI if height and weight are provided
        if ($this->height) {
            $heightInMeters = $this->height / 100; // Height is stored in cm
            $latestWeight = $this->client->weightMeasurements()->latest()->first();
            if ($latestWeight) {
                $weightInKg = $latestWeight->weight_in_kg;
                $data['bmi'] = $weightInKg / ($heightInMeters * $heightInMeters);
            }
        }

        if ($this->editingId) {
            $measurement = BodyMeasurement::findOrFail($this->editingId);
            $measurement->update($data);
            session()->flash('message', 'Body measurement updated successfully.');
        } else {
            BodyMeasurement::create($data);
            session()->flash('message', 'Body measurement added successfully.');
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
            BodyMeasurement::findOrFail($this->deleteId)->delete();
            session()->flash('message', 'Body measurement deleted successfully.');
            $this->confirmingDelete = false;
            $this->deleteId = null;
        }
    }

    public function getBodyMeasurementStatsProperty()
    {
        if (!$this->client) return [];

        $measurements = $this->client->bodyMeasurements()
            ->when($this->timeRange !== 'all', function ($query) {
                $days = $this->timeRange === '7' ? 7 : ($this->timeRange === '30' ? 30 : 90);
                return $query->where('measurement_date', '>=', now()->subDays($days));
            })
            ->orderBy('measurement_date')
            ->get();

        if ($measurements->isEmpty()) {
            return [
                'total_measurements' => 0,
                'current_bmi' => null,
                'average_bmi' => null,
                'body_fat_trend' => null,
                'muscle_mass_trend' => null,
            ];
        }

        $latestMeasurement = $measurements->last();
        $firstMeasurement = $measurements->first();
        
        $bmiChange = $latestMeasurement->bmi && $firstMeasurement->bmi ? 
            $latestMeasurement->bmi - $firstMeasurement->bmi : null;
        
        $bodyFatChange = $latestMeasurement->body_fat_percentage && $firstMeasurement->body_fat_percentage ? 
            $latestMeasurement->body_fat_percentage - $firstMeasurement->body_fat_percentage : null;
        
        $muscleMassChange = $latestMeasurement->muscle_mass && $firstMeasurement->muscle_mass ? 
            $latestMeasurement->muscle_mass - $firstMeasurement->muscle_mass : null;

        return [
            'total_measurements' => $measurements->count(),
            'current_bmi' => $latestMeasurement->bmi,
            'average_bmi' => $measurements->whereNotNull('bmi')->avg('bmi'),
            'body_fat_trend' => $bodyFatChange,
            'muscle_mass_trend' => $muscleMassChange,
            'bmi_change' => $bmiChange,
        ];
    }

    public function render()
    {
        if (!$this->client) {
            return view('livewire.trainer-body-measurement-tracker', [
                'measurements' => collect(),
                'bodyMeasurementStats' => [],
            ]);
        }

        $measurements = $this->client->bodyMeasurements()
            ->when($this->timeRange !== 'all', function ($query) {
                $days = $this->timeRange === '7' ? 7 : ($this->timeRange === '30' ? 30 : 90);
                return $query->where('measurement_date', '>=', now()->subDays($days));
            })
            ->when($this->search, function ($query) {
                return $query->where('notes', 'like', '%' . $this->search . '%');
            })
            ->orderBy('measurement_date', 'desc')
            ->paginate(10);

        return view('livewire.trainer-body-measurement-tracker', [
            'measurements' => $measurements,
            'bodyMeasurementStats' => $this->bodyMeasurementStats,
        ]);
    }
} 