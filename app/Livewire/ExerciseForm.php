<?php

namespace App\Livewire;

use App\Models\Exercise;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.navigation')]
class ExerciseForm extends Component
{
    use WithFileUploads;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string')]
    public $description = '';

    #[Rule('required|string|max:50')]
    public $category = '';

    #[Rule('nullable|string|max:255')]
    public $equipment = '';

    public $exerciseId = null;
    public $isEditing = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category' => 'nullable|string|max:100',
        'equipment' => 'nullable|string|max:100',
    ];

    public function mount($exerciseId = null)
    {
        if (!Auth::user()->can('manage', Exercise::class)) {
            session()->flash('error', 'You are not authorized to manage exercises.');
            return;
        }

        if ($exerciseId) {
            $this->exerciseId = $exerciseId;
            $this->loadExercise();
        }
    }

    public function loadExercise()
    {
        if (!Auth::user()->can('manage', Exercise::class)) {
            session()->flash('error', 'You are not authorized to edit exercises.');
            return redirect()->route('exercises.index');
        }

        $exercise = Exercise::find($this->exerciseId);
        if ($exercise) {
            $this->name = $exercise->name;
            $this->description = $exercise->description;
            $this->category = $exercise->category;
            $this->equipment = $exercise->equipment;
            $this->isEditing = true;
        }
    }

    public function updated($property)
    {
        // Remove debug logging
    }

    public function save()
    {
        if (!Auth::user()->can('manage', Exercise::class)) {
            session()->flash('error', 'You are not authorized to manage exercises.');
            return;
        }

        $this->validate();

        try {
            if ($this->isEditing) {
                if (!Auth::user()->can('update', $this->exercise)) {
                    session()->flash('error', 'You are not authorized to edit exercises.');
                    return;
                }

                $this->exercise->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'category' => $this->category,
                    'equipment' => $this->equipment,
                ]);

                session()->flash('message', 'Exercise updated successfully!');
            } else {
                if (!Auth::user()->can('create', Exercise::class)) {
                    session()->flash('error', 'You are not authorized to manage exercises.');
                    return;
                }

                Exercise::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'category' => $this->category,
                    'equipment' => $this->equipment,
                    'user_id' => auth()->id(),
                ]);

                session()->flash('message', 'Exercise created successfully!');
            }

            $this->redirect(route('workout.exercises'));
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
            Log::error('Error saving exercise: ' . $e->getMessage());
        }
    }

    public function render()
    {
        if (auth()->id() !== 1) {
            return redirect()->route('exercises.index');
        }

        return view('livewire.exercise-form', [
            'categories' => [
                'chest' => 'Chest',
                'back' => 'Back',
                'legs' => 'Legs',
                'shoulders' => 'Shoulders',
                'arms' => 'Arms',
                'cardio' => 'Cardio',
                'full_body' => 'Full Body',
                'core' => 'Core',
                'other' => 'Other',
            ],
            'equipmentOptions' => [
                'barbell' => 'Barbell',
                'dumbbells' => 'Dumbbells',
                'cable_pulley' => 'Cable Pulley',
                'smith_machine' => 'Smith Machine',
                'kettlebell' => 'Kettlebell',
                'weight_plate' => 'Weight Plate',
                'machine' => 'Machine',
                'medicine_ball' => 'Medicine Ball',
                'resistance_bands' => 'Resistance Bands',
                'bodyweight' => 'Bodyweight',
                'bar' => 'Bar',
                'other' => 'Other'
            ]
        ]);
    }
}