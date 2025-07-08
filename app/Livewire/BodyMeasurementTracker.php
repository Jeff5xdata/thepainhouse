<?php

namespace App\Livewire;

use App\Models\BodyMeasurement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class BodyMeasurementTracker extends Component
{
    use WithPagination;

    public $measurement_date;
    public $chest = '';
    public $waist = '';
    public $hips = '';
    public $biceps = '';
    public $forearms = '';
    public $thighs = '';
    public $calves = '';
    public $neck = '';
    public $shoulders = '';
    public $body_fat_percentage = '';
    public $muscle_mass = '';
    public $height = '';
    public $notes = '';
    
    public $editingId = null;
    public $showForm = false;
    public $confirmingDelete = false;
    public $deleteId = null;

    protected $rules = [
        'measurement_date' => 'required|date|before_or_equal:today',
        'chest' => 'nullable|numeric|min:0|max:999.9',
        'waist' => 'nullable|numeric|min:0|max:999.9',
        'hips' => 'nullable|numeric|min:0|max:999.9',
        'biceps' => 'nullable|numeric|min:0|max:999.9',
        'forearms' => 'nullable|numeric|min:0|max:999.9',
        'thighs' => 'nullable|numeric|min:0|max:999.9',
        'calves' => 'nullable|numeric|min:0|max:999.9',
        'neck' => 'nullable|numeric|min:0|max:999.9',
        'shoulders' => 'nullable|numeric|min:0|max:999.9',
        'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
        'muscle_mass' => 'nullable|numeric|min:0|max:999.9',
        'height' => 'nullable|numeric|min:0|max:999.9',
        'notes' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'measurement_date.required' => 'Measurement date is required.',
        'measurement_date.date' => 'Please enter a valid date.',
        'measurement_date.before_or_equal' => 'Measurement date cannot be in the future.',
        'chest.numeric' => 'Chest measurement must be a number.',
        'waist.numeric' => 'Waist measurement must be a number.',
        'hips.numeric' => 'Hips measurement must be a number.',
        'biceps.numeric' => 'Biceps measurement must be a number.',
        'forearms.numeric' => 'Forearms measurement must be a number.',
        'thighs.numeric' => 'Thighs measurement must be a number.',
        'calves.numeric' => 'Calves measurement must be a number.',
        'neck.numeric' => 'Neck measurement must be a number.',
        'shoulders.numeric' => 'Shoulders measurement must be a number.',
        'body_fat_percentage.numeric' => 'Body fat percentage must be a number.',
        'body_fat_percentage.min' => 'Body fat percentage must be positive.',
        'body_fat_percentage.max' => 'Body fat percentage cannot exceed 100%.',
        'muscle_mass.numeric' => 'Muscle mass must be a number.',
        'height.numeric' => 'Height must be a number.',
    ];

    public function mount()
    {
        $this->measurement_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $measurements = BodyMeasurement::where('user_id', Auth::id())
            ->orderBy('measurement_date', 'desc')
            ->paginate(10);

        $latestMeasurement = BodyMeasurement::where('user_id', Auth::id())
            ->latest('measurement_date')
            ->first();

        $stats = $this->getStats();

        return view('livewire.body-measurement-tracker', [
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
            'measurement_date' => $this->measurement_date,
            'chest' => $this->chest ?: null,
            'waist' => $this->waist ?: null,
            'hips' => $this->hips ?: null,
            'biceps' => $this->biceps ?: null,
            'forearms' => $this->forearms ?: null,
            'thighs' => $this->thighs ?: null,
            'calves' => $this->calves ?: null,
            'neck' => $this->neck ?: null,
            'shoulders' => $this->shoulders ?: null,
            'body_fat_percentage' => $this->body_fat_percentage ?: null,
            'muscle_mass' => $this->muscle_mass ?: null,
            'height' => $this->height ?: null,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            BodyMeasurement::find($this->editingId)->update($data);
            session()->flash('message', 'Body measurement updated successfully!');
        } else {
            BodyMeasurement::create($data);
            session()->flash('message', 'Body measurement added successfully!');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function edit($id)
    {
        $measurement = BodyMeasurement::find($id);
        if ($measurement && $measurement->user_id === Auth::id()) {
            $this->editingId = $id;
            $this->measurement_date = $measurement->measurement_date->format('Y-m-d');
            $this->chest = $measurement->chest;
            $this->waist = $measurement->waist;
            $this->hips = $measurement->hips;
            $this->biceps = $measurement->biceps;
            $this->forearms = $measurement->forearms;
            $this->thighs = $measurement->thighs;
            $this->calves = $measurement->calves;
            $this->neck = $measurement->neck;
            $this->shoulders = $measurement->shoulders;
            $this->body_fat_percentage = $measurement->body_fat_percentage;
            $this->muscle_mass = $measurement->muscle_mass;
            $this->height = $measurement->height;
            $this->notes = $measurement->notes;
            $this->showForm = true;
        }
    }

    public function delete($id)
    {
        $measurement = BodyMeasurement::find($id);
        if ($measurement && $measurement->user_id === Auth::id()) {
            $measurement->delete();
            session()->flash('message', 'Body measurement deleted successfully!');
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
        $this->measurement_date = now()->format('Y-m-d');
        $this->chest = '';
        $this->waist = '';
        $this->hips = '';
        $this->biceps = '';
        $this->forearms = '';
        $this->thighs = '';
        $this->calves = '';
        $this->neck = '';
        $this->shoulders = '';
        $this->body_fat_percentage = '';
        $this->muscle_mass = '';
        $this->height = '';
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
        $measurements = BodyMeasurement::where('user_id', Auth::id())
            ->orderBy('measurement_date', 'asc')
            ->get();

        if ($measurements->isEmpty()) {
            return [
                'total_measurements' => 0,
                'latest_measurement' => null,
                'bmi' => null,
                'body_fat_trend' => null,
            ];
        }

        $latestMeasurement = $measurements->last();
        $bmi = $latestMeasurement->bmi;
        
        // Calculate body fat trend (last 3 measurements)
        $recentMeasurements = $measurements->take(-3);
        $bodyFatTrend = null;
        if ($recentMeasurements->count() >= 2) {
            $first = $recentMeasurements->first()->body_fat_percentage;
            $last = $recentMeasurements->last()->body_fat_percentage;
            if ($first && $last) {
                $bodyFatTrend = $last - $first;
            }
        }

        return [
            'total_measurements' => $measurements->count(),
            'latest_measurement' => $latestMeasurement,
            'bmi' => $bmi,
            'body_fat_trend' => $bodyFatTrend,
        ];
    }
}
