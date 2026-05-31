<div
    x-data="{ 
        state: $wire.$entangle('{{ $getStatePath() }}'),
        totalBlocks: 10,
        labels: { 0: 'E', 25: '1/4', 50: '1/2', 75: '3/4', 100: 'F' },
        get displayValue() {
            let val = this.state ?? 0;
            return this.labels[val] !== undefined ? this.labels[val] : val + '%';
        },
        getBlockColor(blockIndex) {
            let threshold = (blockIndex / this.totalBlocks) * 100;

            if ((this.state ?? 0) < threshold) {
                return 'bg-gray-200 dark:bg-gray-800';
            }

            if (threshold <= 20) return 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]';
            if (threshold <= 50) return 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]';
            return 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]';
        }
    }"
    x-init="if (state === null) state = 0"
    class="p-5 rounded-3xl border-1 border-gray-200 dark:border-gray-200 w-full"
>
    <!-- Label + Value -->
    <div class="mb-2 flex justify-between items-center text-xs font-bold text-gray-300 uppercase">
        <span>Fuel Level</span>
        <span
            :class="(state ?? 0) <= 20 ? 'text-red-500' : 'text-emerald-500'"
            x-text="displayValue"
        ></span>
    </div>

    <!-- Bars + Slider -->
    <div class="relative">
        <div class="flex gap-1">
            <template x-for="i in totalBlocks">
                <div
                    class="flex-1 h-4 rounded-sm transition-all duration-200"
                    :class="getBlockColor(i)"
                ></div>
            </template>
        </div>

        <!-- Invisible slider on top -->
        <input
            type="range"
            min="0"
            max="100"
            step="1"
            x-model.number="state"
            class="absolute inset-0 w-full h-full opacity-0 cursor-ew-resize"
        >
    </div>

    <!-- E / F labels -->
    <div class="mt-1 flex justify-between text-[10px] font-bold text-gray-500">
        <span>E</span>
        <span>F</span>
    </div>
</div>
