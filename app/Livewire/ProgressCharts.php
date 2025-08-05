<?php

namespace App\Livewire;

use App\Models\WeightMeasurement;
use App\Models\BodyMeasurement;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProgressCharts extends Component
{
    public $timeRange = '30'; // days
    public $selectedChart = 'weight';
    public $chartData = [];

    public function mount()
    {
        $this->loadChartData();
    }

    public function updatedTimeRange()
    {
        $this->loadChartData();
        $this->dispatch('updateCharts');
    }

    public function updatedSelectedChart()
    {
        $this->loadChartData();
        $this->dispatch('updateCharts');
    }

    public function render()
    {
        return view('livewire.progress-charts');
    }

    private function loadChartData()
    {
        $startDate = Carbon::now()->subDays($this->timeRange);
        
        \Log::info('Loading chart data', [
            'selectedChart' => $this->selectedChart,
            'timeRange' => $this->timeRange,
            'startDate' => $startDate,
            'user_id' => Auth::id()
        ]);
        
        if ($this->selectedChart === 'weight') {
            $this->chartData = $this->getWeightChartData($startDate);
        } elseif ($this->selectedChart === 'body_measurements') {
            $this->chartData = $this->getBodyMeasurementsChartData($startDate);
        } elseif ($this->selectedChart === 'body_fat') {
            $this->chartData = $this->getBodyFatChartData($startDate);
        } elseif ($this->selectedChart === 'bmi') {
            $this->chartData = $this->getBmiChartData($startDate);
        }
        
        \Log::info('Chart data loaded', [
            'chartData' => $this->chartData,
            'hasLabels' => !empty($this->chartData['labels']),
            'labelCount' => count($this->chartData['labels'] ?? [])
        ]);
        
        // Ensure chartData is not empty
        if (empty($this->chartData['labels'])) {
            $this->chartData = [
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

    private function getWeightChartData($startDate)
    {
        $measurements = WeightMeasurement::where('user_id', Auth::id())
            ->where('measurement_date', '>=', $startDate)
            ->orderBy('measurement_date', 'asc')
            ->get();

        \Log::info('Weight measurements query', [
            'user_id' => Auth::id(),
            'startDate' => $startDate,
            'measurementCount' => $measurements->count(),
            'measurements' => $measurements->toArray()
        ]);

        $labels = [];
        $weights = [];
        $trendline = [];

        foreach ($measurements as $measurement) {
            $labels[] = $measurement->measurement_date->format('M j');
            $weights[] = round($measurement->weight_in_kg, 1);
        }

        // Calculate trendline (simple linear regression)
        if (count($weights) > 1) {
            $trendline = $this->calculateTrendline($weights);
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Weight (kg)',
                    'data' => $weights,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                ],
                [
                    'label' => 'Trend',
                    'data' => $trendline,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [5, 5],
                    'tension' => 0,
                    'fill' => false,
                ]
            ]
        ];

        \Log::info('Weight chart data generated', [
            'labels' => $labels,
            'weights' => $weights,
            'trendline' => $trendline,
            'chartData' => $chartData
        ]);

        return $chartData;
    }

    private function getBodyMeasurementsChartData($startDate)
    {
        $measurements = BodyMeasurement::where('user_id', Auth::id())
            ->where('measurement_date', '>=', $startDate)
            ->orderBy('measurement_date', 'asc')
            ->get();

        $labels = [];
        $chest = [];
        $waist = [];
        $hips = [];
        $biceps = [];
        $thighs = [];

        foreach ($measurements as $measurement) {
            $labels[] = $measurement->measurement_date->format('M j');
            $chest[] = $measurement->chest ?? null;
            $waist[] = $measurement->waist ?? null;
            $hips[] = $measurement->hips ?? null;
            $biceps[] = $measurement->biceps ?? null;
            $thighs[] = $measurement->thighs ?? null;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Chest (cm)',
                    'data' => $chest,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Waist (cm)',
                    'data' => $waist,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Hips (cm)',
                    'data' => $hips,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Biceps (cm)',
                    'data' => $biceps,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Thighs (cm)',
                    'data' => $thighs,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.1,
                ]
            ]
        ];
    }

    private function getBodyFatChartData($startDate)
    {
        $measurements = BodyMeasurement::where('user_id', Auth::id())
            ->where('measurement_date', '>=', $startDate)
            ->whereNotNull('body_fat_percentage')
            ->orderBy('measurement_date', 'asc')
            ->get();

        $labels = [];
        $bodyFat = [];
        $muscleMass = [];

        foreach ($measurements as $measurement) {
            $labels[] = $measurement->measurement_date->format('M j');
            $bodyFat[] = $measurement->body_fat_percentage;
            $muscleMass[] = $measurement->muscle_mass ?? null;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Body Fat %',
                    'data' => $bodyFat,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Muscle Mass (kg)',
                    'data' => $muscleMass,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    private function getBmiChartData($startDate)
    {
        $measurements = BodyMeasurement::where('user_id', Auth::id())
            ->where('measurement_date', '>=', $startDate)
            ->whereNotNull('height')
            ->orderBy('measurement_date', 'asc')
            ->get();

        $labels = [];
        $bmi = [];

        foreach ($measurements as $measurement) {
            $bmiValue = $measurement->bmi;
            if ($bmiValue !== null) {
                $labels[] = $measurement->measurement_date->format('M j');
                $bmi[] = round($bmiValue, 1);
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'BMI',
                    'data' => $bmi,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.1,
                    'fill' => true,
                ]
            ]
        ];
    }

    private function calculateTrendline($data)
    {
        $n = count($data);
        if ($n < 2) return $data;

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $data[$i];
            $sumXY += $i * $data[$i];
            $sumX2 += $i * $i;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $trendline = [];
        for ($i = 0; $i < $n; $i++) {
            $trendline[] = round($slope * $i + $intercept, 1);
        }

        return $trendline;
    }

    public function getChartOptions()
    {
        $baseOptions = [
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
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => $this->getYAxisLabel()
                    ]
                ]
            ]
        ];

        // Add dual y-axis for body fat chart
        if ($this->selectedChart === 'body_fat') {
            $baseOptions['scales']['y1'] = [
                'type' => 'linear',
                'display' => true,
                'position' => 'right',
                'title' => [
                    'display' => true,
                    'text' => 'Muscle Mass (kg)'
                ],
                'grid' => [
                    'drawOnChartArea' => false,
                ],
            ];
        }

        return $baseOptions;
    }

    private function getYAxisLabel()
    {
        switch ($this->selectedChart) {
            case 'weight':
                return 'Weight (kg)';
            case 'body_measurements':
                return 'Measurement (cm)';
            case 'body_fat':
                return 'Body Fat (%)';
            case 'bmi':
                return 'BMI';
            default:
                return 'Value';
        }
    }
}
