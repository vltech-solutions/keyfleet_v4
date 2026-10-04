<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
        @foreach($monthGrid->first() as $day)<div class="px-1 py-3 text-center text-[10px] font-bold uppercase text-gray-500 sm:text-xs">{{ $day->format('D') }}</div>@endforeach
    </div>
    <div class="calendar-month-grid grid grid-cols-7">
        @foreach($monthGrid as $week)
            @foreach($week as $day)
                @php $dayEvents=$getEventsForDay($day); $dayKey=$day->toDateString(); $eventCount=$dayEvents->count(); @endphp
                <div @class([
                    'calendar-day relative min-h-[4.75rem] border-b border-r border-gray-200 p-1 outline-none transition sm:min-h-[6rem] lg:min-h-[13rem] lg:p-2',
                    'bg-white dark:bg-gray-900'=>$day->isSameMonth($startsAt),
                    'bg-gray-50/80 text-gray-400 dark:bg-gray-950/50'=>!$day->isSameMonth($startsAt),
                    'ring-2 ring-inset ring-primary-500'=>$selectedDate===$dayKey,
                ]) role="button" tabindex="0"
                    wire:click="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})"
                    wire:keydown.enter="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})"
                    aria-label="{{ $day->format('F j, Y') }}, {{ $eventCount }} bookings">
                    <div class="mb-1 flex items-center justify-between">
                        <span @class(['flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold sm:text-xs lg:h-7 lg:w-7 lg:text-sm','bg-primary-600 text-white'=>$day->isToday(),'text-gray-700 dark:text-gray-200'=>!$day->isToday()])>{{ $day->day }}</span>

                    </div>

                    <div class="mt-1 flex justify-center lg:hidden">
                        <span @class(['inline-flex min-w-7 items-center justify-center rounded-full px-1.5 py-1 text-[10px] font-bold sm:px-2 sm:text-xs','bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300'=>$eventCount>0,'bg-gray-100 text-gray-400 dark:bg-white/5'=>$eventCount===0])>
                            {{ $eventCount }}<span class="ml-1 hidden sm:inline">{{ $eventCount===1 ? 'booking' : 'bookings' }}</span>
                        </span>
                    </div>

                    @if($viewMode==='visual')
                        <div @class([
                            'hidden w-full items-center justify-items-center gap-1 lg:grid',
                            'grid-cols-1'=>$eventCount<=2,
                            'grid-cols-2'=>$eventCount>2 && $eventCount<=6,
                            'grid-cols-3'=>$eventCount>6 && $eventCount<=12,
                            'grid-cols-4'=>$eventCount>12,
                        ])>
                            @foreach($dayEvents as $event)
                                <button type="button" class="flex w-full items-center justify-center p-0.5" wire:click.stop="onEventClick({{ $event['id'] }},'{{ $event['date'] }}')" title="{{ $event['car_name'] }} - {{ ucfirst($event['status']) }}">
                                    @if($eventCount===1)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="mx-auto h-auto w-auto max-w-[90%] object-contain">
                                    @elseif($eventCount===2)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="mx-auto h-auto w-auto max-h-[50px] max-w-[110px] object-contain">
                                    @elseif($eventCount>12)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="mx-auto h-auto w-auto max-h-[18px] max-w-[35px] object-contain">
                                    @elseif($eventCount>6)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="mx-auto h-auto w-auto max-h-[25px] max-w-[50px] object-contain">
                                    @else
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="mx-auto h-auto w-auto max-h-[35px] max-w-[70px] object-contain">
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="hidden space-y-1 lg:block">
                            @foreach($dayEvents->take(6) as $event)
                                <button type="button" class="calendar-operation-event block w-full rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-left dark:border-white/10 dark:bg-white/5" wire:click.stop="onEventClick({{ $event['id'] }},'{{ $event['date'] }}')">
                                    <span class="block truncate text-[11px] font-bold text-gray-900 dark:text-white">{{ $event['car_name'] }}</span>
                                    <span class="block truncate text-[10px] text-gray-500">{{ $event['plate_number'] }} - {{ ucfirst($event['status']) }}</span>
                                </button>
                            @endforeach
                        </div>
                        @if($eventCount>4)<button class="mt-1 hidden w-full rounded-md bg-primary-50 py-1 text-[10px] font-bold text-primary-700 lg:block xl:hidden" wire:click.stop="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})">+{{ $eventCount-4 }} more</button>@endif
                        @if($eventCount>6)<button class="mt-1 hidden w-full rounded-md bg-primary-50 py-1 text-xs font-bold text-primary-700 xl:block" wire:click.stop="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})">+{{ $eventCount-6 }} more</button>@endif
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>
    @if($events->isEmpty())<div class="p-8 text-center text-sm text-gray-500">No bookings match the selected month and filters.</div>@endif
    <style>
        .calendar-operation-event:nth-child(n+3){display:none}
        @media(min-width:640px){.calendar-operation-event:nth-child(-n+4){display:block}}
        @media(min-width:1280px){.calendar-operation-event:nth-child(-n+6){display:block}}
    </style>
</section>
