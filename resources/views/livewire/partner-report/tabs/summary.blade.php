<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $summaryStats['total_bookings'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ $summaryStats['bookings_in_range'] ?? 0 }} in this period
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gross Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ₱{{ number_format($summaryStats['revenue_in_range'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 4v2m-4-6h4m-4 4h8"/>
                    </svg>
                </div>
            </div>
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mt-2">
                <span>Paid: ₱{{ number_format($summaryStats['paid_in_range'] ?? 0, 2) }}</span>
                <span>Balance: ₱{{ number_format($summaryStats['balance_in_range'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Your Earnings</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        ₱{{ number_format($summaryStats['partner_earnings'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-green-600 dark:text-green-400 mt-2">
                After {{ $partner->commission_type === 'percentage' ? ($partner->commission_value ?? 0) . '%' : '₱' . number_format($partner->commission_value ?? 0, 2) }} commission
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $summaryStats['ongoing_bookings'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mt-2">
                <span>Upcoming: {{ $summaryStats['upcoming_bookings'] ?? 0 }}</span>
                <span>Completed: {{ $summaryStats['completed_in_range'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Monthly Revenue Trend</h3>
        <div class="h-64">
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
    </div>
</div>