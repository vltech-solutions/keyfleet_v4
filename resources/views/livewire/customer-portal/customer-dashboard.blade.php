<div class="space-y-4 sm:space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
            Welcome back, {{ $customer->customer_name }}! 👋
        </h1>
        <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mt-1">
            Here's a summary of your bookings and reservations.
        </p>
    </div>

    <!-- Stats Grid - Responsive -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 lg:gap-4">
        <!-- Total -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Total</p>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Pending</p>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['approved'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Approved</p>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
            </div>
        </div>

        <!-- Declined -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['declined'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Declined</p>
                </div>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['cancelled'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity - Responsive -->
    <div class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Recent Activity</h2>
            <a href="{{ route('customer.bookings', $repeatToken) }}" 
               class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View all →
            </a>
        </div>
        <div class="p-3 sm:p-4">
            @if($recentItems->count() > 0)
                <div class="space-y-2 sm:space-y-3">
                    @foreach($recentItems as $item)
                        @php
                            // Determine if it's a reservation or booking
                            $isReservation = isset($item->reservation_number);
                            $isBooking = isset($item->booking_id);
                            
                            // Get car image
                            $thumbnail = $item->car->images->firstWhere('image_type', 'thumbnail') ?? $item->car->images->first();
                            $imageUrl = $thumbnail && !empty($thumbnail->path) 
                                ? Storage::disk('s3')->temporaryUrl($thumbnail->path, now()->addMinutes(5))
                                : ($item->car->image ? Storage::url($item->car->image) : null);
                            
                            // Get status
                            if ($isReservation) {
                                if ($item->booking_id) {
                                    $status = 'Approved';
                                    $statusClass = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                                } elseif ($item->datetime_declined) {
                                    $status = 'Declined';
                                    $statusClass = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                                } elseif ($item->datetime_cancelled) {
                                    $status = 'Cancelled';
                                    $statusClass = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                                } else {
                                    $status = 'Pending';
                                    $statusClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                                }
                            } else {
                                $status = ucfirst($item->status ?? 'pending');
                                $statusClass = match($item->status) {
                                    'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                };
                            }
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600 gap-2 sm:gap-0">
                            <div class="flex items-center gap-3">
                                <!-- Car Image -->
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" 
                                            alt="{{ $item->car->name ?? 'Car' }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h8M4 4h16v16H4z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm truncate">
                                        {{ $item->car->name ?? 'Unknown Vehicle' }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="truncate max-w-[80px] sm:max-w-none">#{{ $isReservation ? $item->reservation_number : $item->booking_id }}</span>
                                        <span>•</span>
                                        <span>
                                            @if($isReservation && $item->start_date)
                                                {{ $item->start_date->format('M d, Y') }}
                                            @elseif($isBooking && $item->start_datetime)
                                                {{ $item->start_datetime->format('M d, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                        <span class="hidden sm:inline">•</span>
                                        <span class="text-gray-400 hidden sm:inline">{{ $item->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pl-14 sm:pl-0">
                                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[10px] sm:text-xs font-medium rounded-full {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                                @if(($isReservation && $item->reservation_fee > 0) || ($isBooking && $item->total_due > 0))
                                    <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        @if($isReservation && $item->reservation_fee > 0)
                                            ₱{{ number_format($item->reservation_fee, 2) }}
                                        @elseif($isBooking && $item->total_due > 0)
                                            ₱{{ number_format($item->total_due, 2) }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 sm:py-8">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-300 dark:text-gray-600 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">No activity yet</p>
                    <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-1">Start your first booking today!</p>
                </div>
            @endif
        </div>
    </div>
</div>