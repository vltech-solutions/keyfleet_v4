@props([
    'model' => null,
    'busyDates' => [],
    'minDateProp' => null, 
    'label' => 'Choose date'
])

<div x-data="{ 
    openDate: false,
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    days: [],
    blankDays: [],
    
    // Kunin ang LIVE value mula sa Livewire property
    get minDateValue() {
        return '{{ $minDateProp }}' ? $wire.get('{{ $minDateProp }}') : null;
    },

    init() { 
        this.syncView();
        this.getDays();

        // Bantayan ang pagbabago sa Livewire variable (e.g. kapag pinindot ang start_date)
        if ('{{ $minDateProp }}') {
            this.$watch('minDateValue', (value) => {
                this.syncView();
                this.getDays();
            });
        }
    },

    syncView() {
        let dateToView = this.minDateValue ? new Date(this.minDateValue) : new Date();
        if (!isNaN(dateToView.getTime())) {
            this.currentMonth = dateToView.getMonth();
            this.currentYear = dateToView.getFullYear();
        }
    },

    getDays() {
        const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
        const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        this.days = Array.from({length: daysInMonth}, (_, i) => i + 1);
        this.blankDays = Array.from({length: firstDay}, (_, i) => i);
    },

    prevMonth() { 
        if(this.currentMonth == 0) { this.currentMonth = 11; this.currentYear--; } 
        else { this.currentMonth--; } 
        this.getDays(); 
    },

    nextMonth() { 
        if(this.currentMonth == 11) { this.currentMonth = 0; this.currentYear++; } 
        else { this.currentMonth++; } 
        this.getDays(); 
    },

    isDateDisabled(day) {
        let date = new Date(this.currentYear, this.currentMonth, day);
        date.setHours(0,0,0,0);

        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let d = String(date.getDate()).padStart(2, '0');
        let formattedDate = `${year}-${month}-${d}`;

        // 1. Disable Past Dates (Before Today)
        let isPast = date < new Date().setHours(0,0,0,0);
        
        // 2. Disable dates before minDateValue
        let isBeforeMin = false;
        if (this.minDateValue) {
            let min = new Date(this.minDateValue);
            min.setHours(0,0,0,0);
            isBeforeMin = date < min;
        }

        // 3. Busy Dates Check
        let busyList = @js($busyDates);
        let isBusy = Array.isArray(busyList) && busyList.includes(formattedDate);
        
        return isPast || isBeforeMin || isBusy;
    },

    selectDate(day) {
        if(this.isDateDisabled(day)) return;
        let selected = new Date(this.currentYear, this.currentMonth, day);
        let year = selected.getFullYear();
        let month = String(selected.getMonth() + 1).padStart(2, '0');
        let d = String(selected.getDate()).padStart(2, '0');
        let finalDate = `${year}-${month}-${d}`;

        @if($attributes->wire('model')->value())
            $wire.set('{{ $attributes->wire('model')->value() }}', finalDate);
        @endif
        
        this.openDate = false;
    }
}" class="w-full">

    {{-- Trigger Button --}}
    <div class="relative">
        <div class="absolute inset-y-0 flex items-center pointer-events-none start-0 ps-3.5">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
            </svg>
        </div>
        <button type="button" @click="openDate = true" 
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl block w-full ps-10 p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-left hover:bg-gray-100 transition-all focus:ring-2 focus:ring-blue-500">
            <span class="font-medium" x-text="$wire.get('{{ $attributes->wire('model')->value() }}') ? $wire.get('{{ $attributes->wire('model')->value() }}') : '{{ $label }}'"></span>
        </button>
       
    </div>
    @php
        $modelName = $attributes->wire('model')->value();
    @endphp

    @if($modelName)
        @error($modelName) 
            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
        @enderror
    @endif
    {{-- Modal --}}
    <div x-show="openDate" x-transition.opacity x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border dark:border-gray-700" @click.away="openDate = false">
            <div class="p-5">
                <div class="flex items-center justify-between mb-6">
                    {{-- Previous Month --}}
                    <button type="button" 
                        @click="prevMonth()" 
                        class="p-2.5 bg-gray-50 hover:bg-gray-100 text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-400 rounded-xl transition-colors border border-gray-100 dark:border-gray-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    {{-- Current Month & Year Display --}}
                    <h2 class="text-sm font-bold tracking-tight text-gray-900 dark:text-white uppercase" 
                        x-text="new Date(currentYear, currentMonth).toLocaleString('default', { month: 'long', year: 'numeric' })">
                    </h2>

                    {{-- Next Month --}}
                    <button type="button" 
                        @click="nextMonth()" 
                        class="p-2.5 bg-gray-50 hover:bg-gray-100 text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-400 rounded-xl transition-colors border border-gray-100 dark:border-gray-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-7 mb-2 text-center text-xs font-bold text-gray-400">
                    <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                </div>

                <div class="grid grid-cols-7 gap-1">
                    <template x-for="blank in blankDays"><div></div></template>
                    <template x-for="day in days" :key="day">
                        <button type="button" @click="selectDate(day)" :disabled="isDateDisabled(day)"
                            class="aspect-square flex items-center justify-center text-sm font-semibold rounded-xl transition-all"
                            :class="{
                                'text-gray-300 dark:text-gray-600 line-through opacity-50': isDateDisabled(day),
                                'bg-blue-600 text-white shadow-lg': $wire.get('{{ $attributes->wire('model')->value() }}') == `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                                'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700': !isDateDisabled(day)
                            }" x-text="day"></button>
                    </template>
                </div>
            </div>
            <div class="p-4 border-t dark:border-gray-700">
                <button type="button" @click="openDate = false" class="w-full py-2 text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors">Cancel</button>
            </div>
        </div>
    </div>
</div>