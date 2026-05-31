<div>
    <h1 class="mb-6 text-lg font-semibold text-gray-700 dark:text-gray-200">Upcoming Bookings</h1>

    @if(count($upcomingBookings) > 0)
        @foreach($upcomingBookings as $booking)
            <div class="flex items-center gap-3 p-2 mb-4 transition-shadow bg-white shadow rounded-xl dark:bg-gray-800 hover:shadow-md">
                <!-- Car icon with circle background -->
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-primary-500/10">
                    {{-- <x-lucide-calendar-check class="w-8 h-8 text-xl text-primary-500" />--}}
                    @php
                        $imagePath = $booking->car->image;
                        $imageUrl = ($booking->car->image && Storage::disk('public')->exists($imagePath))
                            ? Storage::url($imagePath)
                            : Storage::url('images/default-car.png');
                    @endphp
                    <img
                        src="{{ $imageUrl }}"
                        class="ml-3"
                        alt="Car Image"
                        loading="lazy"
                    /> 
                </div>

                <!-- Renter info -->
                <div class="flex-1 ml-4">
                    <div class="text-base font-semibold text-gray-800 dark:text-white">
                        {{ $booking->renter_name }}
                    </div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ \Carbon\Carbon::parse($booking->start_datetime)->format('M d, Y h:i A') }}</span>
                        <span class="mx-1">→</span>
                        <span>{{ \Carbon\Carbon::parse($booking->end_datetime)->format('M d, Y h:i A') }}</span><br/>
                        <span class="px-2 mt-1 text-xs bg-gray-100 rounded-full dark:bg-gray-700">
                            @php
                                $start = \Carbon\Carbon::parse($booking->start_datetime);
                                $end = \Carbon\Carbon::parse($booking->end_datetime);

                                // Base duration
                                $duration = $start->diffInHours($end) > 24
                                    ? round($start->diffInDays($end)) . ' day' . ($start->diffInDays($end) > 1 ? 's' : '')
                                    : $start->diffInHours($end) . ' hour' . ($start->diffInHours($end) > 1 ? 's' : '');

                                // Append extend hours kung meron
                                if ($booking->extend_hours > 0 && $start->diffInHours($end) > 24) {
                                    $duration .= ' & ' . $booking->extend_hours . ' hour' . ($booking->extend_hours > 1 ? 's' : '');
                                }
                            @endphp

                            {{ $duration }}

                            {{-- {{ $diff = \Carbon\Carbon::parse($booking->start_datetime)->diffInDays(\Carbon\Carbon::parse($booking->end_datetime)) }} --}}
                            {{-- {{ $diff === 1 ? 'day' : 'days' }} --}}
                        </span>
                    
                    </div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <x-lucide-car class="w-5 h-5 text-gray-500" />
                            {{ $booking->car->name }}
                        </span>
                        
                    </div>
                </div>

                <!-- Right arrow icon (optional, for visual hint) -->
                {{-- <x-lucide-chevron-right class="w-5 h-5 text-gray-400 dark:text-gray-500" /> --}}
            </div>
        @endforeach
    @else
        <div class="flex flex-col items-center justify-center h-[250px] gap-2 p-6 text-center text-gray-500 border border-dashed rounded-xl dark:text-gray-400 dark:border-gray-700">
            <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm font-medium">No upcoming booking</p>
        </div>
    @endif
</div>
