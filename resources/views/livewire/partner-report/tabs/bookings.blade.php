<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Booking ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Renter
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Vehicle
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Date & Time
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Total Due
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Your Earnings
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Company Commission
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            #{{ $booking->booking_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $booking->renter_name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->contact_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $booking->car->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $booking->start_datetime->format('M d, Y g:i A') }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                to {{ $booking->end_datetime->format('M d, Y g:i A') }}
                            </div>
                            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-0.5">
                                @php
                                    $start = Carbon\Carbon::parse($booking->start_datetime);
                                    $end = Carbon\Carbon::parse($booking->end_datetime);
                                    $diffInHours = $start->diffInHours($end);
                                    $diffInDays = $start->diffInDays($end);
                                @endphp
                                @if($diffInDays > 0)
                                    {{ $diffInDays }} {{ Str::plural('day', $diffInDays) }}
                                    @if($diffInHours % 24 > 0)
                                        , {{ $diffInHours % 24 }} {{ Str::plural('hr', $diffInHours % 24) }}
                                    @endif
                                @else
                                    {{ $diffInHours }} {{ Str::plural('hr', $diffInHours) }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm text-gray-900 dark:text-white font-semibold">
                                ₱{{ number_format($booking->total_due, 2) }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5 mt-1">
                                <div class="flex justify-end gap-2">
                                    <span class="text-gray-400">Rent:</span>
                                    <span>₱{{ number_format($booking->total_rent_due, 2) }}</span>
                                </div>
                                @if($booking->extend_hours > 0)
                                    <div class="flex justify-end gap-2 text-yellow-600 dark:text-yellow-400">
                                        <span>Extend:</span>
                                        <span>+₱{{ number_format($booking->extend_due ?? 0, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->delivery_fee > 0)
                                    <div class="flex justify-end gap-2 text-blue-600 dark:text-blue-400">
                                        <span>Delivery:</span>
                                        <span>+₱{{ number_format($booking->delivery_fee, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->with_driver && $booking->driver_fee > 0)
                                    <div class="flex justify-end gap-2 text-indigo-600 dark:text-indigo-400">
                                        <span>Driver:</span>
                                        <span>+₱{{ number_format($booking->driver_fee, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->insurance > 0)
                                    <div class="flex justify-end gap-2 text-green-600 dark:text-green-400">
                                        <span>Insurance:</span>
                                        <span>+₱{{ number_format($booking->insurance, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->security_deposit > 0)
                                    <div class="flex justify-end gap-2 text-teal-600 dark:text-teal-400">
                                        <span>Security Deposit:</span>
                                        <span>+₱{{ number_format($booking->security_deposit, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->fuel_charge > 0)
                                    <div class="flex justify-end gap-2 text-orange-600 dark:text-orange-400">
                                        <span>Fuel:</span>
                                        <span>+₱{{ number_format($booking->fuel_charge, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->out_of_bounds > 0)
                                    <div class="flex justify-end gap-2 text-red-600 dark:text-red-400">
                                        <span>Out of Bounds:</span>
                                        <span>+₱{{ number_format($booking->out_of_bounds, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->rfid > 0)
                                    <div class="flex justify-end gap-2 text-pink-600 dark:text-pink-400">
                                        <span>RFID:</span>
                                        <span>+₱{{ number_format($booking->rfid, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->damages > 0)
                                    <div class="flex justify-end gap-2 text-red-700 dark:text-red-500 font-semibold">
                                        <span>Damages:</span>
                                        <span>+₱{{ number_format($booking->damages, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->carwash_fee > 0)
                                    <div class="flex justify-end gap-2 text-gray-500 dark:text-gray-400">
                                        <span>Carwash:</span>
                                        <span>+₱{{ number_format($booking->carwash_fee, 2) }}</span>
                                    </div>
                                @endif
                                @if($booking->discount > 0)
                                    <div class="flex justify-end gap-2 text-green-700 dark:text-green-500 font-semibold">
                                        <span>Discount:</span>
                                        <span>-₱{{ number_format($booking->discount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                <div class="flex justify-end gap-2 text-green-600 dark:text-green-400 font-medium">
                                    <span>Paid:</span>
                                    <span>₱{{ number_format($booking->paid_amount, 2) }}</span>
                                </div>
                                @if($booking->balance > 0)
                                    <div class="flex justify-end gap-2 text-red-600 dark:text-red-400 font-semibold">
                                        <span>Balance:</span>
                                        <span>₱{{ number_format($booking->balance, 2) }}</span>
                                    </div>
                                @elseif($booking->balance < 0)
                                    <div class="flex justify-end gap-2 text-green-600 dark:text-green-400 font-semibold">
                                        <span>Overpayment:</span>
                                        <span>₱{{ number_format(abs($booking->balance), 2) }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-end gap-2 text-green-600 dark:text-green-400 font-semibold">
                                        <span>Status:</span>
                                        <span>Fully Paid ✓</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-purple-600 dark:text-purple-400 font-semibold">
                            ₱{{ number_format($booking->partner_commission ?? 0, 2) }}
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                {{ $booking->total_due > 0 ? round(($booking->partner_commission / $booking->total_due) * 100, 1) : 0 }}% of total
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-blue-600 dark:text-blue-400 font-semibold">
                            ₱{{ number_format($booking->company_earnings ?? 0, 2) }}
                            <!-- Use historical commission data from the booking -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                @if($booking->commission_type && $booking->commission_value)
                                    {{ $booking->commission_type === 'percentage' 
                                        ? $booking->commission_value . '%' 
                                        : '₱' . number_format($booking->commission_value, 2) }}
                                    @if($booking->commission_base === 'total_due')
                                        (on total)
                                    @elseif($booking->commission_base === 'rent_only')
                                        (on rent)
                                    @endif
                                    <br>
                                    {{ $booking->total_due > 0 ? round(($booking->company_earnings / $booking->total_due) * 100, 1) : 0 }}% of total
                                @else
                                    <span class="text-gray-400">No commission data</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $booking->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                   ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                   ($booking->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')) }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No bookings found
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($bookings->count() > 0)
                <tfoot class="bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Totals:
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($bookings->sum('total_due'), 2) }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($bookings->sum('partner_commission'), 2) }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-purple-600 dark:text-purple-400">
                            ₱{{ number_format($bookings->sum('company_earnings'), 2) }}
                        </td>
                        <td colspan="1"></td>
                        <td colspan="1"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($bookings as $booking)
            <div class="p-4 space-y-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                <!-- Header: Booking ID + Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            #{{ $booking->booking_id }}
                        </span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $booking->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                               ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                               ($booking->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                               'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')) }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        ₱{{ number_format($booking->total_due, 2) }}
                    </div>
                </div>

                <!-- Renter & Vehicle -->
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Renter</span>
                        <div class="text-gray-900 dark:text-white font-medium">{{ $booking->renter_name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->contact_number }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Vehicle</span>
                        <div class="text-gray-900 dark:text-white font-medium">{{ $booking->car->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <!-- Date & Time with Duration -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Start</span>
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ $booking->start_datetime->format('M d, Y g:i A') }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">End</span>
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ $booking->end_datetime->format('M d, Y g:i A') }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm border-t border-gray-200 dark:border-gray-600 pt-1 mt-1">
                        <span class="text-gray-500 dark:text-gray-400">Duration</span>
                        <span class="text-blue-600 dark:text-blue-400 font-medium">
                            @php
                                $start = Carbon\Carbon::parse($booking->start_datetime);
                                $end = Carbon\Carbon::parse($booking->end_datetime);
                                $diffInHours = $start->diffInHours($end);
                                $diffInDays = $start->diffInDays($end);
                            @endphp
                            @if($diffInDays > 0)
                                {{ $diffInDays }} {{ Str::plural('day', $diffInDays) }}
                                @if($diffInHours % 24 > 0)
                                    , {{ $diffInHours % 24 }} {{ Str::plural('hr', $diffInHours % 24) }}
                                @endif
                            @else
                                {{ $diffInHours }} {{ Str::plural('hr', $diffInHours) }}
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Base Rent</span>
                        <span class="text-gray-900 dark:text-white">₱{{ number_format($booking->total_rent_due, 2) }}</span>
                    </div>
                    
                    @if($booking->extend_hours > 0)
                        <div class="flex justify-between text-sm text-yellow-600 dark:text-yellow-400">
                            <span>Extend Hours</span>
                            <span>+₱{{ number_format($booking->extend_due ?? 0, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->delivery_fee > 0)
                        <div class="flex justify-between text-sm text-blue-600 dark:text-blue-400">
                            <span>Delivery Fee</span>
                            <span>+₱{{ number_format($booking->delivery_fee, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->with_driver && $booking->driver_fee > 0)
                        <div class="flex justify-between text-sm text-indigo-600 dark:text-indigo-400">
                            <span>Driver Fee</span>
                            <span>+₱{{ number_format($booking->driver_fee, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->insurance > 0)
                        <div class="flex justify-between text-sm text-green-600 dark:text-green-400">
                            <span>Insurance</span>
                            <span>+₱{{ number_format($booking->insurance, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->security_deposit > 0)
                        <div class="flex justify-between text-sm text-teal-600 dark:text-teal-400">
                            <span>Security Deposit</span>
                            <span>+₱{{ number_format($booking->security_deposit, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->fuel_charge > 0)
                        <div class="flex justify-between text-sm text-orange-600 dark:text-orange-400">
                            <span>Fuel Charge</span>
                            <span>+₱{{ number_format($booking->fuel_charge, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->out_of_bounds > 0)
                        <div class="flex justify-between text-sm text-red-600 dark:text-red-400">
                            <span>Out of Bounds</span>
                            <span>+₱{{ number_format($booking->out_of_bounds, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->rfid > 0)
                        <div class="flex justify-between text-sm text-pink-600 dark:text-pink-400">
                            <span>RFID</span>
                            <span>+₱{{ number_format($booking->rfid, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->damages > 0)
                        <div class="flex justify-between text-sm text-red-700 dark:text-red-500 font-semibold">
                            <span>Damages</span>
                            <span>+₱{{ number_format($booking->damages, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->carwash_fee > 0)
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>Carwash Fee</span>
                            <span>+₱{{ number_format($booking->carwash_fee, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($booking->discount > 0)
                        <div class="flex justify-between text-sm text-green-700 dark:text-green-500 font-semibold">
                            <span>Discount</span>
                            <span>-₱{{ number_format($booking->discount, 2) }}</span>
                        </div>
                    @endif
                    
                    <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                    
                    <div class="flex justify-between text-sm font-semibold">
                        <span class="text-gray-900 dark:text-white">Total Due</span>
                        <span class="text-gray-900 dark:text-white">₱{{ number_format($booking->total_due, 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between text-sm text-green-600 dark:text-green-400">
                        <span>Paid</span>
                        <span>₱{{ number_format($booking->paid_amount, 2) }}</span>
                    </div>
                    
                    @if($booking->balance > 0)
                        <div class="flex justify-between text-sm text-red-600 dark:text-red-400 font-semibold">
                            <span>Balance</span>
                            <span>₱{{ number_format($booking->balance, 2) }}</span>
                        </div>
                    @elseif($booking->balance < 0)
                        <div class="flex justify-between text-sm text-green-600 dark:text-green-400 font-semibold">
                            <span>Overpayment</span>
                            <span>₱{{ number_format(abs($booking->balance), 2) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between text-sm text-green-600 dark:text-green-400 font-semibold">
                            <span>Payment Status</span>
                            <span>Fully Paid ✓</span>
                        </div>
                    @endif
                </div>

                <!-- Partner Earnings & Company Commission -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-2 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Partner Earnings</div>
                        <div class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                            ₱{{ number_format($booking->partner_commission ?? 0, 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $booking->total_due > 0 ? round(($booking->partner_commission / $booking->total_due) * 100, 1) : 0 }}% of total
                        </div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2 text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Company Commission</div>
                        <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            ₱{{ number_format($booking->company_earnings ?? 0, 2) }}
                        </div>
                        <!-- Use historical commission data from the booking -->
                        @if($booking->commission_type && $booking->commission_value)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $booking->commission_type === 'percentage' 
                                    ? $booking->commission_value . '%' 
                                    : '₱' . number_format($booking->commission_value, 2) }}
                                @if($booking->commission_base === 'total_due')
                                    (on total)
                                @elseif($booking->commission_base === 'rent_only')
                                    (on rent)
                                @endif
                            </div>
                        @else
                            <div class="text-xs text-gray-400">No commission data</div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                No bookings found
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-sm text-gray-500 dark:text-gray-400 order-2 sm:order-1">
            Showing {{ $bookings->firstItem() ?? 0 }} to {{ $bookings->lastItem() ?? 0 }} of {{ $bookings->total() }} bookings
        </div>
        <div class="order-1 sm:order-2 w-full sm:w-auto">
            {{ $bookings->links() }}
        </div>
    </div>
</div>