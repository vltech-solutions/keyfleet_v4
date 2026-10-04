<div wire:key="booking-calendar" class="space-y-5" x-data="{
    exportOpen:false, previewOpen:false, exporting:false, previewImage:'',
    preset:'landscape', title:'Booking Calendar', theme:'light',
    showLogo:true, showCompany:true, showContact:false, showStatus:true, showMore:true,
    dimensions(){ return this.preset === 'square' ? 'width:1080px;min-height:1080px' : 'width:1600px;min-height:900px' },
    async previewExport(){
        this.exporting=true; await this.$nextTick();
        try {
            const canvas=await window.html2canvas(this.$refs.marketingCanvas,{scale:2,useCORS:true,backgroundColor:this.theme==='dark'?'#111827':'#ffffff',logging:false});
            this.previewImage=canvas.toDataURL('image/png'); this.previewOpen=true; this.exportOpen=false;
        } finally { this.exporting=false }
    },
    downloadExport(){ const a=document.createElement('a'); a.download='keyfleet-{{ $startsAt->format('Y-m') }}-'+this.preset+'.png'; a.href=this.previewImage; a.click() }
}">
    @include('components.calendar.toolbar')

    @if($calendarScope === 'month')
        @include('components.calendar.month-grid')
    @elseif($calendarScope === 'week')
        @include('components.calendar.week-view')
    @else
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            @include('components.calendar.day-detail', ['panelMode' => false])
        </div>
    @endif

    @if($showDayPanel && $calendarScope !== 'day')
        <div class="fixed inset-0 z-40 bg-gray-950/60 backdrop-blur-sm" wire:click="closeDayPanel"></div>
        <aside class="fixed inset-y-0 right-0 z-50 w-full max-w-xl overflow-y-auto bg-white p-5 shadow-2xl dark:bg-gray-900 sm:p-7" role="dialog" aria-modal="true" aria-label="Selected date bookings">
            @include('components.calendar.day-detail', ['panelMode' => true])
        </aside>
    @endif

    @include('components.calendar.marketing-export')

    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/80 p-4" role="dialog" aria-modal="true" aria-label="Marketing calendar preview">
        <div class="max-h-full w-full max-w-6xl overflow-auto rounded-2xl bg-white p-4 shadow-2xl dark:bg-gray-900">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div><h2 class="text-lg font-bold text-gray-950 dark:text-white">Export preview</h2><p class="text-sm text-gray-500">This preview matches the high-resolution PNG.</p></div>
                <button type="button" @click="previewOpen=false" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10" aria-label="Close preview">X</button>
            </div>
            <img :src="previewImage" alt="Generated marketing calendar preview" class="mx-auto max-w-full rounded-xl border border-gray-200 dark:border-white/10">
            <div class="mt-4 flex justify-end gap-2">
                <x-filament::button color="gray" @click="previewOpen=false; exportOpen=true">Regenerate</x-filament::button>
                <x-filament::button @click="downloadExport">Download PNG</x-filament::button>
            </div>
        </div>
    </div>
</div>
