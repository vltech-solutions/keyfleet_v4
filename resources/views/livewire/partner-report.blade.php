<div>
    @if($loading)
        <div class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Loading your report...</p>
            </div>
        </div>
    @else
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Company Logo -->
                            @if($partner->company && $partner->company->avatar_url)
                                <div class="flex-shrink-0">
                                    <img src="{{ Storage::url($partner->company->avatar_url) }}" 
                                        alt="{{ $partner->company->name }}" 
                                        class="h-16 w-16 object-contain rounded-lg">
                                </div>
                            @else
                                <div class="flex-shrink-0 h-16 w-16 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $partner->company->name }} 
                                    <small class="text-sm font-medium text-gray-500 dark:text-gray-400">Partner's Report</small>
                                </h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $partner->name }} • {{ $partner->cars->count() }} vehicles in fleet
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex items-center space-x-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Report as of {{ now()->format('F j, Y g:i A') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <!-- Main Filter Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date Range
                            </label>
                            <select wire:model.live="dateRange" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <!-- Car Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Filter by Car
                            </label>
                            <select wire:model.live="selectedCar" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Cars</option>
                                @foreach($carOptions as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Booking Status
                            </label>
                            <select wire:model.live="bookingStatus" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Status</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Search
                            </label>
                            <div class="relative">
                                <input type="text" 
                                    wire:model.live.debounce.300ms="searchTerm" 
                                    placeholder="Search bookings..."
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 pl-10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Date Range (appears below when selected) -->
                    @if($dateRange === 'custom')
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Start Date
                                    </label>
                                    <input type="date" 
                                        wire:model="customStartDate" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        End Date
                                    </label>
                                    <input type="date" 
                                        wire:model="customEndDate" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="applyCustomDateRange" 
                                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Apply Range
                                    </button>
                                    <button wire:click="resetFilters" 
                                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Reset Button (when not in custom mode) -->
                    @if($dateRange !== 'custom')
                        <div class="mt-4 flex justify-end">
                            <button wire:click="resetFilters" 
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset Filters
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="switchTab('earnings')" 
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors
                                    {{ $activeTab === 'earnings' 
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                            Earnings Report
                        </button>
                        <button wire:click="switchTab('cars')" 
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors
                                    {{ $activeTab === 'cars' 
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                            Vehicle Performance
                        </button>
                        <button wire:click="switchTab('bookings')" 
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors
                                    {{ $activeTab === 'bookings' 
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                            Bookings
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                @switch($activeTab)
                    @case('earnings')
                        @include('livewire.partner-report.tabs.earnings')
                    @break

                    @case('cars')
                        @include('livewire.partner-report.tabs.cars')
                    @break

                    @case('bookings')
                        @include('livewire.partner-report.tabs.bookings')
                    @break
                @endswitch
            </div>

            <!-- Footer -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                    This report is generated from KeyFleet system. Data is updated in real-time.
                </p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:initialized', function () {
        // Initialize charts after Livewire is ready
        Livewire.on('initCharts', function(data) {
            // Monthly Revenue Chart
            const ctx = document.getElementById('monthlyRevenueChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.months.map(item => item.month),
                        datasets: [
                            {
                                label: 'Revenue',
                                data: data.months.map(item => item.revenue),
                                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Your Earnings',
                                data: data.months.map(item => item.earnings),
                                backgroundColor: 'rgba(139, 92, 246, 0.5)',
                                borderColor: 'rgba(139, 92, 246, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });

        // Dispatch the initCharts event after Livewire renders
        Livewire.dispatch('initCharts', {
            months: @json($monthlyRevenue)
        });
    });
</script>
@endpush