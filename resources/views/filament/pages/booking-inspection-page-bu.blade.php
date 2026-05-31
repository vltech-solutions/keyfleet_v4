<x-filament-panels::page>
    <div class="space-y-6"  style="--c-50:var(--primary-50);--c-400:var(--primary-400);--c-600:var(--primary-600);">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-200">
                        {{ ucfirst($inspectionType) }} Inspection
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Booking #{{ $booking->id }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Instruction -->
        <div class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-primary flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-[var(--primary-50)] dark:text-[var(--primary-50)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <div class="space-y-2 text-sm leading-relaxed">
                <p>
                        <strong class="font-semibold ">Instruction:</strong> 
                        Leave the checkbox <span class="font-medium underline decoration-blue-300 dark:decoration-blue-700">checked</span> if the item is in good condition.
                        <strong class="font-semibold ">Uncheck</strong> the item if there is any damage or issue, then provide remarks and upload a photo as evidence.
                </p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            @php
                $grouped = $this->groupedItems();
                $groups = $grouped->keys()->toArray();
                $firstGroup = $groups[0] ?? null;
            @endphp

            <div x-data="{ tab: '{{ $firstGroup }}' }">
                <!-- Tab Buttons -->
                <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div class="flex gap-2 overflow-x-auto whitespace-nowrap scrollbar-thin">
                        @foreach ($groups as $groupName)
                            <button
                                type="button"
                                @click="tab='{{ $groupName }}'"
                                :class="tab === '{{ $groupName }}'
                                    ? 'border-b-2 border-[var(--primary-50)] text-[var(--primary-50)] font-semibold'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="px-4 py-2 focus:outline-none shrink-0"
                            >
                                {{ $groupName }}
                            </button>
                        @endforeach


                        <button
                            type="button"
                            @click="tab='Gas / Odometer Reading'"
                            :class="tab === 'Gas / Odometer Reading'
                                ? 'border-b-2 border-[var(--primary-50)] text-[var(--primary-50)] font-semibold'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                            class="px-4 py-2 focus:outline-none shrink-0"
                        >
                            Gas / Odometer Reading
                        </button>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="space-y-4">
                    @foreach ($groups as $groupName)
                        <div x-show="tab === '{{ $groupName }}'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($grouped[$groupName] as $item)
                                <div class="relative border rounded-lg p-4 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600"
                                >
                                    <div class="flex justify-between items-center w-full">
                                        <!-- Left side: Checkbox + Label -->
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                wire:model="items.{{ $item->id }}"
                                                class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 transition duration-75 checked:ring-0 focus:ring-2 focus:ring-offset-0 disabled:pointer-events-none disabled:bg-gray-50 disabled:text-gray-50 disabled:checked:bg-gray-400 disabled:checked:text-gray-400 dark:bg-white/5 dark:disabled:bg-transparent dark:disabled:checked:bg-gray-600 text-primary-600 ring-gray-950/10 focus:ring-[#000]-600 checked:focus:ring-primary-500/50 dark:text-primary-500 dark:ring-white/20 dark:checked:bg-primary-500 dark:focus:ring-primary-500 dark:checked:focus:ring-primary-400/50 dark:disabled:ring-white/10 fi-ta-record-checkbox"
                                            >
                                            <span class="font-medium text-gray-900 dark:text-gray-200">{{ $item->item }}</span>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div x-show="tab === 'Gas / Odometer Reading'" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Odometer -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                    Odometer Reading (km)
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" step="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white pr-12 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 45,230"
                                    >
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                        km
                                    </span>
                                </div>
                            </div>

                            <!-- Fuel Reading -->
                            <div  x-data="{ fuel: 0 }">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Fuel Reading</label>

                                <div class="px-2">
                                    <input 
                                        type="range" 
                                        min="0" 
                                        max="4" 
                                        x-model="fuel" 
                                        class="w-full h-2 rounded-lg cursor-pointer bg-gray-200 dark:bg-gray-700 accent-blue-600"
                                    />

                                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <span>Empty</span><span>1/4</span><span>1/2</span><span>3/4</span><span>Full</span>
                                    </div>
                                </div>

                                <input type="hidden" name="fuel_level" :value="fuel">
                            </div>


                            <!-- Autosweep Load -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Autosweep Load</label>
                                <input type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500" placeholder="0"
                                >
                            </div>

                            <!-- Easytrip Load -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1"> Easytrip Load
                                </label>
                                <input type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500" placeholder="0" >
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button
                wire:click="save"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
            >
                Save Inspection
            </button>
        </div>

    </div>
</x-filament-panels::page>
