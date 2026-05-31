<x-filament::page>
    {{-- Header --}}
    <div class="flex items-center mb-6 gap-x-4">
        <img
            src="{{ Storage::url($record->avatar_url) }}"
            alt="{{ $record->name }} Logo"
            class="w-16 h-16 border shadow"
        >
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ $record->name }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $record->address }}</p>
        </div>
    </div>

    {{-- {{ json_encode($record) }} --}}

    {{-- Widgets --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        {{-- Bookings Widget --}}
        <div class="flex items-center p-4 bg-white border shadow-sm dark:bg-gray-900 dark:border-gray-700 gap-x-2 rounded-xl">
            <x-lucide-calendar-days class="w-10 h-10 text-blue-600" />
            <div class="ml-4">
                <div class="text-sm text-gray-600 dark:text-gray-300">Bookings</div>
                <div class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ count($record->bookings) }}</div>
            </div>
        </div>

        {{-- Cars Widget --}}
        <div class="flex items-center p-4 bg-white border shadow-sm dark:bg-gray-900 dark:border-gray-700 gap-x-2 rounded-xl">
            <x-lucide-car class="w-10 h-10 text-green-600" />
            <div class="ml-4">
                <div class="text-sm text-gray-600 dark:text-gray-300">Cars</div>
                <div class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ count($record->cars) }}</div>
            </div>
        </div>

        {{-- Subscription Widget --}}
        <div class="flex items-center p-4 bg-white border shadow-sm dark:bg-gray-900 dark:border-gray-700 gap-x-2 rounded-xl">
            <x-lucide-badge-check class="w-10 h-10 text-purple-600" />
            <div class="ml-4">
                <div class="text-sm text-gray-600 dark:text-gray-300">Subscription</div>
                <div class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    {{ $record->subscription ? 'Active' : 'None' }}
                </div>
            </div>
        </div>
    </div>
    
    @if ($record->subscriptions->count())
        <div class="mt-6 overflow-hidden bg-white border shadow-sm rounded-xl dark:bg-gray-900 dark:border-gray-700">
            <div class="px-4 py-3 text-base font-semibold text-gray-800 dark:text-gray-100">
                Subscription History
            </div>
            {{ $this->table }}
        </div>
    @endif

</x-filament::page>
