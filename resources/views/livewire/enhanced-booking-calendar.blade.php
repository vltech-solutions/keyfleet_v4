<div wire:key="booking-calendar" class="space-y-5" x-data="{
    exportOpen:false, previewOpen:false, exporting:false, previewImage:'',
    preset:'landscape', theme:'light',
    showLogo:true, showCompany:true, showContact:false,
    dimensions(){ return this.preset === 'square' ? 'width:1080px;min-height:1080px' : 'width:1600px;min-height:900px' },
    applyExportTheme(root){
        if (!root) return;
        const dark=this.theme==='dark';
        const background=dark?'#111827':'#ffffff';
        const foreground=dark?'#f9fafb':'#111827';
        const border=dark?'rgba(255,255,255,0.16)':'#e5e7eb';
        root.setAttribute('data-export-theme',this.theme);
        root.style.setProperty('background-color',background,'important');
        root.style.setProperty('color',foreground,'important');
        root.style.setProperty('color-scheme',this.theme);
        root.querySelectorAll('[data-export-grid]').forEach(element => {
            element.style.setProperty('border-color',border,'important');
        });
        root.querySelectorAll('[data-export-cell]').forEach(element => {
            element.style.setProperty('background-color',background,'important');
            element.style.setProperty('border-color',border,'important');
            element.style.setProperty('color',foreground,'important');
        });
    },
    async previewExport(){
        this.exporting=true;
        await this.$nextTick();
        try {
            this.applyExportTheme(this.$refs.marketingCanvas);
            if (document.fonts && document.fonts.ready) await document.fonts.ready;
            const images=Array.from(this.$refs.marketingCanvas.querySelectorAll('img'));
            await Promise.all(images.map(async image => {
                if (!image.complete) await new Promise(resolve => {
                    image.addEventListener('load',resolve,{once:true});
                    image.addEventListener('error',resolve,{once:true});
                });
                if (image.decode) await image.decode().catch(() => {});
            }));
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            this.applyExportTheme(this.$refs.marketingCanvas);
            const canvas=await window.html2canvas(this.$refs.marketingCanvas,{
                scale:2,
                useCORS:true,
                backgroundColor:this.theme==='dark'?'#111827':'#ffffff',
                logging:false,
                onclone:clonedDocument => {
                    clonedDocument.querySelectorAll('.dark').forEach(element => element.classList.remove('dark'));
                    const clonedCanvas=clonedDocument.querySelector('[data-export-theme]');
                    this.applyExportTheme(clonedCanvas);
                },
            });
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
        <template x-teleport="body">
            <div class="fixed inset-0 z-[9999]">
                <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" wire:click="closeDayPanel"></div>
                <aside class="absolute inset-y-0 right-0 z-10 w-full max-w-xl overflow-y-auto bg-white p-5 shadow-2xl dark:bg-gray-900 sm:p-7" role="dialog" aria-modal="true" aria-label="Selected date bookings">
                    @include('components.calendar.day-detail', ['panelMode' => true])
                </aside>
            </div>
        </template>
    @endif

    @include('components.calendar.marketing-export')

    <template x-teleport="body">
    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-950/80 p-4" role="dialog" aria-modal="true" aria-label="Marketing calendar preview">
        <div class="max-h-full w-full max-w-6xl overflow-auto rounded-2xl bg-white p-4 shadow-2xl dark:bg-gray-900">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div><h2 class="text-lg font-bold text-gray-950 dark:text-white">Export preview</h2><p class="text-sm text-gray-500">This preview matches the high-resolution PNG.</p></div>
                <button type="button" @click="previewOpen=false" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10" aria-label="Close preview"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                </button>
            </div>
            <img :src="previewImage" alt="Generated marketing calendar preview" class="mx-auto max-w-full rounded-xl border border-gray-200 dark:border-white/10">
            <div class="mt-4 flex justify-end gap-2">
                <x-filament::button color="gray" @click="previewOpen=false; exportOpen=true">Regenerate</x-filament::button>
                <x-filament::button @click="downloadExport">Download PNG</x-filament::button>
            </div>
        </div>
    </div>
    </template>
</div>
