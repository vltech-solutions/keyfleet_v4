@php
    $value = $getState() ?? 0;
@endphp

<div
    x-data="{
        state: {{ $value }},
        totalBlocks: 10,
        labels: { 0: 'E', 25: '1/4', 50: '1/2', 75: '3/4', 100: 'F' },
        get displayValue() {
            return this.labels[this.state] ?? this.state + '%'
        },
        getBlockColor(blockIndex) {
            let threshold = (blockIndex / this.totalBlocks) * 100;

            if (this.state < threshold) {
                return 'bg-gray-200 dark:bg-gray-800';
            }

            if (threshold <= 20) return 'bg-red-500';
            if (threshold <= 50) return 'bg-amber-500';
            return 'bg-emerald-500';
        }
    }"
    class="w-full"
>
    <!-- Percentage / Label -->
    <div class="mb-2 text-xs font-semibold text-gray-600 flex justify-between">
        <span>Fuel Level</span>
        <span
            :class="state <= 20 ? 'text-red-500' : 'text-emerald-500'"
            x-text="displayValue"
        ></span>
    </div>

    <!-- Horizontal Bars -->
    <div class="flex gap-1">
        <template x-for="i in totalBlocks">
            <div
                class="flex-1 h-3 rounded-sm transition-all"
                :class="getBlockColor(i)"
            ></div>
        </template>
    </div>

    <!-- E / F labels -->
    <div class="mt-1 flex justify-between text-[10px] font-bold text-gray-500">
        <span>E</span>
        <span>F</span>
    </div>
</div>
