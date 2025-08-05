<?php

namespace App\Livewire;

use App\Models\WorkoutSession;
use App\Models\Exercise;
use App\Models\WorkoutPlan;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.navigation')]
class WorkoutHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $dateRange = 'all';
    public $selectedPlan = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedDate = null;

    public function mount($date = null)
    {
        if ($date) {
            $this->selectedDate = $date;
            $this->dateRange = 'custom';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        if ($this->dateRange !== 'custom') {
            $this->selectedDate = null;
        }
        $this->resetPage();
    }

    public function updatingSelectedPlan()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    protected function getDateRangeFilter()
    {
        if ($this->dateRange === 'custom' && $this->selectedDate) {
            return Carbon::parse($this->selectedDate)->startOfDay();
        }

        return match($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => null,
        };
    }

    public function render()
    {
        $query = WorkoutSession::with(['workoutPlan', 'exerciseSets.exercise'])
            ->where('user_id', auth()->id())
            ->orderBy('completed_at', 'asc');

        if ($this->search) {
            $query->whereHas('workoutPlan', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })->orWhereHas('exerciseSets.exercise', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($dateFrom = $this->getDateRangeFilter()) {
            if ($this->dateRange === 'custom') {
                $query->whereDate('date', $dateFrom);
            } else {
                $query->where(function($q) use ($dateFrom) {
                    $q->where('created_at', '>=', $dateFrom)
                      ->orWhere('completed_at', '>=', $dateFrom);
                });
            }
        }

        if ($this->selectedPlan) {
            $query->where('workout_plan_id', $this->selectedPlan);
        }

        if ($this->sortField === 'completed_at') {
            $query->orderByRaw('COALESCE(completed_at, created_at) ' . $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return view('livewire.workout-history', [
            'sessions' => $query->paginate(10),
            'workoutPlans' => WorkoutPlan::where('user_id', auth()->id())->get(),
        ]);
    }
} 