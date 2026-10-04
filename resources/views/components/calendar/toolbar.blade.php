<section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::button color="gray" wire:click="goToPreviousMonth">Prev</x-filament::button>
            <x-filament::button color="gray" wire:click="goToCurrentMonth">Today</x-filament::button>
            <x-filament::button color="gray" wire:click="goToNextMonth">Next</x-filament::button>
            <h1 class="ml-1 text-2xl font-black tracking-tight text-gray-950 dark:text-white sm:text-3xl">{{ $startsAt->format('F Y') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-white/5" aria-label="Calendar purpose">
                <button wire:click="setViewMode('visual')" @class(['rounded-lg px-3 py-2 text-sm font-semibold','bg-white text-primary-600 shadow-sm dark:bg-gray-800'=>$viewMode==='visual','text-gray-600 dark:text-gray-300'=>$viewMode!=='visual'])>Visual</button>
                <button wire:click="setViewMode('operations')" @class(['rounded-lg px-3 py-2 text-sm font-semibold','bg-white text-primary-600 shadow-sm dark:bg-gray-800'=>$viewMode==='operations','text-gray-600 dark:text-gray-300'=>$viewMode!=='operations'])>Operations</button>
            </div>
            <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-white/5" aria-label="Calendar period">
                @foreach(['month','week','day'] as $scope)
                    <button wire:click="setCalendarScope('{{ $scope }}')" @class(['rounded-lg px-3 py-2 text-sm font-semibold capitalize','bg-white text-primary-600 shadow-sm dark:bg-gray-800'=>$calendarScope===$scope,'text-gray-600 dark:text-gray-300'=>$calendarScope!==$scope])>{{ $scope }}</button>
                @endforeach
            </div>
            <x-filament::button @click="exportOpen=true">Marketing Export</x-filament::button>
        </div>
    </div>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Vehicle
            <select wire:model.live="carId" class="mt-1 w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-800 dark:text-white">
                <option value="">All vehicles</option>
                @foreach($carOptions as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
            </select>
        </label>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Status
            <select wire:model.live="status" class="mt-1 w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-800 dark:text-white">
                <option value="">All statuses</option>
                @foreach($statusOptions as $option)<option value="{{ $option }}">{{ ucfirst($option) }}</option>@endforeach
            </select>
        </label>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Source
            <select wire:model.live="sourceId" class="mt-1 w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-800 dark:text-white">
                <option value="">All sources</option>
                @foreach($sourceOptions as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
            </select>
        </label>
        <div class="flex items-end gap-2">
            <x-filament::button color="gray" wire:click="clearFilters" class="flex-1">Clear Filters</x-filament::button>
            <a href="{{ $newBookingUrl }}{{ $selectedDate ? '?start_datetime='.$selectedDate : '' }}" class="inline-flex min-h-10 items-center rounded-lg bg-primary-600 px-3 text-sm font-semibold text-white">+ New Booking</a>
        </div>
    </div>
    <details class="mt-4 border-t border-gray-100 pt-3 dark:border-white/10">
        <summary class="cursor-pointer text-sm font-semibold text-gray-700 dark:text-gray-200">Apple / external calendar subscription</summary>
        <p class="mt-2 text-xs text-gray-500">One-way feed. KeyFleet remains the source of truth. Regenerating revokes the previous URL.</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-filament::button size="sm" wire:click="generateCalendarFeed">{{ auth()->user()->calendar_feed_token_hash ? 'Regenerate secure URL' : 'Generate secure URL' }}</x-filament::button>
            @if(auth()->user()->calendar_feed_token_hash)<x-filament::button size="sm" color="danger" wire:click="revokeCalendarFeed">Revoke</x-filament::button>@endif
        </div>
        @if($calendarFeedUrl)
            <div class="mt-2 flex gap-2">
                <input readonly value="{{ $calendarFeedUrl }}" class="min-w-0 flex-1 rounded-lg border-gray-300 bg-gray-50 text-xs dark:border-white/10 dark:bg-gray-800 dark:text-white">
                <button type="button" @click="navigator.clipboard.writeText(@js($calendarFeedUrl))" class="rounded-lg border border-gray-300 px-3 text-sm font-semibold dark:border-white/10 dark:text-white">Copy</button>
            </div>
        @endif
    </details>
</section>
