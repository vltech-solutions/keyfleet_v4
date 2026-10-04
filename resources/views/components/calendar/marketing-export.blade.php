<template x-teleport="body">
<div x-show="exportOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-950/70 p-4" role="dialog" aria-modal="true" aria-label="Marketing export settings">
    <div class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-2xl dark:bg-gray-900">
        <div class="flex items-start justify-between"><div><h2 class="text-xl font-black text-gray-950 dark:text-white">Marketing Calendar Export</h2><p class="mt-1 text-sm text-gray-500">Create a branded, fixed-size social graphic.</p></div><button @click="exportOpen=false" class="rounded-lg p-2 text-gray-500" aria-label="Close export settings"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg></button></div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">

            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Format
                <select x-model="preset" class="mt-1 w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-800 dark:text-white"><option value="landscape">Landscape - 1600x900</option><option value="square">Square - 1080x1080</option></select>
            </label>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Theme
                <select x-model="theme" class="mt-1 w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-gray-800 dark:text-white"><option value="light">Light</option><option value="dark">Dark</option></select>
            </label>
            <fieldset class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                <legend class="mb-1 font-semibold">Branding</legend>
                <label class="group flex cursor-pointer items-center gap-3 rounded-lg px-1 py-1">
                    <input type="checkbox" x-model="showLogo" class="peer sr-only">
                    <span :class="showLogo ? 'border-primary-500 bg-primary-600 text-white' : 'border-gray-400 bg-white text-transparent dark:border-gray-500 dark:bg-gray-800'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 text-xs font-black shadow-sm transition peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-gray-900"><span x-show="showLogo">&#10003;</span></span>
                    <span>Company logo</span>
                </label>
                <label class="group flex cursor-pointer items-center gap-3 rounded-lg px-1 py-1">
                    <input type="checkbox" x-model="showCompany" class="peer sr-only">
                    <span :class="showCompany ? 'border-primary-500 bg-primary-600 text-white' : 'border-gray-400 bg-white text-transparent dark:border-gray-500 dark:bg-gray-800'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 text-xs font-black shadow-sm transition peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-gray-900"><span x-show="showCompany">&#10003;</span></span>
                    <span>Company name</span>
                </label>
                <label class="group flex cursor-pointer items-center gap-3 rounded-lg px-1 py-1">
                    <input type="checkbox" x-model="showContact" class="peer sr-only">
                    <span :class="showContact ? 'border-primary-500 bg-primary-600 text-white' : 'border-gray-400 bg-white text-transparent dark:border-gray-500 dark:bg-gray-800'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 text-xs font-black shadow-sm transition peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-gray-900"><span x-show="showContact">&#10003;</span></span>
                    <span>Public contact details</span>
                </label>
            </fieldset>
        </div>
        <div class="mt-6 flex justify-end gap-2"><x-filament::button color="gray" @click="exportOpen=false">Cancel</x-filament::button><x-filament::button @click="previewExport" x-bind:disabled="exporting"><span x-text="exporting ? 'Rendering...' : 'Generate Preview'"></span></x-filament::button></div>
    </div>
</div>
</template>
<div class="fixed -left-[10000px] top-0">
    <div
        x-ref="marketingCanvas"
        :data-export-theme="theme"
        :style="dimensions() + ';background-color:' + (theme==='dark' ? '#111827' : '#ffffff') + ';color:' + (theme==='dark' ? '#f9fafb' : '#111827') + ';color-scheme:' + theme"
        class="box-border flex flex-col overflow-hidden p-10 font-sans"
    >
        <header class="mb-6 flex min-h-[170px] items-center gap-10 border-b-4 pb-6" style="border-bottom-color: {{ $company->primary_color ?: '#16a34a' }};">
            @if($company->avatar_url)
                <img
                    x-show="showLogo"
                    src="{{ asset('storage/'.$company->avatar_url) }}"
                    alt="{{ $company->name }} logo"
                    style="display:block;width:auto;height:auto;max-width:260px;max-height:130px;object-fit:contain;object-position:center;flex:0 0 auto;"
                >
            @endif
            <div style="display:flex;min-width:0;flex:1;flex-direction:column;justify-content:center;overflow:visible;">
                <div x-show="showCompany" style="display:block;margin:0;padding:4px 0;font-size:54px;font-weight:900;line-height:64px;letter-spacing:-0.025em;overflow:visible;">
                    {{ $company->name }}
                </div>
                <div style="display:block;margin:4px 0 0 0;padding:0;font-size:38px;font-weight:800;line-height:48px;letter-spacing:-0.02em;overflow:visible;opacity:0.78;">
                    {{ $startsAt->format('F Y') }}
                </div>
            </div>
            <div x-show="showContact" class="max-w-[280px] whitespace-pre-line text-right text-sm font-semibold leading-6 opacity-70">{{ $company->contacts }}</div>
        </header>
        <div
            data-export-grid
            class="grid grid-cols-7 border-l border-t"
            :style="'border-color:' + (theme==='dark' ? 'rgba(255,255,255,0.16)' : '#e5e7eb')"
        >
            @foreach($monthGrid->first() as $day)<div class="border-b border-r px-2 py-3 text-center text-sm font-black uppercase text-white" style="background-color: {{ $company->primary_color ?: '#16a34a' }}; border-color: rgba(255,255,255,0.28);">{{ $day->format('D') }}</div>@endforeach
            @foreach($monthGrid as $week)
                @foreach($week as $day)
                    @php $exportEvents=$getEventsForDay($day); $exportCount=$exportEvents->count(); @endphp
                    <div
                        data-export-cell
                        class="min-h-28 border-b border-r p-2"
                        :style="'background-color:' + (theme==='dark' ? '#111827' : '#ffffff') + ';border-color:' + (theme==='dark' ? 'rgba(255,255,255,0.16)' : '#e5e7eb') + ';color:' + (theme==='dark' ? '#f9fafb' : '#111827')"
                    >
                        <div class="mb-1"><span class="text-base font-black {{ $day->isSameMonth($startsAt)?'':'opacity-30' }}">{{ $day->day }}</span></div>
                        <div @class([
                            'grid w-full items-center justify-items-center gap-1',
                            'grid-cols-1'=>$exportCount<=2,
                            'grid-cols-2'=>$exportCount>2 && $exportCount<=6,
                            'grid-cols-3'=>$exportCount>6 && $exportCount<=12,
                            'grid-cols-4'=>$exportCount>12,
                        ])>
                            @foreach($exportEvents as $event)
                                <div class="flex w-full items-center justify-center">
                                    @if($exportCount===1)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" class="mx-auto h-auto max-h-[74px] w-auto max-w-[90%] object-contain">
                                    @elseif($exportCount===2)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" class="mx-auto h-auto max-h-[38px] w-auto max-w-[110px] object-contain">
                                    @elseif($exportCount>12)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" class="mx-auto h-auto max-h-[18px] w-auto max-w-[35px] object-contain">
                                    @elseif($exportCount>6)
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" class="mx-auto h-auto max-h-[25px] w-auto max-w-[50px] object-contain">
                                    @else
                                        <img src="{{ $event['image_url'] }}" alt="{{ $event['car_name'] }}" class="mx-auto h-auto max-h-[35px] w-auto max-w-[70px] object-contain">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
        <footer x-show="showContact" class="mt-auto pt-4 text-center text-sm font-semibold opacity-60">{{ $company->contacts }}</footer>
    </div>
</div>
