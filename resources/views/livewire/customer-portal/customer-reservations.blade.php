<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Reservations</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View all your reservations and their status</p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="statusFilter" 
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="declined">Declined</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Reservations List - Responsive Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium text-gray-900 dark:text-white">{{ $reservations->count() }}</span> reservations
                </span>
            </div>
        </div>
        <div class="p-4">
            @if($reservations->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($reservations as $reservation)
                        @php
                            // Check if reservation is past
                            $isPast = $reservation->end_date && $reservation->end_date->isPast();
                            // Check if can be cancelled (pending, not past, not already cancelled/declined)
                            $canCancel = !$reservation->booking_id && 
                                        !$reservation->datetime_declined && 
                                        !$reservation->datetime_cancelled && 
                                        !$isPast;
                        @endphp
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow overflow-hidden flex flex-col">
                            <!-- Car Image -->
                            <div class="relative w-full aspect-[4/3] bg-gray-200 dark:bg-gray-600">
                                @php
                                    $thumbnail = $reservation->car->images->firstWhere('image_type', 'thumbnail') ?? $reservation->car->images->first();
                                    $imageUrl = $thumbnail && !empty($thumbnail->path) 
                                        ? \Storage::disk('s3')->temporaryUrl($thumbnail->path, now()->addMinutes(5))
                                        : ($reservation->car->image ? Storage::url($reservation->car->image) : null);
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $reservation->car->name }}"
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
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full shadow-sm
                                        @if($reservation->booking_id) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                        @elseif(!$reservation->booking_id && !$reservation->datetime_declined && !$reservation->datetime_cancelled) bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                        @elseif($reservation->datetime_declined) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                        @elseif($reservation->datetime_cancelled) bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        {{ $reservation->booking_id ? 'Approved' : ($reservation->datetime_declined ? 'Declined' : ($reservation->datetime_cancelled ? 'Cancelled' : 'Pending')) }}
                                    </span>
                                    @if($isPast && !$reservation->booking_id)
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300 shadow-sm">
                                            Expired
                                        </span>
                                    @endif
                                </div>

                                <!-- Days Badge - Bottom Right -->
                                <div class="absolute bottom-3 right-3 px-3 py-1.5 bg-black/70 backdrop-blur-sm rounded-lg text-white text-xs font-medium">
                                    {{ $reservation->start_date->diffInDays($reservation->end_date) }} day(s)
                                </div>
                            </div>

                            <!-- Reservation Details -->
                            <div class="p-4 flex-1 flex flex-col">
                                <div class="flex-1">
                                    <!-- Car Name & Reservation Number -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">
                                                {{ $reservation->car->name ?? 'Unknown Vehicle' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $reservation->reservation_number }}
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 text-right flex-shrink-0">
                                            <span>{{ $reservation->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>

                                    <!-- Period -->
                                    <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="truncate">
                                                {{ $reservation->start_date->format('M d, h:i A') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="truncate">
                                                {{ $reservation->end_date->format('M d, h:i A') }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        @if($reservation->pickup_address)
                                            <span class="truncate max-w-[80px]">📍 {{ Str::limit($reservation->pickup_address, 15) }}</span>
                                        @endif
                                        @if($reservation->with_driver)
                                            <span class="text-blue-600 dark:text-blue-400">🚗</span>
                                        @endif
                                        @if($reservation->reservation_fee > 0)
                                            <span class="text-amber-600 dark:text-amber-400 font-medium">
                                                ₱{{ number_format($reservation->reservation_fee, 2) }}
                                            </span>
                                        @endif
                                        @if($isPast)
                                            <span class="text-red-500 text-xs">Past</span>
                                        @endif
                                    </div>

                                    <!-- Cancellation/Decline Reason -->
                                    @if($reservation->datetime_cancelled && $reservation->cancellation_reason)
                                        <div class="mt-1.5 text-xs text-red-500 truncate">
                                            Reason: {{ Str::limit($reservation->cancellation_reason, 25) }}
                                        </div>
                                    @endif
                                    @if($reservation->datetime_declined && $reservation->decline_reason)
                                        <div class="mt-1.5 text-xs text-red-500 truncate">
                                            Reason: {{ Str::limit($reservation->decline_reason, 25) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5">
                                        @if($reservation->reservation_fee_receipt)
                                            <button wire:click="openReceiptModal({{ $reservation->id }})" 
                                                    class="px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition whitespace-nowrap">
                                                Receipt
                                            </button>
                                        @endif
                                        @if($reservation->booking_id)
                                            <a href="#" 
                                               class="px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition whitespace-nowrap">
                                                View Booking
                                            </a>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($canCancel)
                                            <button wire:click="openCancelModal({{ $reservation->id }})" 
                                                    class="px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition whitespace-nowrap">
                                                Cancel
                                            </button>
                                        @endif
                                        <span class="text-xs text-gray-400 whitespace-nowrap">
                                            {{ $reservation->updated_at->diffForHumans() }}
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
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No reservations found</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">You haven't made any reservations yet.</p>
                    <a href="/" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                        Browse Vehicles
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Cancel Reservation Modal -->
    @if($showCancelModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
            x-data="{ show: true }"
            x-init="setTimeout(() => { show = true }, 100)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="$wire.closeCancelModal()"
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;">
            
            <div class="bg-white dark:bg-gray-900 rounded-3xl max-w-md w-full shadow-2xl overflow-hidden relative z-[10000]">
                <!-- Header -->
                <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cancel Reservation</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Please tell us why you're cancelling</p>
                            </div>
                        </div>
                        <button @click="$wire.closeCancelModal()" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="p-6">
                    <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                        <p class="text-sm text-amber-700 dark:text-amber-300 flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>This action cannot be undone. Your reservation will be permanently cancelled.</span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Cancellation <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="cancellationReason" 
                                rows="4"
                                placeholder="Please provide a reason for cancelling your reservation..."
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all dark:text-white resize-none"></textarea>
                        @error('cancellationReason')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row gap-3">
                    <button @click="$wire.closeCancelModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">
                        Keep Reservation
                    </button>
                    <button wire:click="confirmCancelReservation" 
                            class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-medium flex items-center justify-center gap-2">
                        Yes, Cancel Reservation
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if($showReceiptModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
            x-data="{ show: true }"
            x-init="setTimeout(() => { show = true }, 100)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="$wire.closeReceiptModal()"
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;">
            
            <div class="bg-white dark:bg-gray-900 rounded-3xl max-w-4xl w-full max-h-[90vh] shadow-2xl overflow-hidden relative z-[10000]">
                <!-- Header -->
                <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Payment Receipt</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Reservation #{{ $receiptReservationNumber }}
                            </p>
                        </div>
                    </div>
                    <button @click="$wire.closeReceiptModal()" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Body - Image Display -->
                <div class="p-6 flex items-center justify-center min-h-[300px] bg-gray-50 dark:bg-gray-800/50">
                    @if($receiptImageUrl)
                        <img src="{{ $receiptImageUrl }}" 
                            alt="Payment Receipt for Reservation #{{ $receiptReservationNumber }}"
                            class="max-w-full max-h-[65vh] object-contain rounded-lg shadow-lg">
                    @else
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm">No receipt available</span>
                        </div>
                    @endif
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row justify-end gap-3">
                    <button @click="$wire.closeReceiptModal()" 
                            class="px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>