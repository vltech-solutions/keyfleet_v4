<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Vehicle
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Bookings
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Revenue
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Your Earnings
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Utilization
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($carPerformance as $car)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    @if($car['image'])
                                        <img src="{{ $car['image'] }}" 
                                             alt="{{ $car['name'] }}" 
                                             class="h-16 w-16 object-contain">
                                    @else
                                        <div class="h-16 w-16 bg-gray-200 dark:bg-gray-600 flex items-center justify-center rounded">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $car['name'] }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $car['brand'] }} {{ $car['model'] }} • {{ $car['plate_number'] }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $car['status_color'] === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                   ($car['status_color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                {{ $car['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white">
                            {{ $car['booking_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white">
                            ₱{{ number_format($car['total_revenue'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-purple-600 dark:text-purple-400 font-semibold">
                            ₱{{ number_format($car['partner_earnings'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end">
                                <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: {{ min($car['utilization_rate'], 100) }}%">
                                    </div>
                                </div>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $car['utilization_rate'] }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No vehicles found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($carPerformance as $car)
            <div class="p-4 space-y-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                <!-- Vehicle Header with Image -->
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 h-16 w-16">
                        @if($car['image'])
                            <img src="{{ $car['image'] }}" 
                                 alt="{{ $car['name'] }}" 
                                 class="h-16 w-16 object-contain rounded-lg">
                        @else
                            <div class="h-16 w-16 bg-gray-200 dark:bg-gray-600 flex items-center justify-center rounded-lg">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $car['name'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $car['brand'] }} {{ $car['model'] }}
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $car['plate_number'] }}
                        </div>
                    </div>
                    <div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $car['status_color'] === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                               ($car['status_color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                               'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                            {{ $car['status'] }}
                        </span>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Bookings</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $car['booking_count'] }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Revenue</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($car['total_revenue'], 2) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Earnings</div>
                        <div class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                            ₱{{ number_format($car['partner_earnings'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Utilization Progress Bar -->
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500 dark:text-gray-400">Utilization</span>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                            {{ $car['utilization_rate'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ min($car['utilization_rate'], 100) }}%">
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                No vehicles found
            </div>
        @endforelse
    </div>
</div>