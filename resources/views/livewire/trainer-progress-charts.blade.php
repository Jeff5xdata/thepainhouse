<div class="max-w-7xl mx-auto p-6">
    @if(!$client)
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No client selected</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Please select a client to view their progress charts.
            </p>
        </div>
    @else
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Progress Charts</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Viewing progress charts for {{ $client->name }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Weight</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            @if($stats['weight']['current'])
                                {{ number_format($stats['weight']['current'], 1) }} kg
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current BMI</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            @if($stats['bmi']['current'])
                                {{ number_format($stats['bmi']['current'], 1) }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Weight Change</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            @if($stats['weight']['change'] !== null)
                                @if($stats['weight']['change'] > 0)
                                    <span class="text-red-600">+{{ number_format($stats['weight']['change'], 1) }} kg</span>
                                @elseif($stats['weight']['change'] < 0)
                                    <span class="text-green-600">{{ number_format($stats['weight']['change'], 1) }} kg</span>
                                @else
                                    <span class="text-gray-600">0.0 kg</span>
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">BMI Change</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            @if($stats['bmi']['change'] !== null)
                                @if($stats['bmi']['change'] > 0)
                                    <span class="text-red-600">+{{ number_format($stats['bmi']['change'], 1) }}</span>
                                @elseif($stats['bmi']['change'] < 0)
                                    <span class="text-green-600">{{ number_format($stats['bmi']['change'], 1) }}</span>
                                @else
                                    <span class="text-gray-600">0.0</span>
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Controls -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="timeRange" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Range</label>
                        <select wire:model.live="timeRange" id="timeRange" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="all">All time</option>
                        </select>
                    </div>
                    <div>
                        <label for="selectedChart" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Chart Type</label>
                        <select wire:model.live="selectedChart" id="selectedChart" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="weight">Weight Progress</option>
                            <option value="bodyMeasurements">Body Measurements</option>
                            <option value="bmi">BMI Progress</option>
                            <option value="bodyComposition">Body Composition</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    @switch($selectedChart)
                        @case('weight')
                            Weight Progress
                            @break
                        @case('bodyMeasurements')
                            Body Measurements
                            @break
                        @case('bmi')
                            BMI Progress
                            @break
                        @case('bodyComposition')
                            Body Composition
                            @break
                        @default
                            Weight Progress
                    @endswitch
                </h3>
            </div>
            
            <div class="h-96">
                <div id="progressChart" wire:ignore></div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Body Composition</h4>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Current Body Fat</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($stats['body_fat']['current'])
                                {{ number_format($stats['body_fat']['current'], 1) }}%
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Body Fat Change</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($stats['body_fat']['change'] !== null)
                                @if($stats['body_fat']['change'] > 0)
                                    <span class="text-red-600">+{{ number_format($stats['body_fat']['change'], 1) }}%</span>
                                @elseif($stats['body_fat']['change'] < 0)
                                    <span class="text-green-600">{{ number_format($stats['body_fat']['change'], 1) }}%</span>
                                @else
                                    <span class="text-gray-600">0.0%</span>
                                @endif
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Current Muscle Mass</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($stats['muscle_mass']['current'])
                                {{ number_format($stats['muscle_mass']['current'], 1) }} kg
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Muscle Mass Change</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($stats['muscle_mass']['change'] !== null)
                                @if($stats['muscle_mass']['change'] > 0)
                                    <span class="text-green-600">+{{ number_format($stats['muscle_mass']['change'], 1) }} kg</span>
                                @elseif($stats['muscle_mass']['change'] < 0)
                                    <span class="text-red-600">{{ number_format($stats['muscle_mass']['change'], 1) }} kg</span>
                                @else
                                    <span class="text-gray-600">0.0 kg</span>
                                @endif
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Measurement Summary</h4>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Weight Measurements</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['weight']['measurements'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Body Measurements</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['bmi']['measurements'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('livewire:init', () => {
    let chart = null;
    
    function initChart() {
        const chartContainer = document.getElementById('progressChart');
        if (!chartContainer) {
            console.error('Chart container not found');
            return;
        }
        
        if (chart) {
            chart.destroy();
        }
        
        // Clear the container
        chartContainer.innerHTML = '';
        
        const chartData = @json($chartData);
        const selectedChart = @json($selectedChart);
        
        console.log('Chart data:', chartData);
        console.log('Selected chart:', selectedChart);
        
        const data = chartData[selectedChart] || chartData.weight;
        
        // Check if we have valid data
        if (!data || !data.labels || data.labels.length === 0) {
            console.log('No chart data available');
            chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
            return;
        }
        
        // Prepare data for ApexCharts
        const series = [];
        data.datasets.forEach(dataset => {
            series.push({
                name: dataset.label,
                data: dataset.data,
                color: dataset.borderColor
            });
        });
        
        const isDarkMode = document.documentElement.classList.contains('dark');
        
        const options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                },
                background: 'transparent'
            },
            series: series,
            xaxis: {
                categories: data.labels,
                labels: {
                    style: {
                        colors: isDarkMode ? '#d1d5db' : '#374151'
                    }
                },
                axisBorder: {
                    color: isDarkMode ? '#374151' : '#e5e7eb'
                },
                axisTicks: {
                    color: isDarkMode ? '#374151' : '#e5e7eb'
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: isDarkMode ? '#d1d5db' : '#374151'
                    }
                }
            },
            grid: {
                borderColor: isDarkMode ? '#374151' : '#e5e7eb',
                strokeDashArray: 4
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                position: 'top',
                labels: {
                    colors: isDarkMode ? '#d1d5db' : '#374151'
                }
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light'
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: {
                        height: 250
                    }
                }
            }]
        };
        
        try {
            chart = new ApexCharts(chartContainer, options);
            chart.render();
            console.log('Chart initialized successfully');
        } catch (error) {
            console.error('Error initializing chart:', error);
            chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Error loading chart</div>';
        }
    }
    
    // Initialize chart on page load
    setTimeout(() => {
        console.log('Initializing chart...');
        initChart();
    }, 200);
    
    // Update chart when Livewire updates
    Livewire.on('updateCharts', () => {
        console.log('Livewire updateCharts event received');
        setTimeout(() => {
            initChart();
        }, 200);
    });
});
</script>
@endpush 