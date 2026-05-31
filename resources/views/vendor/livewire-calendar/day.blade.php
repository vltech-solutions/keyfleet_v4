<div
    ondragenter="onLivewireCalendarEventDragEnter(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragleave="onLivewireCalendarEventDragLeave(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragover="onLivewireCalendarEventDragOver(event);"
    ondrop="onLivewireCalendarEventDrop(event, '{{ $componentId }}', '{{ $day }}', {{ $day->year }}, {{ $day->month }}, {{ $day->day }}, '{{ $dragAndDropClasses }}');"
    class="flex-1 h-auto min-h-[10rem] -mt-px -ml-px border border-gray-200"
    style="min-width: 10rem; min-height: 10rem;"
>
    <div class="w-full h-full" id="{{ $componentId }}-{{ $day }}">
        <div
            @if($dayClickEnabled)
                wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
            @endif
            class="w-full h-full p-2 flex flex-col items-start 
                {{ $dayInMonth 
                        ? ($isToday 
                            ? 'bg-white dark:bg-gray-800' 
                            : 'bg-gray-50 dark:bg-gray-900') 
                        : 'bg-gray-100 dark:bg-gray-700' 
                }} 
                dark:text-white"
            style="min-width: 10rem; min-height: 10rem;"
            >
            <div class="flex items-center">
                <p class="text-xl {{ $dayInMonth ? 'font-medium' : '' }}">
                    {{ $day->format('j') }}
                </p>
            </div>

            <div class="flex-1 w-full mt-2 space-y-1">
                <div
                    @class([
                        'grid gap-1',
                        'grid-cols-1' => count($events) <= 2,
                        'grid-cols-2' => count($events) > 2 && count($events) <= 6,
                        'grid-cols-3' => count($events) > 6 && count($events) <= 12,
                        'grid-cols-4' => count($events) > 12,
                    ])
                >
                    @foreach($events as $event)
                        <div
                            @if($dragAndDropEnabled)
                                draggable="true"
                            @endif
                            ondragstart="onLivewireCalendarEventDragStart(event, '{{ $event['id'] }}')"
                        >
                            @include($eventView, ['event' => $event])
                        </div>
                    @endforeach
                </div>
            </div>


        </div>
    </div>
</div>
