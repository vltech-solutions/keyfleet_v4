<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Bookings</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">All your confirmed rental bookings</p>
        </div>
        <div class="flex items-center gap-2">
           
        </div>
    </div>

    <!-- Bookings List - Responsive Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium text-gray-900 dark:text-white">{{ $bookings->count() }}</span> bookings
                </span>
            </div>
        </div>
        <div class="p-4">
            @if($bookings->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($bookings as $booking)
                        @php
                            // Determine status display
                            $status = $booking->status ?? 'pending';
                            $isPast = $booking->end_datetime && $booking->end_datetime->isPast();
                            
                            // If status is approved and end date is past, show as completed
                            if ($status === 'approved' && $isPast) {
                                $displayStatus = 'Completed';
                                $statusClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                            } elseif ($status === 'approved') {
                                $displayStatus = 'Approved';
                                $statusClass = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                            } elseif ($status === 'cancelled') {
                                $displayStatus = 'Cancelled';
                                $statusClass = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                            } elseif ($status === 'completed') {
                                $displayStatus = 'Completed';
                                $statusClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                            } else {
                                $displayStatus = ucfirst($status);
                                $statusClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                            }
                        @endphp
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow overflow-hidden flex flex-col">
                            <!-- Car Image -->
                            <div class="relative w-full aspect-[4/3] bg-gray-200 dark:bg-gray-600">
                                @php
                                    $thumbnail = $booking->car->images->firstWhere('image_type', 'thumbnail') ?? $booking->car->images->first();
                                    $imageUrl = $thumbnail && !empty($thumbnail->path) 
                                        ? Storage::disk('s3')->temporaryUrl($thumbnail->path, now()->addMinutes(5))
                                        : ($booking->car->image ? Storage::url($booking->car->image) : null);
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $booking->car->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h8M4 4h16v16H4z"/>
                                        </svg>
                                    </div>
                                @endif
                                
                                <!-- Status Badge - Top Right -->
                                <div class="absolute top-3 right-3 flex flex-col gap-1.5">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full shadow-sm {{ $statusClass }}">
                                        {{ $displayStatus }}
                                    </span>
                                    @if($isPast && $status === 'approved')
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300 shadow-sm">
                                            Completed
                                        </span>
                                    @endif
                                </div>

                                <!-- Days Badge - Bottom Right -->
                                @if($booking->start_datetime && $booking->end_datetime)
                                    <div class="absolute bottom-3 right-3 px-3 py-1.5 bg-black/70 backdrop-blur-sm rounded-lg text-white text-xs font-medium">
                                        {{ $booking->start_datetime->diffInDays($booking->end_datetime) }} day(s)
                                    </div>
                                @endif
                            </div>

                            <!-- Booking Details -->
                            <div class="p-4 flex-1 flex flex-col">
                                <div class="flex-1">
                                    <!-- Car Name & Booking ID -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">
                                                {{ $booking->car->name ?? 'Unknown Vehicle' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $booking->booking_id ?? $booking->id }}
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 text-right flex-shrink-0">
                                            <span>{{ $booking->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>

                                    <!-- Period -->
                                    @if($booking->start_datetime && $booking->end_datetime)
                                        <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="truncate">
                                                    {{ $booking->start_datetime->format('M d, h:i A') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="truncate">
                                                    {{ $booking->end_datetime->format('M d, h:i A') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Additional Info -->
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        @if($booking->destination)
                                            <span class="truncate max-w-[80px]">📍 {{ Str::limit($booking->destination, 15) }}</span>
                                        @endif
                                        @if($booking->with_driver)
                                            <span class="text-blue-600 dark:text-blue-400">🚗 Driver</span>
                                        @endif
                                        @if($booking->total_due > 0)
                                            <span class="text-amber-600 dark:text-amber-400 font-medium">
                                                ₱{{ number_format($booking->total_due, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($booking->reservation && $booking->reservation->reservation_fee_receipt)
                                            <a href="{{ Storage::disk('s3')->temporaryUrl($booking->reservation->reservation_fee_receipt, now()->addMinutes(15)) }}" 
                                               target="_blank"
                                               class="px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition whitespace-nowrap">
                                                Receipt
                                            </a>
                                        @endif
                                        
                                        <!-- Download Invoice Button -->
                                        @if($booking->status === 'approved' || $booking->status === 'completed')
                                            <button wire:click="downloadInvoice({{ $booking->id }})" 
                                                    class="px-2.5 py-1 text-xs font-medium text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Invoice
                                            </button>
                                        @endif
                                        
                                        <a href="#" 
                                           class="px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition whitespace-nowrap">
                                            View Details
                                        </a>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400 whitespace-nowrap">
                                            {{ $booking->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No bookings found</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">You haven't made any confirmed bookings yet.</p>
                    <a href="/" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                        Browse Vehicles
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>