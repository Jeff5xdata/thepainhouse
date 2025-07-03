<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Progress Charts</h1>
            <p class="mt-2 text-gray-600">Visualize your fitness progress with interactive charts</p>
        </div>

        <!-- Chart Controls -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="selectedChart" class="block text-sm font-medium text-gray-700">Chart Type</label>
                        <select wire:model="selectedChart" id="selectedChart" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="weight">Weight Progress</option>
                            <option value="body_measurements">Body Measurements</option>
                            <option value="body_fat">Body Fat & Muscle Mass</option>
                            <option value="bmi">BMI Progress</option>
                        </select>
                    </div>

                    <div>
                        <label for="timeRange" class="block text-sm font-medium text-gray-700">Time Range</label>
                        <select wire:model="timeRange" id="timeRange" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="180">Last 6 months</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="h-96">
                    <canvas id="progressChart" wire:ignore></canvas>
                </div>
            </div>
        </div>

        <!-- Chart Legend -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Chart Information</h3>
                
                @if($selectedChart === 'weight')
                <div class="prose prose-sm text-gray-500">
                    <p>This chart shows your weight progress over time. The blue line represents your actual weight measurements, while the red dashed line shows the trend. A downward trend indicates weight loss, while an upward trend indicates weight gain.</p>
                    <ul class="mt-2">
                        <li><strong>Weight (kg):</strong> Your weight measurements converted to kilograms</li>
                        <li><strong>Trend:</strong> Linear regression trend line showing overall direction</li>
                    </ul>
                </div>
                @elseif($selectedChart === 'body_measurements')
                <div class="prose prose-sm text-gray-500">
                    <p>This chart displays multiple body measurements over time, allowing you to track changes in different areas of your body.</p>
                    <ul class="mt-2">
                        <li><strong>Chest:</strong> Chest circumference in centimeters</li>
                        <li><strong>Waist:</strong> Waist circumference in centimeters</li>
                        <li><strong>Hips:</strong> Hip circumference in centimeters</li>
                        <li><strong>Biceps:</strong> Bicep circumference in centimeters</li>
                        <li><strong>Thighs:</strong> Thigh circumference in centimeters</li>
                    </ul>
                </div>
                @elseif($selectedChart === 'body_fat')
                <div class="prose prose-sm text-gray-500">
                    <p>This chart shows your body fat percentage and muscle mass over time, helping you track body composition changes.</p>
                    <ul class="mt-2">
                        <li><strong>Body Fat %:</strong> Your body fat percentage (left axis)</li>
                        <li><strong>Muscle Mass:</strong> Your muscle mass in kilograms (right axis)</li>
                    </ul>
                </div>
                @elseif($selectedChart === 'bmi')
                <div class="prose prose-sm text-gray-500">
                    <p>This chart displays your Body Mass Index (BMI) over time. BMI is calculated using your height and weight measurements.</p>
                    <ul class="mt-2">
                        <li><strong>BMI:</strong> Body Mass Index calculated from height and weight</li>
                        <li><strong>BMI Categories:</strong> Underweight (&lt;18.5), Normal (18.5-24.9), Overweight (25-29.9), Obese (&gt;30)</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('weight.tracker') }}" class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-700 ring-4 ring-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-8">
                    <h3 class="text-lg font-medium">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Weight Tracker
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Add and manage your weight measurements
                    </p>
                </div>
                <span class="pointer-events-none absolute top-6 right-6 text-gray-300 group-hover:text-gray-400" aria-hidden="true">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z"/>
                    </svg>
                </span>
            </a>

            <a href="{{ route('body.measurements') }}" class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-700 ring-4 ring-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-8">
                    <h3 class="text-lg font-medium">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Body Measurements
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Track detailed body measurements
                    </p>
                </div>
                <span class="pointer-events-none absolute top-6 right-6 text-gray-300 group-hover:text-gray-400" aria-hidden="true">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z"/>
                    </svg>
                </span>
            </a>

            <a href="{{ route('workout.progress') }}" class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-700 ring-4 ring-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-8">
                    <h3 class="text-lg font-medium">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Workout Progress
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        View your workout performance
                    </p>
                </div>
                <span class="pointer-events-none absolute top-6 right-6 text-gray-300 group-hover:text-gray-400" aria-hidden="true">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z"/>
                    </svg>
                </span>
            </a>

            <a href="{{ route('nutrition') }}" class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-700 ring-4 ring-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-8">
                    <h3 class="text-lg font-medium">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Nutrition
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Track your nutrition intake
                    </p>
                </div>
                <span class="pointer-events-none absolute top-6 right-6 text-gray-300 group-hover:text-gray-400" aria-hidden="true">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z"/>
                    </svg>
                </span>
            </a>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script>
        let chart = null;

        function initChart() {
            const ctx = document.getElementById('progressChart').getContext('2d');
            
            if (chart) {
                chart.destroy();
            }

            const chartData = @json($chartData);
            const chartOptions = @json($this->getChartOptions());

            chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: chartOptions
            });
        }

        // Initialize chart when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
        });

        // Update chart when Livewire updates
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                if (component.fingerprint.name === 'progress-charts') {
                    setTimeout(() => {
                        initChart();
                    }, 100);
                }
            });
        });
    </script>
    @endpush
</div>
