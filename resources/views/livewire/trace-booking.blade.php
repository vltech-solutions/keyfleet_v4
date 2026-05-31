<div>
   

    <form wire:submit.prevent="submit" class="space-y-4">
        @csrf

        {{-- Flash messages --}}
        @php
        $alerts = [
            'error' => [
                'light' => 'text-red-700 bg-red-100',
                'dark'  => 'dark:bg-red-900 dark:text-red-100',
            ],
            'danger' => [
                'light' => 'text-red-700 bg-red-100',
                'dark'  => 'dark:bg-red-900 dark:text-red-100',
            ],
            'warning' => [
                'light' => 'text-yellow-700 bg-yellow-100',
                'dark'  => 'dark:bg-yellow-900 dark:text-yellow-100',
            ],
            'success' => [
                'light' => 'text-green-700 bg-green-100',
                'dark'  => 'dark:bg-green-900 dark:text-green-100',
            ],
        ];
        @endphp

        @foreach ($alerts as $msg => $classes)
            @if (session()->has($msg))
                <div class="p-3 text-sm rounded-lg {{ $classes['light'] }} {{ $classes['dark'] }}">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach


        {{-- Reservation Number --}}
        <div>
            <label for="reservation_number" class="block text-sm font-medium text-gray-700 dark:text-gray-500">
                Reservation Number
            </label>
            <input type="text" id="reservation_number" wire:model="reservation_number"
                class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-[var(--tw-primary)] focus:ring-[var(--tw-primary)] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                required>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end space-x-2">
            <button type="button" 
                    @click="openTrace = false;"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                Cancel
            </button>
            <button type="submit" 
                    class="px-4 py-2 text-sm font-semibold text-white rounded-lg shadow bg-[var(--tw-primary)] hover:opacity-90">
                Submit
            </button>
        </div>
    </form>

    <!-- Invoice Modal -->
    <div
        x-data="{ open: @entangle('showInvoiceModal') }"
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        x-cloak
    >
        
        <div class="w-full max-w-3xl p-6 bg-white shadow-2xl rounded-2xl dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <!-- Heroicon Component -->
                    <x-heroicon-s-check-circle class="flex-shrink-0 w-10 h-10 text-green-500" />

                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Booking Approved</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Your reservation was approved. Please review the booking details and download your invoice.
                        </p>
                    </div>
                </div>

                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    ✕
                </button>
            </div>


            <!-- Booking Details -->
            @if($showInvoiceModal && isset($invoiceData['booking']->id))
                <div class="p-4 mb-6 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Renter</dt>
                            <dd class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                {{ $invoiceData['booking']->renter_name }}<br/>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $invoiceData['booking']->contact_number }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Car</dt>
                            <dd class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                {{ $invoiceData['booking']->car->brand.' '.$invoiceData['booking']->car->model.' '.$invoiceData['booking']->car->year }}<br/>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $invoiceData['booking']->car->name }} ({{ $invoiceData['booking']->car->plate_number }})</span>
                            </dd>
                        </div>
                        <div>
                            {{-- <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rental Period</dt>
                            <dd class="text-md font-semibold text-gray-800 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($invoiceData['booking']->start_datetime)->format('M d, Y h:i A') }}
                                –
                                {{ \Carbon\Carbon::parse($invoiceData['booking']->end_datetime)->format('M d, Y h:i A') }}
                            </dd> --}}
                            <div class="mt-2 space-y-4 text-gray-500 dark:text-gray-400">

                                <!-- Pickup -->
                                <div class="space-y-1">
                                    <span class="text-sm font-medium text-gray-400 dark:text-gray-500">Pickup:</span>
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-calendar class="w-6 h-6"/>
                                        <span class="font-semibold">{{ \Carbon\Carbon::parse($invoiceData['booking']->start_datetime)->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-map-pin class="w-6 h-6"/>
                                        <span>{{ $invoiceData['booking']->pickup_address ?? 'Garage' }}</span>
                                    </div>
                                </div>

                                <!-- Return -->
                                <div class="space-y-1">
                                    <span class="text-sm font-medium text-gray-400 dark:text-gray-500">Return:</span>
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-calendar class="w-6 h-6"/>
                                        <span class="font-semibold">{{ \Carbon\Carbon::parse($invoiceData['booking']->end_datetime)->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-map-pin class="w-6 h-6"/>
                                        <span>{{ $invoiceData['booking']->return_address ?? 'Garage' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php

                        $start = $invoiceData['booking']->start_datetime ?? null;
                        $end = $invoiceData['booking']->end_datetime ?? null;
                        $duration = '-';

                        if ($start && $end) {
                            $startCarbon = \Carbon\Carbon::parse($start);
                            $endCarbon = \Carbon\Carbon::parse($end);

                            $diffInHours = $startCarbon->diffInHours($endCarbon);

                            if ($diffInHours < 24) {
                                $duration = '1 day';
                                $daysRented = 1;
                            } else {
                                $days = floor($diffInHours / 24);
                                $extendHours = $diffInHours % 24;

                                $daysRented = $days;
                                $duration = $days . ' day' . ($days > 1 ? 's' : '');
                                if ($extendHours > 0) {
                                    $duration .= " and {$extendHours} hr" . ($extendHours > 1 ? 's' : '');
                                }
                            }
                        } else {
                            $daysRented = 0;
                        }

                        $perDayRate = $invoiceData['booking']->rate_per_day ?? 0;
                        $totalDue = $invoiceData['booking']->total_due ?? 0;
                    @endphp

                    <div class="mt-4">
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $duration }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Daily Rate</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">₱{{ number_format($invoiceData['booking']->daily_rate, 2) }}</dd>
                            </div>
                            
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Delivery Fee</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">₱{{ number_format($invoiceData['booking']->delivery_fee, 2) }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Total Due</dt>
                                <dd class="text-lg font-semibold text-red-600 dark:text-red-400">
                                    ₱{{ number_format($totalDue, 2) }}
                                </dd>
                            </div>
                        </dl>
                        <span class="text-xs text-gray-500 dark:text-gray-400">*Download Invoice for more details.</span>
                    </div>

                    </dl>
                </div>

                <!-- Download Button -->
                <div class="flex justify-center">
                    <a 
                        href="{{ route('invoices.download', $invoiceData['booking']->id) }}" 
                        target="_blank"
                        class="inline-flex items-center px-6 py-3 text-base font-medium text-white transition-colors shadow-lg rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800"
                        style="background-color: var(--tw-primary);"
                    >
                        <!-- Heroicon: Document Download -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-3-3m3 3l3-3m-9 8h12" />
                        </svg>
                        Download Invoice
                    </a>
                </div>
            @else
                <div class="text-center text-gray-500 dark:text-gray-400">
                    No invoice available.
                </div>
            @endif
        </div>
    </div>

    
</div>
