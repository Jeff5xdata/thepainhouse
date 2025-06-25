<?php

namespace App\Livewire;

use App\Models\Exercise;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class ExerciseList extends Component
{
    use WithPagination;

    public $showDeleteModal = false;
    public $showEditModal = false;
    public $exerciseToDelete = null;
    public $exerciseToEdit = null;
    public $search = '';
    public $category = '';
    public $equipment = '';
    public $editingExercise = null;
    public $editingName = '';
    public $editingDescription = '';
    public $editingCategory = '';
    public $editingEquipment = '';

    // Edit form properties
    public $editForm = [
        'name' => '',
        'description' => '',
        'category' => '',
        'equipment' => ''
    ];

    protected $listeners = ['exerciseCreated' => '$refresh'];

    protected $rules = [
        'editingName' => 'required|string|max:255',
        'editingDescription' => 'nullable|string',
        'editingCategory' => 'nullable|string|max:100',
        'editingEquipment' => 'nullable|string|max:100',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($exerciseId)
    {
        if (!Auth::user()->can('delete', Exercise::class)) {
            session()->flash('error', 'You are not authorized to delete exercises.');
            return;
        }

        try {
            $exercise = Exercise::findOrFail($exerciseId);
            
            if (!Auth::user()->can('delete', $exercise)) {
                session()->flash('error', 'You are not authorized to delete exercises.');
                return;
            }

            $this->exerciseToDelete = $exercise;
            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to find exercise: ' . $e->getMessage());
        }
    }

    public function deleteExercise()
    {
        if (!Auth::user()->can('delete', Exercise::class)) {
            session()->flash('error', 'You are not authorized to delete exercises.');
            return;
        }

        if ($this->exerciseToDelete) {
            try {
                if (!Auth::user()->can('delete', $this->exerciseToDelete)) {
                    session()->flash('error', 'You are not authorized to delete exercises.');
                    return;
                }

                $this->exerciseToDelete->delete();
                $this->showDeleteModal = false;
                $this->exerciseToDelete = null;
                session()->flash('message', 'Exercise deleted successfully.');
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to delete exercise: ' . $e->getMessage());
            }
        }
    }

    public function editExercise($exerciseId)
    {
        if (!Auth::user()->can('update', Exercise::class)) {
            session()->flash('error', 'You are not authorized to edit exercises.');
            return;
        }

        try {
            $exercise = Exercise::findOrFail($exerciseId);
            
            if (!Auth::user()->can('update', $exercise)) {
                session()->flash('error', 'You are not authorized to edit exercises.');
                return;
            }

            $this->editingExercise = $exercise;
            $this->editingName = $exercise->name;
            $this->editingDescription = $exercise->description;
            $this->editingCategory = $exercise->category;
            $this->editingEquipment = $exercise->equipment;
            $this->showEditModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Exercise not found.');
        }
    }

    public function updateExercise()
    {
        if (!Auth::user()->can('update', Exercise::class)) {
            session()->flash('error', 'You are not authorized to edit exercises.');
            return;
        }

        $this->validate();

        try {
            if (!$this->editingExercise || !Auth::user()->can('update', $this->editingExercise)) {
                session()->flash('error', 'You are not authorized to edit exercises.');
                return;
            }

            $this->editingExercise->update([
                'name' => $this->editingName,
                'description' => $this->editingDescription,
                'category' => $this->editingCategory,
                'equipment' => $this->editingEquipment,
            ]);

            $this->showEditModal = false;
            $this->editingExercise = null;
            $this->reset(['editingName', 'editingDescription', 'editingCategory', 'editingEquipment']);
            session()->flash('message', 'Exercise updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update exercise: ' . $e->getMessage());
        }
    }

    public function cancelEdit()
    {
        $this->editingExercise = null;
        $this->reset(['editingName', 'editingDescription', 'editingCategory', 'editingEquipment']);
    }

    public function render()
    {
        $query = Exercise::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->equipment) {
            $query->where('equipment', $this->equipment);
        }

        $exercises = $query->orderBy('name')->paginate(20);

        $categories = Exercise::distinct()->pluck('category')->filter()->sort()->values();
        $equipment = Exercise::distinct()->pluck('equipment')->filter()->sort()->values();

        return view('livewire.exercise-list', [
            'exercises' => $exercises,
            'categories' => $categories,
            'equipment' => $equipment,
        ]);
    }
} 