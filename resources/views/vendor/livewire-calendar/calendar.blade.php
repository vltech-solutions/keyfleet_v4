<div
    @if($pollMillis !== null && $pollAction !== null)
        wire:poll.{{ $pollMillis }}ms="{{ $pollAction }}"
    @elseif($pollMillis !== null)
        wire:poll.{{ $pollMillis }}ms
    @endif
    
    x-data="{
    capturing: false,
    showPreview: false,
    previewImage: null,
    async capture() {
        this.capturing = true
        await this.$nextTick()

        const el = this.$refs.captureTargetHidden

        window.html2canvas(el, {
            scale: 2,
            useCORS: true
        }).then(canvas => {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

            if (isIOS) {
                const url = canvas.toDataURL('image/png');
                this.previewImage = url;
                this.showPreview = true;
            } else {
                canvas.toBlob(blob => {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'calendar.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }
            this.capturing = false
        })
    }
}"


>
<div x-show="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
    <div class="relative max-w-full max-h-full p-4 overflow-auto bg-white rounded shadow-lg">
        <button @click="showPreview = false" class="absolute text-lg text-gray-500 top-2 right-2 hover:text-red-500">×</button>
        <p class="mb-2 text-sm text-center text-gray-700">Tap and hold the image below to save it.</p>
        <img :src="previewImage" alt="Captured Image" class="w-full h-auto rounded" />
    </div>
</div>

    <div>
        @includeIf($beforeCalendarView)
    </div>
    
    <div class="" x-ref="captureTargetVisible"
        {{-- :class="{ 'min-w-[1440px]': capturing }" --}}
        class="p-3 dark:bg-gray-950 dark:text-white">
        <div class="flex items-center justify-between mb-4">
            <div class="text-6xl text-gray-800 dark:text-white tracking-tight font-extrabold mt-2">
                {{ $this->startsAt->format('F Y') }}
            </div>
            <div class="text-6xl font-bold text-gray-800 dark:text-white">
                {{-- <img src="{{ $companyLogo }}" alt="Company Logo" class="object-contain w-40 h-28"> --}}
            </div>
        </div>

        <br/>
        <div  
            class="flex w-full mt-4 print-calendar" >
            <div class="w-full overflow-x-auto">
                <div class="inline-block min-w-full overflow-hidden">

                    <div class="flex flex-row w-full border">
                        @foreach($monthGrid->first() as $day)
                            @include($dayOfWeekView, ['day' => $day])
                        @endforeach
                    </div>
                    @foreach($monthGrid as $week)
                        <div class="flex flex-row w-full border">
                            @foreach($week as $day)
                                @include($dayView, [
                                        'componentId' => $componentId,
                                        'day' => $day,
                                        'dayInMonth' => $day->isSameMonth($startsAt),
                                        'isToday' => $day->isToday(),
                                        'events' => $getEventsForDay($day, $events),
                                    ])
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div class="p-3 absolute -left-[9999px] top-0 w-[1200px] dark:bg-gray-950 dark:text-white" x-ref="captureTargetHidden"
        {{-- :class="{ 'min-w-[1440px]': capturing }" --}}
       >
        <div class="flex items-center justify-between mb-4">
            <div class="text-6xl text-gray-800 dark:text-white tracking-tight font-extrabold mt-2">
                {{ $this->startsAt->format('F Y') }}
            </div>
            <div class="text-6xl font-bold text-gray-800 dark:text-white">
                {{-- <img src="{{ $companyLogo }}" alt="Company Logo" class="object-contain w-40 h-28"> --}}
            </div>
        </div>

        <br/>
        <div  
            class="flex w-full mt-4 print-calendar" >
            <div class="w-full overflow-x-auto">
                <div class="inline-block min-w-full overflow-hidden">

                    <div class="flex flex-row w-full border">
                        @foreach($monthGrid->first() as $day)
                            @include($dayOfWeekView, ['day' => $day])
                        @endforeach
                    </div>
                    @foreach($monthGrid as $week)
                        <div class="flex flex-row w-full border">
                            @foreach($week as $day)
                                @include($dayView, [
                                        'componentId' => $componentId,
                                        'day' => $day,
                                        'dayInMonth' => $day->isSameMonth($startsAt),
                                        'isToday' => $day->isToday(),
                                        'events' => $getEventsForDay($day, $events),
                                    ])
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div>
        @includeIf($afterCalendarView)
    </div>
</div>
