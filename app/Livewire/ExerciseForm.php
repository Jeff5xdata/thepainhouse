<?php

namespace App\Livewire;

use App\Models\Exercise;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
class ExerciseForm extends Component
{
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

    public function mount($exerciseId = null)
    {
        if (auth()->id() !== 1) {
            session()->flash('error', 'You are not authorized to manage exercises.');
            return redirect()->route('exercises.index');
        }

        if ($exerciseId) {
            $this->exerciseId = $exerciseId;
            $this->loadExercise();
        }
    }

    public function loadExercise()
    {
        if (auth()->id() !== 1) {
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
        if (auth()->id() !== 1) {
            session()->flash('error', 'You are not authorized to manage exercises.');
            return redirect()->route('exercises.index');
        }

        try {
            $validated = $this->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|string|max:50',
                'equipment' => 'nullable|string|max:255'
            ]);

            if ($this->isEditing) {
                $exercise = Exercise::find($this->exerciseId);
                if (!$exercise) {
                    session()->flash('error', 'Exercise not found for editing');
                    return;
                }

                $exercise->update($validated);
                session()->flash('message', 'Exercise updated successfully!');
            } else {
                $exercise = Exercise::create($validated);
                session()->flash('message', 'Exercise created successfully!');
            }

            // Reset form fields
            $this->reset(['name', 'description', 'category', 'equipment']);
            
            // Redirect to the exercises list
            return redirect()->route('exercises.index');

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