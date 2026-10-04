@php
    $anchor = $startsAt->copy()->startOfWeek((int) $weekStartsAt);
    if ($selectedDate) {
        [$selectedYear, $selectedMonth, $selectedDay] = array_map('intval', explode('-', $selectedDate));
        $anchor->setDate($selectedYear, $selectedMonth, $selectedDay)->startOfWeek((int) $weekStartsAt);
    }
    $weekDays = collect(range(0, 6))->map(fn ($offset) => $anchor->copy()->addDays($offset));
@endphp
<section class="grid gap-3 lg:grid-cols-7">
    @foreach($weekDays as $day)
        @php $dayEvents = $events->where('date', $day->toDateString())->unique('id')->values(); @endphp
        <div class="min-h-48 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <button type="button" wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})" class="mb-3 w-full text-left">
                <span class="block text-xs font-bold uppercase text-gray-400">{{ $day->format('D') }}</span>
                <span @class(['mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full text-lg font-black','bg-primary-600 text-white'=>$day->isToday(),'text-gray-950 dark:text-white'=>!$day->isToday()])>{{ $day->day }}</span>
            </button>
            <div class="space-y-2">
                @forelse($dayEvents as $event)
                    <button type="button" wire:click="onEventClick({{ $event['id'] }}, '{{ $event['date'] }}')" class="w-full rounded-xl bg-gray-50 p-2 text-left hover:ring-2 hover:ring-primary-400 dark:bg-white/5">
                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" loading="lazy" class="h-14 w-full object-contain">
                        <span class="mt-1 block truncate text-xs font-bold text-gray-900 dark:text-white">{{ $event['car_name'] }}</span>
                        <span class="block truncate text-[10px] text-gray-500">{{ $event['plate_number'] }} - {{ ucfirst($event['status']) }}</span>
                    </button>
                @empty
                    <p class="py-6 text-center text-xs text-gray-400">No bookings</p>
                @endforelse
            </div>
        </div>
    @endforeach
</section>
