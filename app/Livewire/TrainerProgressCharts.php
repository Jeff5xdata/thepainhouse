<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WeightMeasurement;
use App\Models\BodyMeasurement;
use Carbon\Carbon;

class TrainerProgressCharts extends Component
{
    public $clientId;
    public $client;
    public $timeRange = '30';
    public $selectedChart = 'weight';

    public function mount($clientId = null)
    {
        $this->clientId = $clientId;
        
        // Check if user is a trainer
        if (!auth()->user()->isTrainer()) {
            abort(403, 'Access denied. Only trainers can view client data.');
        }
        
        if ($this->clientId) {
            $this->client = User::with(['weightMeasurements', 'bodyMeasurements'])->findOrFail($this->clientId);
            
            // Check if the client belongs to this trainer
            if (!$this->client->trainer || $this->client->trainer->id !== auth()->id()) {
                abort(403, 'Access denied. You can only view your own clients.');
            }
        }
    }

    public function updatedTimeRange()
    {
        $this->dispatch('updateCharts');
    }

    public function updatedSelectedChart()
    {
        $this->dispatch('updateCharts');
    }

    public function getChartDataProperty()
    {
        if (!$this->client) return [];

        $days = $this->timeRange === '7' ? 7 : ($this->timeRange === '30' ? 30 : 90);
        $startDate = $this->timeRange === 'all' ? null : now()->subDays($days);

        // Weight data
        $weightQuery = $this->client->weightMeasurements();
        if ($startDate) {
            $weightQuery->where('measurement_date', '>=', $startDate);
        }
        $weightData = $weightQuery->orderBy('measurement_date')->get();

        // Body measurement data
        $bodyQuery = $this->client->bodyMeasurements();
        if ($startDate) {
            $bodyQuery->where('measurement_date', '>=', $startDate);
        }
        $bodyData = $bodyQuery->orderBy('measurement_date')->get();

        $chartData = [
            'weight' => [
                'labels' => $weightData->pluck('measurement_date')->map(fn($date) => $date->format('M j')),
                'datasets' => [
                    [
                        'label' => 'Weight (kg)',
                        'data' => $weightData->pluck('weight'),
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.1,
                    ]
                ]
            ],
            'bodyMeasurements' => [
                'labels' => $bodyData->pluck('measurement_date')->map(fn($date) => $date->format('M j')),
                'datasets' => [
                    [
                        'label' => 'Chest (cm)',
                        'data' => $bodyData->pluck('chest')->map(fn($value) => $value ?? null),
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'tension' => 0.1,
                    ],
                    [
                        'label' => 'Waist (cm)',
                        'data' => $bodyData->pluck('waist')->map(fn($value) => $value ?? null),
                        'borderColor' => 'rgb(245, 158, 11)',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'tension' => 0.1,
                    ],
                    [
                        'label' => 'Hips (cm)',
                        'data' => $bodyData->pluck('hips')->map(fn($value) => $value ?? null),
                        'borderColor' => 'rgb(168, 85, 247)',
                        'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                        'tension' => 0.1,
                    ]
                ]
            ],
            'bmi' => [
                'labels' => $bodyData->pluck('measurement_date')->map(fn($date) => $date->format('M j')),
                'datasets' => [
                    [
                        'label' => 'BMI',
                        'data' => $bodyData->pluck('bmi')->map(fn($value) => $value ?? null),
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'tension' => 0.1,
                    ]
                ]
            ],
            'bodyComposition' => [
                'labels' => $bodyData->pluck('measurement_date')->map(fn($date) => $date->format('M j')),
                'datasets' => [
                    [
                        'label' => 'Body Fat %',
                        'data' => $bodyData->pluck('body_fat_percentage')->map(fn($value) => $value ?? null),
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'tension' => 0.1,
                    ],
                    [
                        'label' => 'Muscle Mass (kg)',
                        'data' => $bodyData->pluck('muscle_mass')->map(fn($value) => $value ?? null),
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.1,
                    ]
                ]
            ]
        ];
        
        // Ensure each chart has data
        foreach ($chartData as $chartType => $data) {
            if (empty($data['labels'])) {
                $chartData[$chartType] = [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'No Data Available',
                            'data' => [],
                            'borderColor' => 'rgb(156, 163, 175)',
                            'backgroundColor' => 'rgba(156, 163, 175, 0.1)',
                            'tension' => 0.1,
                            'fill' => true,
                        ]
                    ]
                ];
            }
        }
        
        return $chartData;
    }

    public function getStatsProperty()
    {
        if (!$this->client) return [];

        $days = $this->timeRange === '7' ? 7 : ($this->timeRange === '30' ? 30 : 90);
        $startDate = $this->timeRange === 'all' ? null : now()->subDays($days);

        // Weight stats
        $weightQuery = $this->client->weightMeasurements();
        if ($startDate) {
            $weightQuery->where('measurement_date', '>=', $startDate);
        }
        $weightMeasurements = $weightQuery->orderBy('measurement_date')->get();

        $weightChange = null;
        $currentWeight = null;
        if ($weightMeasurements->count() > 1) {
            $currentWeight = $weightMeasurements->last()->weight;
            $firstWeight = $weightMeasurements->first()->weight;
            $weightChange = $currentWeight - $firstWeight;
        }

        // Body measurement stats
        $bodyQuery = $this->client->bodyMeasurements();
        if ($startDate) {
            $bodyQuery->where('measurement_date', '>=', $startDate);
        }
        $bodyMeasurements = $bodyQuery->orderBy('measurement_date')->get();

        $currentBMI = $bodyMeasurements->last()->bmi ?? null;
        $bmiChange = null;
        if ($bodyMeasurements->count() > 1 && $bodyMeasurements->last()->bmi && $bodyMeasurements->first()->bmi) {
            $bmiChange = $bodyMeasurements->last()->bmi - $bodyMeasurements->first()->bmi;
        }

        return [
            'weight' => [
                'current' => $currentWeight,
                'change' => $weightChange,
                'measurements' => $weightMeasurements->count(),
            ],
            'bmi' => [
                'current' => $currentBMI,
                'change' => $bmiChange,
                'measurements' => $bodyMeasurements->count(),
            ],
            'body_fat' => [
                'current' => $bodyMeasurements->last()->body_fat_percentage ?? null,
                'change' => $bodyMeasurements->count() > 1 ? 
                    ($bodyMeasurements->last()->body_fat_percentage ?? 0) - ($bodyMeasurements->first()->body_fat_percentage ?? 0) : null,
            ],
            'muscle_mass' => [
                'current' => $bodyMeasurements->last()->muscle_mass ?? null,
                'change' => $bodyMeasurements->count() > 1 ? 
                    ($bodyMeasurements->last()->muscle_mass ?? 0) - ($bodyMeasurements->first()->muscle_mass ?? 0) : null,
            ]
        ];
    }

    public function render()
    {
        if (!$this->client) {
            return view('livewire.trainer-progress-charts', [
                'chartData' => [],
                'stats' => [],
            ]);
        }

        return view('livewire.trainer-progress-charts', [
            'chartData' => $this->chartData,
            'stats' => $this->stats,
        ]);
    }
} 