@props([
    'company' => null,
    'busyDates' => []
])

<div class="space-y-6">
    {{-- 1. Date & Time Selection --}}
    <div class="grid grid-cols-1 gap-4">
        {{-- Trip Start --}}
        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
            <label class="block mb-2 text-sm font-bold text-gray-700 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Trip Start <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <x-booking.datepicker wire:model.live="start_date" :busyDates="$busyDates" />
                <x-booking.timepicker wire:model="start_time" dateProp="start_date" title="Select Pickup Time" />
            </div>
        </div>

        {{-- Trip Ends --}}
        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
            <label class="block mb-2 text-sm font-bold text-gray-700 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Trip Ends <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <x-booking.datepicker wire:model.live="end_date" :busyDates="$busyDates" minDateProp="start_date"/>
                <x-booking.timepicker wire:model="end_time" dateProp="end_date" minTimeProp="start_time" title="Select Return Time" />
            </div>
        </div>
    </div>

    {{-- 2. Delivery Options --}}
    <div class="pt-2">
        <x-booking.delivery-options :company="$company" />
    </div>

    {{-- 3. Destinations & Drivers --}}
    <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-700">
        <div>
            <label class="block mb-1 text-sm font-medium dark:text-white text-gray-700">Destinations <span class="text-red-500">*</span></label>
            <input type="text" wire:model.defer="destination" placeholder="e.g. Tagaytay, Baguio, Manila"
                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 outline-none transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-white">
            @error('destination') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <p class="mt-1.5 text-[11px] text-gray-400">Please declare all planned destinations for insurance purposes.</p>
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium dark:text-white text-gray-700">Other Driver (Optional)</label>
            <input type="text" wire:model.defer="other_drivers" placeholder="Name of secondary driver"
                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 outline-none transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-white">
            <p class="mt-1.5 text-[11px] text-gray-400">Leave blank if you are the only driver.</p>
        </div>
    </div>

    {{-- 4. Add-ons (Driver) --}}
    @if($company->offer_driver_service)
        <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50">
            <label class="inline-flex items-center cursor-pointer group">
                <input type="checkbox" wire:model.defer="with_driver" value="1" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                <span class="ms-3 text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">Book with Professional Driver</span>
            </label>
            <p class="mt-2 text-xs text-blue-700/70 dark:text-blue-300/70 leading-relaxed">
                Highly recommended for long trips. Professional driver fees and meals are settled separately.
            </p>
        </div>
    @endif
</div>