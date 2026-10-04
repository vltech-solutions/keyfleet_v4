<div x-show="exportOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/70 p-4" role="dialog" aria-modal="true" aria-label="Marketing export settings">
    <div class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-2xl dark:bg-gray-900">
        <div class="flex items-start justify-between"><div><h2 class="text-xl font-black text-gray-950 dark:text-white">Marketing Calendar Export</h2><p class="mt-1 text-sm text-gray-500">Create a branded, fixed-size social graphic.</p></div><button @click="exportOpen=false" class="rounded-lg p-2 text-gray-500" aria-label="Close export settings">X</button></div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Calendar title
                <select x-model="title" class="mt-1 w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-800 dark:text-white"><option>Booking Calendar</option><option>Availability Calendar</option><option>Fleet Schedule</option></select>
            </label>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Format
                <select x-model="preset" class="mt-1 w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-800 dark:text-white"><option value="landscape">Landscape - 1600x900</option><option value="square">Square - 1080x1080</option></select>
            </label>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Theme
                <select x-model="theme" class="mt-1 w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-800 dark:text-white"><option value="light">Light</option><option value="dark">Dark</option></select>
            </label>
            <fieldset class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                <legend class="mb-1 font-semibold">Branding and display</legend>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="showLogo" class="rounded"> Company logo</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="showCompany" class="rounded"> Company name</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="showContact" class="rounded"> Public contact details</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="showStatus" class="rounded"> Booking status</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="showMore" class="rounded"> +N more indicator</label>
            </fieldset>
        </div>
        <div class="mt-6 flex justify-end gap-2"><x-filament::button color="gray" @click="exportOpen=false">Cancel</x-filament::button><x-filament::button @click="previewExport" x-bind:disabled="exporting"><span x-text="exporting ? 'Rendering...' : 'Generate Preview'"></span></x-filament::button></div>
    </div>
</div>
<div class="fixed -left-[10000px] top-0">
    <div x-ref="marketingCanvas" :style="dimensions()" :class="theme==='dark'?'bg-gray-900 text-white':'bg-white text-gray-950'" class="box-border flex flex-col overflow-hidden p-10 font-sans">
        <header class="mb-6 flex min-h-24 items-center gap-6 border-b pb-5" :class="theme==='dark'?'border-white/15':'border-gray-200'">
            @if($company->avatar_url)<img x-show="showLogo" src="{{ asset('storage/'.$company->avatar_url) }}" alt="{{ $company->name }} logo" class="h-20 w-48 object-contain">@endif
            <div class="min-w-0 flex-1"><p x-show="showCompany" class="truncate text-xl font-bold opacity-70">{{ $company->name }}</p><h2 class="text-5xl font-black tracking-tight">{{ $startsAt->format('F Y') }}</h2><p class="mt-1 text-lg font-semibold opacity-70" x-text="title"></p></div>
            <div x-show="showContact" class="max-w-xs whitespace-pre-line text-right text-sm font-medium opacity-70">{{ $company->contacts }}</div>
        </header>
        <div class="grid grid-cols-7 border-l border-t" :class="theme==='dark'?'border-white/15':'border-gray-200'">
            @foreach($monthGrid->first() as $day)<div class="border-b border-r px-2 py-2 text-center text-sm font-black uppercase opacity-60" :class="theme==='dark'?'border-white/15':'border-gray-200'">{{ $day->format('D') }}</div>@endforeach
            @foreach($monthGrid as $week)
                @foreach($week as $day)
                    @php $exportEvents=$getEventsForDay($day); @endphp
                    <div class="min-h-28 border-b border-r p-2" :class="theme==='dark'?'border-white/15 bg-gray-900':'border-gray-200 bg-white'">
                        <div class="mb-1 flex items-center justify-between"><span class="text-base font-black {{ $day->isSameMonth($startsAt)?'':'opacity-30' }}">{{ $day->day }}</span>@if($exportEvents->isNotEmpty())<span class="text-xs font-bold opacity-40">{{ $exportEvents->count() }}</span>@endif</div>
                        <div class="grid grid-cols-3 gap-1">
                            @foreach($exportEvents->take(6) as $event)
                                <div class="relative flex h-10 items-center justify-center rounded-md" :class="theme==='dark'?'bg-white/5':'bg-gray-50'"><img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" class="h-full w-full object-contain"><span x-show="showStatus" class="absolute right-0.5 top-0.5 h-2 w-2 rounded-full bg-primary-500"></span></div>
                            @endforeach
                        </div>
                        @if($exportEvents->count()>6)<p x-show="showMore" class="mt-1 text-center text-xs font-black text-primary-500">+{{ $exportEvents->count()-6 }} more</p>@endif
                    </div>
                @endforeach
            @endforeach
        </div>
        <footer x-show="showContact" class="mt-auto pt-4 text-center text-sm font-semibold opacity-60">{{ $company->contacts }}</footer>
    </div>
</div>
