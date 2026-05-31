@props([
    'model' => null,
    'label' => 'Pick time',
    'title' => 'Select Time',
    'dateProp' => 'start_date', {{-- Pangalan ng date variable sa Livewire --}}
    'minTimeProp' => null       {{-- Pangalan ng start_time variable para sa end_time picker --}}
])

<div class="flex flex-col" x-data="{ 
    openTime: false,
    selectedTime: @entangle($attributes->wire('model')),
    
    // Getters para sa Livewire values
    get currentSelectedDate() { return $wire.get('{{ $dateProp }}'); },
    get minTimeConstraint() { return '{{ $minTimeProp }}' ? $wire.get('{{ $minTimeProp }}') : null; },

    getTimeSlots() {
        return Array.from({length:48},(_,i)=>{
            let h = (i/2|0);
            let m = i%2?'30':'00';
            let displayH = h==0?12:(h>12?h-12:h);
            let ampm = h < 12 ? 'AM' : 'PM';
            return {
                formatted: `${displayH.toString().padStart(2,'0')}:${m} ${ampm}`, 
                hour: h, 
                minute: parseInt(m)
            };
        });
    },

    isTimeDisabled(slot) {
        let now = new Date();
        let todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        
        // RULE 1: Kung ang napiling date ay TODAY, disable past times
        if (this.currentSelectedDate === todayStr) {
            if (slot.hour < now.getHours()) return true;
            if (slot.hour === now.getHours() && slot.minute <= now.getMinutes()) return true;
        }

        // RULE 2: Kung may minTimeConstraint (para sa End Time picker)
        // I-check muna kung same ang start_date at end_date bago i-apply ang time restriction
        let startDate = $wire.get('start_date');
        let endDate = $wire.get('end_date');

        if (this.minTimeConstraint && (startDate === endDate)) {
            let [time, modifier] = this.minTimeConstraint.split(' ');
            let [minH, minM] = time.split(':');
            minH = parseInt(minH);
            minM = parseInt(minM);

            if (modifier === 'PM' && minH < 12) minH += 12;
            if (modifier === 'AM' && minH === 12) minH = 0;

            if (slot.hour < minH) return true;
            if (slot.hour === minH && slot.minute <= minM) return true;
        }

        return false;
    }
}">
    {{-- Trigger Button --}}
    <div class="relative">
        <div class="absolute inset-y-0 flex items-center pointer-events-none start-0 ps-3.5">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <button type="button" @click="openTime = true" 
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl block w-full ps-10 p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-left shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-all outline-none focus:ring-2 focus:ring-blue-500">
            <span class="font-medium" x-text="selectedTime ? selectedTime : '{{ $label }}'"></span>
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
    {{-- Time Modal --}}
    <div x-show="openTime" 
         x-transition.opacity
         x-cloak
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        
        <div class="w-full max-w-md overflow-hidden bg-white shadow-2xl dark:bg-gray-800 rounded-2xl border dark:border-gray-700" @click.away="openTime = false">
            <div class="p-5">
                <div class="flex items-center justify-between mb-4 border-b pb-3 dark:border-gray-700">
                    <h2 class="text-base font-bold dark:text-white">{{ $title }}</h2>
                    <button type="button" @click="openTime = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-2 overflow-y-auto max-h-72 p-1">
                    <template x-for="slot in getTimeSlots()" :key="slot.formatted">
                        <button type="button" 
                            x-show="!isTimeDisabled(slot)"
                            @click="selectedTime = slot.formatted; openTime = false"
                            class="px-2 py-3 text-sm font-semibold rounded-xl transition-all duration-200 border"
                            :class="{
                                'bg-blue-600 text-white border-blue-600 shadow-md': selectedTime == slot.formatted,
                                'text-gray-700 dark:text-gray-200 border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700': selectedTime != slot.formatted
                            }">
                            <span x-text="slot.formatted"></span>
                        </button>
                    </template>
                </div>
            </div>
            
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t dark:border-gray-700 text-center">
                <button type="button" @click="openTime = false" class="text-sm font-bold text-gray-500">Close</button>
            </div>
        </div>
    </div>
</div>