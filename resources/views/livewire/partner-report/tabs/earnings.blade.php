<div class="space-y-6">
    <!-- Earnings Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Gross Revenue</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                ₱{{ number_format($summaryStats['revenue_in_range'] ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Your Earnings (Net)</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                ₱{{ number_format($summaryStats['partner_earnings'] ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Total earnings for all bookings
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Company Commission</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                ₱{{ number_format($summaryStats['company_earnings'] ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Car rental owner's Commission
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Outstanding Balance</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                ₱{{ number_format($summaryStats['balance_in_range'] ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ $summaryStats['completed_in_range'] ?? 0 }} completed bookings
            </p>
        </div>
    </div>

    <!-- Commission Breakdown -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Commission Breakdown</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Current Commission Structure</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Commission Type</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ ucfirst($partner->commission_type ?? 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Commission Value</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ isset($partner->commission_type) && $partner->commission_type === 'percentage' 
                                ? ($partner->commission_value ?? 0) . '%' 
                                : '₱' . number_format($partner->commission_value ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Commission Base</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ ucfirst(str_replace('_', ' ', $partner->commission_base ?? 'N/A')) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <span class="text-sm text-yellow-700 dark:text-yellow-300">
                            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Note:
                        </span>
                        <span class="text-sm font-medium text-yellow-700 dark:text-yellow-300">
                            Changes to commission settings only affect future bookings
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Summary (Based on Historical Data)</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <span class="text-sm text-purple-700 dark:text-purple-300">Partner's Share</span>
                        <span class="text-sm font-medium text-purple-900 dark:text-purple-200">
                            {{ ($summaryStats['bookings_in_range'] ?? 0) > 0 
                                ? round((($summaryStats['partner_earnings'] ?? 0) / max(($summaryStats['revenue_in_range'] ?? 1), 1)) * 100, 1) 
                                : 0 }}%
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <span class="text-sm text-blue-700 dark:text-blue-300">Company Commission</span>
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                            {{ ($summaryStats['bookings_in_range'] ?? 0) > 0 
                                ? round((($summaryStats['company_earnings'] ?? 0) / max(($summaryStats['revenue_in_range'] ?? 1), 1)) * 100, 1) 
                                : 0 }}%
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <span class="text-sm text-green-700 dark:text-green-300">Total Bookings</span>
                        <span class="text-sm font-medium text-green-900 dark:text-green-200">
                            {{ $summaryStats['bookings_in_range'] ?? 0 }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Commission Rate Applied</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $summaryStats['avg_commission_rate'] ?? 0 }}% average
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historical Commission Rates -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Commission Rates Applied Per Booking</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Booking ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Commission Type
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Rate Applied
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Base
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                #{{ $booking->booking_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $booking->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ ($booking->commission_type ?? '') === 'percentage' 
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ ucfirst($booking->commission_type ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                                @if(isset($booking->commission_type) && $booking->commission_type === 'percentage')
                                    {{ $booking->commission_value ?? 0 }}%
                                @elseif(isset($booking->commission_type) && $booking->commission_type === 'fixed')
                                    ₱{{ number_format($booking->commission_value ?? 0, 2) }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $booking->commission_base ?? 'N/A')) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No bookings found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $bookings->links() }}
        </div>
    </div>

    <!-- Commission Calculation Example -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hidden">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How Partner's Earnings Are Calculated</h3>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 dark:text-gray-300">Booking Total</span>
                    <span class="font-medium text-gray-900 dark:text-white">₱10,000.00</span>
                </div>
                @if(isset($partner->commission_type) && $partner->commission_type === 'percentage')
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span class="ml-4">− Company Commission ({{ $partner->commission_value ?? 0 }}%)</span>
                        <span class="text-red-600">- ₱{{ number_format(10000 * (($partner->commission_value ?? 0) / 100), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span class="ml-4">= Partner's Net Earnings ({{ 100 - ($partner->commission_value ?? 0) }}%)</span>
                        <span class="text-purple-600 font-semibold">₱{{ number_format(10000 * ((100 - ($partner->commission_value ?? 0)) / 100), 2) }}</span>
                    </div>
                @else
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span class="ml-4">− Company Commission (Fixed)</span>
                        <span class="text-red-600">- ₱{{ number_format($partner->commission_value ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span class="ml-4">= Partner's Net Earnings</span>
                        <span class="text-purple-600 font-semibold">₱{{ number_format(10000 - ($partner->commission_value ?? 0), 2) }}</span>
                    </div>
                @endif
                <div class="border-t border-blue-200 dark:border-blue-700 my-2"></div>
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-blue-700 dark:text-blue-300">Company Commission</span>
                    <span class="text-blue-700 dark:text-blue-300">
                        @if(isset($partner->commission_type) && $partner->commission_type === 'percentage')
                            ₱{{ number_format(10000 * (($partner->commission_value ?? 0) / 100), 2) }}
                        @else
                            ₱{{ number_format($partner->commission_value ?? 0, 2) }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-purple-700 dark:text-purple-300">Partner's Net Earnings</span>
                    <span class="text-purple-700 dark:text-purple-300">
                        @if(isset($partner->commission_type) && $partner->commission_type === 'percentage')
                            ₱{{ number_format(10000 * ((100 - ($partner->commission_value ?? 0)) / 100), 2) }}
                        @else
                            ₱{{ number_format(10000 - ($partner->commission_value ?? 0), 2) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            * This is an example calculation. The company takes a {{ $partner->commission_type === 'percentage' ? $partner->commission_value . '%' : '₱' . number_format($partner->commission_value ?? 0, 2) }} commission from each booking.
        </p>
    </div>
</div>