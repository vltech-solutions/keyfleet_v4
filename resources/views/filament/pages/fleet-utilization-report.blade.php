<x-filament-panels::page>
    <div>
        <form wire:submit="submit">
            {{ $this->form }}
        </form>
    </div>


    @php 
        $report = $this->report_data; 
        $chartData = $this->chart_data;
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 xl:grid-cols-3">
        
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fleet Utilization Rate</h3>
                <p class="mt-1 text-2xl font-bold text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400">
                    {{ $report['fleet_rate'] }}%
                </p>
                <p class="mt-1 text-xs text-gray-400">{{ $report['remark'] }}</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-{{ $report['color'] }}-500/10">
                <x-heroicon-o-chart-pie class="w-7 h-7 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" />
            </div>
        </div>

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Vehicles</h3>
                <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($report['total_cars']) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Analyzed for selected period</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-heroicon-o-truck class="w-7 h-7 text-blue-600 dark:text-blue-400" />
            </div>
        </div>

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                <p class="mt-1 text-lg font-bold {{ $report['fleet_rate'] >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $report['fleet_rate'] >= 70 ? 'Healthy Operations' : 'Attention Needed' }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Real-time status assessment</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full {{ $report['fleet_rate'] >= 70 ? 'bg-emerald-500/10' : 'bg-amber-500/10' }}">
                <x-heroicon-o-check-circle class="w-7 h-7 {{ $report['fleet_rate'] >= 70 ? 'text-emerald-600' : 'text-amber-600' }}" />
            </div>
        </div>

    </div>

    <x-filament::section class="mb-6">
        <x-slot name="heading">Utilization Trend</x-slot>
        
        <div wire:key="fleet-chart" class="h-[300px] w-full"
            wire:ignore
            x-data="{
                chart: null,
                init() {
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    this.chart = new Chart($refs.canvas, {
                        type: 'line',
                        data: @js($this->chart_data),
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            scales: {
                                x: {
                                    grid: { display: false } // Removes vertical grids
                                },
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    grid: { display: false }, // Removes horizontal grids
                                    ticks: {
                                        callback: value => value + '%'
                                    }
                                }
                            },
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });

                    Livewire.on('updateChart', (event) => {

                        if (this.chart) {
                            this.chart.destroy();
                        }

                        this.chart = new Chart($refs.canvas, {
                            type: 'line',
                            data: event.data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { intersect: false, mode: 'index' },
                                scales: {
                                    x: {
                                        grid: { display: false } // Removes vertical grids
                                    },
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        grid: { display: false }, // Removes horizontal grids
                                        ticks: {
                                            callback: value => value + '%'
                                        }
                                    }
                                },
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        });
                    });
                }
            }"
        >
            <canvas x-ref="canvas" id="fleetUtilizationChart"></canvas>
        </div>
    </x-filament::section>

    {{-- Native Filament Table --}}
    <div> 
        {{ $this->table }}
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

</x-filament-panels::page>