@props([
    'company' => null,
    'pickupOption' => null,
])

@php 
    $companyName = addslashes($company->name ?? 'Company'); 
    $availableMethods = $company->delivery_methods;
    $allowed = json_encode(collect($availableMethods)->values()->toArray());
@endphp

<div x-data="{ 
    open: false, 
    allowedMethods: {{ $allowed }},
    {{-- Full mapping of labels --}}
    labels: {
        'renter_pickup_renter_return': 'Renter Pickup & Renter Return',
        'renter_pickup_owner_collection': 'Renter Pickup & Owner Collection',
        'owner_delivery_renter_return': 'Owner Delivery & Renter Return',
        'owner_delivery_owner_collection': 'Owner Delivery & Owner Collection'
    },
    {{-- Full mapping of descriptions --}}
    descriptions: {
        'renter_pickup_renter_return': 'You will pick up the car from {{ $companyName }} garage and return it to the garage.',
        'renter_pickup_owner_collection': 'You will pick up from {{ $companyName }} garage, and they will collect it from you.',
        'owner_delivery_renter_return': '{{ $companyName }} will deliver to you, and you will return it to their garage.',
        'owner_delivery_owner_collection': '{{ $companyName }} will deliver to you and also collect it after the rental.'
    }
}" class="space-y-4">

    {{-- Dropdown Select --}}
    <div class="relative">
        <label class="block mb-1 text-sm font-medium dark:text-white text-gray-700">
            Delivery Method <span class="text-red-500">*</span>
        </label>

        <button
            type="button"
            @click="open = !open"
            class="w-full px-4 py-3 text-left border border-gray-300 rounded-xl dark:text-white dark:bg-gray-800 dark:border-gray-700 focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all flex justify-between items-center bg-white shadow-sm">
            
            <span class="truncate" x-text="labels[$wire.pickup_option] || 'Select Delivery Method'"></span>
            
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Options Dropdown --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            @click.outside="open = false"
            class="absolute z-20 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl dark:bg-gray-800 dark:border-gray-700 overflow-hidden">

            <template x-for="key in allowedMethods" :key="key">
                <div 
                    @click="$wire.set('pickup_option', key); open = false"
                    class="px-4 py-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b last:border-0 dark:border-gray-700 transition-colors">
                    
                    <div class="font-bold text-sm text-gray-900 dark:text-gray-100" x-text="labels[key]"></div>
                    <div class="mt-1 text-xs text-blue-600 dark:text-blue-400 leading-relaxed" x-text="descriptions[key]"></div>
                </div>
            </template>
        </div>
    </div>

    @error('pickup_option') 
        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
    @enderror

    {{-- Conditional Address Fields --}}
    <div class="grid grid-cols-1 gap-4" x-data="{ 
        get showDelivery() { return ['owner_delivery_renter_return','owner_delivery_owner_collection'].includes($wire.pickup_option) },
        get showReturn() { return ['renter_pickup_owner_collection','owner_delivery_owner_collection'].includes($wire.pickup_option) }
    }">
        <template x-if="showDelivery">
            <div x-transition class="space-y-1">
                <label class="block text-sm font-medium dark:text-white text-gray-700">Delivery Address</label>
                <input type="text" wire:model.defer="pickup_address" placeholder="Where should we deliver the car?"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:ring-4 focus:ring-blue-100 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm">
                @error('pickup_address') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </template>

        <template x-if="showReturn">
            <div x-transition class="space-y-1">
                <label class="block text-sm font-medium dark:text-white text-gray-700">Return Address (Collection Point)</label>
                <input type="text" wire:model.defer="return_address" placeholder="Where should we collect the car?"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:ring-4 focus:ring-blue-100 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm">
                @error('return_address') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </template>
    </div>
</div>