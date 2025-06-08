<?php

namespace App\Livewire;

use App\Models\Exercise;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;

#[Layout('components.layouts.app')]
class ExerciseManager extends Component
{
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:1000')]
    public $description = '';

    #[Rule('required|string|in:chest,back,legs,shoulders,arms,core,cardio,other')]
    public $category = '';

    #[Rule('required|string|in:barbell,dumbbell,machine,bodyweight,cables,kettlebell,resistance bands,other')]
    public $equipment = '';

    public $showCreateModal = false;
    public $search = '';
    public $exercises = [];
    public $editingExercise = null;

    protected $categories = [
        'chest' => 'Chest',
        'back' => 'Back',
        'legs' => 'Legs',
        'shoulders' => 'Shoulders',
        'arms' => 'Arms',
        'core' => 'Core',
        'cardio' => 'Cardio',
        'other' => 'Other',
    ];

    protected $equipment_types = [
        'barbell' => 'Barbell',
        'dumbbell' => 'Dumbbell',
        'machine' => 'Machine',
        'bodyweight' => 'Bodyweight',
        'cables' => 'Cables',
        'kettlebell' => 'Kettlebell',
        'resistance bands' => 'Resistance Bands',
        'other' => 'Other',
    ];

    public function mount()
    {
        $this->refreshExercises();
    }

    public function refreshExercises()
    {
        $query = Exercise::query();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%')
                  ->orWhere('equipment', 'like', '%' . $this->search . '%');
            });
        }
        
        $this->exercises = $query->orderBy('name')->get();
    }

    public function updatedSearch()
    {
        $this->refreshExercises();
    }

    public function createExercise()
    {
        $this->validate();

        Exercise::create([
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'equipment' => $this->equipment,
        ]);

        $this->reset(['name', 'description', 'category', 'equipment']);
        $this->showCreateModal = false;
        $this->refreshExercises();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Exercise created successfully!']);
    }

    public function editExercise(Exercise $exercise)
    {
        $this->editingExercise = $exercise;
        $this->name = $exercise->name;
        $this->description = $exercise->description;
        $this->category = $exercise->category;
        $this->equipment = $exercise->equipment;
        $this->showCreateModal = true;
    }

    public function updateExercise()
    {
        $this->validate();

        $this->editingExercise->update([
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'equipment' => $this->equipment,
        ]);

        $this->reset(['name', 'description', 'category', 'equipment', 'editingExercise']);
        $this->showCreateModal = false;
        $this->refreshExercises();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Exercise updated successfully!']);
    }

    public function deleteExercise(Exercise $exercise)
    {
        $exercise->delete();
        $this->refreshExercises();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Exercise deleted successfully!']);
    }

    public function render()
    {
        return view('livewire.exercise-manager', [
            'categories' => $this->categories,
            'equipment_types' => $this->equipment_types,
        ]);
    }
}
