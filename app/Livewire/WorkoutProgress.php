<?php

namespace App\Livewire;

use App\Models\WorkoutSession;
use App\Models\Exercise;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.navigation')]
class WorkoutProgress extends Component
{
    public $selectedExercise = null;
    public $timeframe = 'month';
    public $progressData = [];
    public $chartData = [];

    public function mount()
    {
        $this->loadProgressData();
    }

    public function updatedSelectedExercise()
    {
        $this->loadProgressData();
    }

    public function updatedTimeframe()
    {
        $this->loadProgressData();
    }

    public function loadProgressData()
    {
        if (!$this->selectedExercise) {
            $this->progressData = [];
            $this->chartData = [];
            return;
        }

        $startDate = match($this->timeframe) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };

        $sessions = WorkoutSession::where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->whereHas('exerciseSets', function ($query) {
                $query->where('exercise_id', $this->selectedExercise)
                    ->where('completed', true);
            })
            ->with(['exerciseSets' => function ($query) {
                $query->where('exercise_id', $this->selectedExercise)
                    ->where('completed', true)
                    ->orderBy('created_at');
            }])
            ->get();

        // Group data by date
        $groupedData = [];
        foreach ($sessions as $session) {
            $date = $session->completed_at->format('Y-m-d');
            
            if (!isset($groupedData[$date])) {
                $groupedData[$date] = [
                    'max_weight' => 0,
                    'total_volume' => 0,
                    'total_reps' => 0,
                    'sets' => []
                ];
            }
            
            foreach ($session->exerciseSets as $set) {
                $groupedData[$date]['max_weight'] = max($groupedData[$date]['max_weight'], $set->weight);
                $groupedData[$date]['total_volume'] += $set->weight * $set->reps;
                $groupedData[$date]['total_reps'] += $set->reps;
                $groupedData[$date]['sets'][] = $set;
            }
        }

        $this->progressData = $groupedData;
        $this->chartData = $this->generateChartData($groupedData);
        
        // Ensure chartData is not empty
        if (empty($this->chartData['labels'])) {
            $this->chartData = [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Max Weight (' . strtoupper(auth()->user()->getPreferredWeightUnit()) . ')',
                        'data' => [],
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.1,
                        'fill' => true,
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'Total Volume (' . strtoupper(auth()->user()->getPreferredWeightUnit()) . ')',
                        'data' => [],
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.1,
                        'fill' => true,
                        'yAxisID' => 'y1',
                    ],
                    [
                        'label' => 'Total Reps',
                        'data' => [],
                        'borderColor' => 'rgb(139, 92, 246)',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'tension' => 0.1,
                        'fill' => true,
                        'yAxisID' => 'y2',
                    ]
                ]
            ];
        }
    }

    private function generateChartData($groupedData)
    {
        $labels = [];
        $maxWeights = [];
        $totalVolumes = [];
        $totalReps = [];

        // Sort by date
        ksort($groupedData);

        foreach ($groupedData as $date => $data) {
            $labels[] = Carbon::parse($date)->format('M j');
            $maxWeights[] = $data['max_weight'];
            $totalVolumes[] = $data['total_volume'];
            $totalReps[] = $data['total_reps'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Max Weight (' . strtoupper(auth()->user()->getPreferredWeightUnit()) . ')',
                    'data' => $maxWeights,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Total Volume (' . strtoupper(auth()->user()->getPreferredWeightUnit()) . ')',
                    'data' => $totalVolumes,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Total Reps',
                    'data' => $totalReps,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                    'yAxisID' => 'y2',
                ]
            ]
        ];
    }

    public function getChartOptions()
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Date'
                    ]
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Max Weight (' . strtoupper(auth()->user()->getPreferredWeightUnit()) . ')'
                    ]
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Volume (' . strtoupper(auth()->user()->getPreferredWeightUnit()) . ')'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'y2' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Reps'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ]
            ]
        ];
    }

    public function render()
    {
        return view('livewire.workout-progress', [
            'exercises' => Exercise::orderBy('name')->get(),
        ]);
    }
}
