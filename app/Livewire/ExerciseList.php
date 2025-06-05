<?php

namespace App\Livewire;

use App\Models\Exercise;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class ExerciseList extends Component
{
    use WithPagination;

    public $showDeleteModal = false;
    public $showEditModal = false;
    public $exerciseToDelete = null;
    public $exerciseToEdit = null;
    public $search = '';

    // Edit form properties
    public $editForm = [
        'name' => '',
        'description' => '',
        'category' => '',
        'equipment' => ''
    ];

    protected $listeners = ['exerciseCreated' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($exerciseId)
    {
        if (auth()->id() !== 1) {
            session()->flash('error', 'You are not authorized to delete exercises.');
            return;
        }

        $this->exerciseToDelete = Exercise::find($exerciseId);
        $this->showDeleteModal = true;
    }

    public function deleteExercise()
    {
        if (auth()->id() !== 1) {
            session()->flash('error', 'You are not authorized to delete exercises.');
            return;
        }

        if ($this->exerciseToDelete) {
            $this->exerciseToDelete->delete();
            $this->showDeleteModal = false;
            $this->exerciseToDelete = null;
            session()->flash('message', 'Exercise deleted successfully.');
        }
    }

    public function editExercise($exerciseId)
    {
        if (auth()->id() !== 1) {
            session()->flash('error', 'You are not authorized to edit exercises.');
            return;
        }

        $this->exerciseToEdit = Exercise::find($exerciseId);
        if ($this->exerciseToEdit) {
            $this->editForm = [
                'name' => $this->exerciseToEdit->name,
                'description' => $this->exerciseToEdit->description,
                'category' => $this->exerciseToEdit->category,
                'equipment' => $this->exerciseToEdit->equipment
            ];
            $this->showEditModal = true;
        }
    }

    public function updateExercise()
    {
        if (auth()->id() !== 1) {
            session()->flash('error', 'You are not authorized to edit exercises.');
            return;
        }

        $this->validate([
            'editForm.name' => 'required|string|max:255',
            'editForm.description' => 'nullable|string',
            'editForm.category' => 'required|string|max:50',
            'editForm.equipment' => 'nullable|string|max:255'
        ]);

        if ($this->exerciseToEdit) {
            $this->exerciseToEdit->update([
                'name' => $this->editForm['name'],
                'description' => $this->editForm['description'],
                'category' => $this->editForm['category'],
                'equipment' => $this->editForm['equipment']
            ]);

            $this->showEditModal = false;
            $this->exerciseToEdit = null;
            $this->editForm = [
                'name' => '',
                'description' => '',
                'category' => '',
                'equipment' => ''
            ];
            session()->flash('message', 'Exercise updated successfully.');
        }
    }

    public function render()
    {
        $exercises = Exercise::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('category', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.exercise-list', [
            'exercises' => $exercises,
            'categories' => [
                'chest' => 'Chest',
                'back' => 'Back',
                'legs' => 'Legs',
                'shoulders' => 'Shoulders',
                'arms' => 'Arms',
                'core' => 'Core',
                'cardio' => 'Cardio',
                'other' => 'Other',
            ],
            'canManageExercises' => auth()->id() === 1,
        ]);
    }
} 