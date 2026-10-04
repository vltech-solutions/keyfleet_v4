<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
        @foreach($monthGrid->first() as $day)<div class="px-1 py-3 text-center text-[10px] font-bold uppercase text-gray-500 sm:text-xs">{{ $day->format('D') }}</div>@endforeach
    </div>
    <div class="calendar-month-grid grid grid-cols-7">
        @foreach($monthGrid as $week)
            @foreach($week as $day)
                @php $dayEvents=$getEventsForDay($day); $dayKey=$day->toDateString(); @endphp
                <div @class([
                    'calendar-day relative min-h-[7rem] border-b border-r border-gray-200 p-1.5 outline-none transition sm:min-h-[10rem] sm:p-2 lg:min-h-[13rem]',
                    'bg-white dark:bg-gray-900'=>$day->isSameMonth($startsAt),
                    'bg-gray-50/80 text-gray-400 dark:bg-gray-950/50'=>!$day->isSameMonth($startsAt),
                    'ring-2 ring-inset ring-primary-500'=>$selectedDate===$dayKey,
                ]) role="button" tabindex="0"
                    wire:click="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})"
                    wire:keydown.enter="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})"
                    aria-label="{{ $day->format('F j, Y') }}, {{ $dayEvents->count() }} bookings">
                    <div class="mb-1 flex items-center justify-between">
                        <span @class(['flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold sm:text-sm','bg-primary-600 text-white'=>$day->isToday(),'text-gray-700 dark:text-gray-200'=>!$day->isToday()])>{{ $day->day }}</span>
                        @if($dayEvents->isNotEmpty())<span class="text-[10px] font-semibold text-gray-400">{{ $dayEvents->count() }}</span>@endif
                    </div>
                    @if($viewMode==='visual')
                        <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                            @foreach($dayEvents->take(6) as $event)
                                <button type="button" class="calendar-visual-event relative min-h-10 overflow-hidden rounded-lg bg-gray-50 p-0.5 hover:ring-2 hover:ring-primary-400 dark:bg-white/5" wire:click.stop="onEventClick({{ $event['id'] }},'{{ $event['date'] }}')" title="{{ $event['car_name'] }} - {{ ucfirst($event['status']) }}">
                                    <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }} {{ $event['plate_number'] }}" loading="lazy" class="h-10 w-full object-contain sm:h-14">
                                    <span class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full border border-white bg-primary-500"><span class="sr-only">{{ ucfirst($event['status']) }}</span></span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-1">
                            @foreach($dayEvents->take(6) as $event)
                                <button type="button" class="calendar-operation-event block w-full rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-left dark:border-white/10 dark:bg-white/5" wire:click.stop="onEventClick({{ $event['id'] }},'{{ $event['date'] }}')">
                                    <span class="block truncate text-[11px] font-bold text-gray-900 dark:text-white">{{ $event['car_name'] }}</span>
                                    <span class="block truncate text-[10px] text-gray-500">{{ $event['plate_number'] }} - {{ ucfirst($event['status']) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @if($dayEvents->count()>2)<button class="mobile-more mt-1 w-full rounded-md bg-primary-50 py-1 text-[10px] font-bold text-primary-700 sm:hidden" wire:click.stop="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})">+{{ $dayEvents->count()-2 }} more</button>@endif
                    @if($dayEvents->count()>4)<button class="tablet-more mt-1 hidden w-full rounded-md bg-primary-50 py-1 text-[10px] font-bold text-primary-700 sm:block xl:hidden" wire:click.stop="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})">+{{ $dayEvents->count()-4 }} more</button>@endif
                    @if($dayEvents->count()>6)<button class="desktop-more mt-1 hidden w-full rounded-md bg-primary-50 py-1 text-xs font-bold text-primary-700 xl:block" wire:click.stop="onDayClick({{ $day->year }},{{ $day->month }},{{ $day->day }})">+{{ $dayEvents->count()-6 }} more</button>@endif
                </div>
            @endforeach
        @endforeach
    </div>
    @if($events->isEmpty())<div class="p-8 text-center text-sm text-gray-500">No bookings match the selected month and filters.</div>@endif
    <style>
        .calendar-visual-event:nth-child(n+3),.calendar-operation-event:nth-child(n+3){display:none}
        @media(min-width:640px){.calendar-visual-event:nth-child(-n+4),.calendar-operation-event:nth-child(-n+4){display:block}}
        @media(min-width:1280px){.calendar-visual-event:nth-child(-n+6),.calendar-operation-event:nth-child(-n+6){display:block}}
    </style>
</section>
