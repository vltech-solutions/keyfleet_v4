@php
    $detailDate=$selectedDate ?: now(config('app.timezone'))->toDateString();
    $dayEvents=$events->where('date',$detailDate)->unique('id')->values();
    $statusSummary=$dayEvents->countBy('status');
    $availableCount=max(count($carOptions)-$dayEvents->pluck('car_id')->unique()->count(),0);
@endphp
<div>
    <div class="mb-5 flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-primary-600">Day details</p>
            <h2 class="mt-1 text-2xl font-black text-gray-950 dark:text-white">{{ date('l, F j, Y', strtotime($detailDate)) }}</h2>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-gray-100 px-2.5 py-1 font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">{{ $dayEvents->count() }} bookings</span>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $availableCount }} fleet vehicles not booked</span>
                @foreach($statusSummary as $summaryStatus=>$count)<span class="rounded-full bg-primary-50 px-2.5 py-1 font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $count }} {{ ucfirst($summaryStatus) }}</span>@endforeach
            </div>
        </div>
        @if($panelMode)<button type="button" wire:click="closeDayPanel" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10" aria-label="Close day details"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
</svg>
</button>@endif
    </div>
    <div class="space-y-3">
        @forelse($dayEvents as $event)
            <article @class(['rounded-2xl border bg-white p-3 shadow-sm dark:bg-gray-950','border-primary-500 ring-2 ring-primary-500/20'=>$selectedBookingId===$event['id'],'border-gray-200 dark:border-white/10'=>$selectedBookingId!==$event['id']])>
                <div class="flex gap-3">
                    <div class="flex h-24 w-32 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-white/5"><img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="h-full w-full object-contain"></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div><h3 class="font-bold text-gray-950 dark:text-white">{{ $event['car_name'] }}</h3><p class="text-xs font-semibold text-gray-500">{{ $event['plate_number'] ?: 'No plate number' }}</p></div>
                            <span class="rounded-full bg-primary-50 px-2 py-1 text-[10px] font-bold uppercase text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $event['status'] }}</span>
                        </div>
                        <dl class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                            <div><dt class="inline font-semibold">Customer:</dt> <dd class="inline">{{ $event['renter_name'] }}</dd></div>
                            <div><dt class="inline font-semibold">Reference:</dt> <dd class="inline">{{ $event['booking_id'] }}</dd></div>
                            <div><dt class="inline font-semibold">Rental:</dt> <dd class="inline">{{ $event['start_label'] }} to {{ $event['end_label'] }}</dd></div>
                            @if($event['location'])<div><dt class="inline font-semibold">Pickup:</dt> <dd class="inline">{{ $event['location'] }}</dd></div>@endif
                            @if($event['return_location'])<div><dt class="inline font-semibold">Drop-off:</dt> <dd class="inline">{{ $event['return_location'] }}</dd></div>@endif
                        </dl>
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                    <a href="{{ $bookingEditBaseUrl.'/'.$event['id'].'/edit' }}" class="rounded-lg bg-primary-600 px-3 py-2 text-xs font-bold text-white">View Booking</a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center dark:border-white/10"><p class="font-semibold text-gray-800 dark:text-gray-200">No bookings scheduled for this date.</p><a href="{{ $newBookingUrl }}?start_datetime={{ $detailDate }}" class="mt-3 inline-block text-sm font-bold text-primary-600">+ New Booking</a></div>
        @endforelse
    </div>
</div>
