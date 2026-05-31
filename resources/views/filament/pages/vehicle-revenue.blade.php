<x-filament-panels::page>
    <span class="text-xs"><b>Note:</b> Balance is included in these values.</span>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>
    <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 xl:grid-cols-3">
        
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</h3>
                <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">
                    ₱{{ number_format($this->getTotalRevenue(), 2) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Total revenue in selected range</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-heroicon-o-currency-dollar class="w-7 h-7 text-primary-600 dark:text-primary-400" />
            </div>
        </div>

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bookings</h3>
                <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($this->getTotalBookings()) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">All bookings from filters</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-heroicon-o-calendar-days class="w-7 h-7 text-blue-600 dark:text-blue-400" />
            </div>
        </div>

        @php
            $topCar = $this->getTopCar();
        @endphp

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="max-w-[65%]">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Top Performing Car</h3>
                <p class="mt-1 text-lg font-bold truncate text-emerald-600 dark:text-emerald-400">
                    {{ $topCar?->name ?? 'No bookings yet' }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Highest revenue generator</p>
            </div>
            
            <div class="flex items-center justify-center w-14 h-14 overflow-hidden rounded-full bg-emerald-500/10 ring-2 ring-emerald-500/20">
                @if ($topCar?->image)
                    <img src="{{ Storage::url($topCar->image) }}" alt="Car Image" class="object-contain w-full h-full" />
                @else
                    <x-heroicon-o-truck class="w-7 h-7 text-emerald-600 opacity-50" />
                @endif
            </div>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-4">
        {{-- Table section - scrollable --}}
        <div class="col-span-12 xl:col-span-12">
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
