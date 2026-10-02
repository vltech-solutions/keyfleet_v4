<x-filament-panels::page>

    @php
        $releases = $this->releases;
        $returns = $this->returns;
        $fleet = $this->fleetStatus;
        $outstanding = $this->outstandingBookings;
    @endphp

    <div class="space-y-6">

        {{-- HEADER / DATE NAVIGATION --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-primary-500/10 blur-3xl dark:bg-primary-400/10"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-primary-500/5 blur-3xl"></div>

            <div class="relative flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Rental Operations</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $this->selectedDateLabel }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="previousDay" wire:loading.attr="disabled" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white" title="Previous day">
                        <x-filament::icon icon="heroicon-o-chevron-left" class="h-4 w-4" />
                    </button>

                    <input type="date" wire:model.live="selectedDate" class="rounded-xl border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200" />

                    <button type="button" wire:click="nextDay" wire:loading.attr="disabled" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white" title="Next day">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
                    </button>

                    @if(! $this->isToday)
                        <button type="button" wire:click="goToToday" class="rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 transition hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:bg-primary-500/20">Today</button>
                    @endif
                </div>
            </div>
        </section>


        {{-- SUMMARY CARDS --}}
        <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">

            {{-- Releases --}}
            <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Releases</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $releases->count() }}</p>
                        <p class="mt-1 text-xs text-gray-400">Scheduled for selected date</p>
                    </div>

                    <div class="hidden h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 sm:flex">
                        <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-5 w-5" />
                    </div>
                </div>
            </article>


            {{-- Returns --}}
            <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Returns</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $returns->count() }}</p>
                        <p class="mt-1 text-xs text-gray-400">Expected on selected date</p>
                    </div>

                    <div class="hidden h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 sm:flex">
                        <x-filament::icon icon="heroicon-o-arrow-down-left" class="h-5 w-5" />
                    </div>
                </div>
            </article>


            {{-- Receivables --}}
            <article class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-500/20 dark:bg-gray-900 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Receivables</p>
                        <p class="mt-2 truncate text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">₱{{ number_format($this->outstandingBalance, 2) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $this->outstandingCount }} {{ $this->outstandingCount === 1 ? 'booking' : 'bookings' }}</p>
                    </div>

                    <div class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 sm:flex">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
                    </div>
                </div>
            </article>


            {{-- Available Cars --}}
            <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Available Now</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $fleet['available']['count'] }}</p>
                        <p class="mt-1 text-xs text-gray-400">of {{ $fleet['total'] }} vehicles</p>
                    </div>

                    <div class="hidden h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 sm:flex">
                        <x-filament::icon icon="heroicon-o-truck" class="h-5 w-5" />
                    </div>
                </div>
            </article>
        </section>


        {{-- RELEASES + RETURNS --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- SCHEDULED RELEASES --}}
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:px-6">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">Scheduled Releases</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vehicles scheduled to go out.</p>
                    </div>

                    <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $releases->count() }}</span>
                </div>

                @forelse($releases as $booking)
                    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 last:border-0 transition hover:bg-gray-50/70 dark:border-white/5 dark:hover:bg-white/[0.025] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl ">
                                <img src="{{ $this->getCarImageUrl($booking->car) }}" alt="{{ $this->getCarName($booking) }}" class="h-full w-full object-contain p-1" loading="lazy" />
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $booking->renter_name }}</p>
                                <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $this->getCarName($booking) }}</p>

                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-1 text-[11px] font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-3 w-3" />
                                        {{ \Carbon\Carbon::parse($booking->start_datetime)->format('M d') }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                        <x-filament::icon icon="heroicon-o-clock" class="h-3 w-3" />
                                        {{ \Carbon\Carbon::parse($booking->start_datetime)->format('h:i A') }}
                                    </span>

                                    <span class="text-[11px] text-gray-400">{{ $this->getBookingReference($booking) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            @if(($booking->balance ?? 0) > 0)
                                <div class="sm:text-right">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Balance</p>
                                    <p class="mt-1 text-sm font-semibold text-amber-600 dark:text-amber-400">₱{{ number_format($booking->balance, 2) }}</p>
                                </div>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Paid</span>
                            @endif

                            <a href="{{ $this->getBookingUrl($booking->id) }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-primary-500/20 dark:hover:bg-primary-500/10 dark:hover:text-primary-400" title="Open booking">
                                <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-[260px] flex-col items-center justify-center p-8 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/5">
                            <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-6 w-6" />
                        </div>

                        <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">No scheduled releases</p>
                        <p class="mt-1 max-w-xs text-sm text-gray-400">Nothing is scheduled to go out on this date.</p>
                    </div>
                @endforelse
            </div>


            {{-- SCHEDULED RETURNS --}}
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:px-6">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">Scheduled Returns</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vehicles expected back.</p>
                    </div>

                    <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $returns->count() }}</span>
                </div>

                @forelse($returns as $booking)
                    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 last:border-0 transition hover:bg-gray-50/70 dark:border-white/5 dark:hover:bg-white/[0.025] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 dark:border-white/5 dark:bg-white/[0.03]">
                                <img src="{{ $this->getCarImageUrl($booking->car) }}" alt="{{ $this->getCarName($booking) }}" class="h-full w-full object-contain p-1" loading="lazy" />
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $booking->renter_name }}</p>
                                <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $this->getCarName($booking) }}</p>

                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-1 text-[11px] font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-3 w-3" />
                                        {{ \Carbon\Carbon::parse($booking->end_datetime)->format('M d') }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                        <x-filament::icon icon="heroicon-o-clock" class="h-3 w-3" />
                                        {{ \Carbon\Carbon::parse($booking->end_datetime)->format('h:i A') }}
                                    </span>

                                    <span class="text-[11px] text-gray-400">{{ $this->getBookingReference($booking) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            @if(($booking->balance ?? 0) > 0)
                                <div class="sm:text-right">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Balance</p>
                                    <p class="mt-1 text-sm font-semibold text-amber-600 dark:text-amber-400">₱{{ number_format($booking->balance, 2) }}</p>
                                </div>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Paid</span>
                            @endif

                            <a href="{{ $this->getBookingUrl($booking->id) }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-primary-500/20 dark:hover:bg-primary-500/10 dark:hover:text-primary-400" title="Open booking">
                                <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-[260px] flex-col items-center justify-center p-8 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/5">
                            <x-filament::icon icon="heroicon-o-arrow-down-left" class="h-6 w-6" />
                        </div>

                        <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">No scheduled returns</p>
                        <p class="mt-1 max-w-xs text-sm text-gray-400">Nothing is expected back on this date.</p>
                    </div>
                @endforelse
            </div>
        </section>


        {{-- FLEET STATUS --}}
        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">Fleet Status</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current vehicle availability across your fleet.</p>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Live availability
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/5 dark:bg-white/[0.025]">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Fleet</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">{{ $fleet['total'] }}</p>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 dark:border-emerald-500/10 dark:bg-emerald-500/[0.07]">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Available</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ $fleet['available']['count'] }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/5 dark:bg-white/[0.025]">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Unavailable</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">{{ $fleet['unavailable']['count'] }}</p>
                </div>
            </div>

            @if($fleet['unavailable']['count'] > 0)
                <div class="mt-6 border-t border-gray-100 pt-5 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Currently Unavailable</p>
                            <p class="mt-0.5 text-xs text-gray-400">Vehicles currently not available for booking.</p>
                        </div>

                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-300">{{ $fleet['unavailable']['count'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($fleet['unavailable']['cars'] as $car)
                            <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 transition hover:border-gray-200 hover:bg-gray-50 dark:border-white/5 dark:bg-white/[0.025] dark:hover:border-white/10 dark:hover:bg-white/[0.04]">
                                <div class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl ">
                                    <img src="{{ $this->getCarImageUrl($car) }}" alt="{{ $car->name ?? 'Vehicle' }}" class="h-full w-full object-contain p-1" loading="lazy" />
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $car->name ?? 'Vehicle #' . $car->id }}</p>

                                    @if($car->brand || $car->model)
                                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ trim(($car->brand ?? '') . ' ' . ($car->model ?? '')) }}</p>
                                    @endif

                                    @if($car->plate_number ?? null)
                                        <p class="mt-1 text-[11px] font-medium text-gray-400">{{ $car->plate_number }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-5 text-center dark:border-emerald-500/20 dark:bg-emerald-500/[0.04]">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                    </div>

                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">All vehicles are currently available</p>
                </div>
            @endif
        </section>


        {{-- OUTSTANDING BALANCES --}}
        <section id="outstanding-balances" class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

            {{-- Header --}}
            <div class="flex flex-col justify-between gap-4 border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            <x-filament::icon icon="heroicon-o-banknotes" class="h-4 w-4" />
                        </div>

                        <h2 class="font-semibold text-gray-950 dark:text-white">Outstanding Balances</h2>
                    </div>

                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Approved bookings with remaining receivables.</p>
                </div>

                <div class="sm:text-right">
                    <p class="text-xs font-medium text-gray-400">Total Outstanding</p>
                    <p class="mt-1 text-lg font-bold text-amber-600 dark:text-amber-400">₱{{ number_format($this->outstandingBalance, 2) }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">{{ $this->outstandingCount }} {{ $this->outstandingCount === 1 ? 'booking' : 'bookings' }}</p>
                </div>
            </div>


            @if($outstanding->count())

                {{-- DESKTOP TABLE --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full">
                        <thead class="border-b border-gray-100 bg-gray-50/70 dark:border-white/5 dark:bg-white/[0.02]">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Vehicle</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Renter</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Booking</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Rental Period</th>
                                <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">Balance</th>
                                <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($outstanding as $booking)
                                <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.025]">
                                    {{-- Vehicle --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl">
                                                <img src="{{ $this->getCarImageUrl($booking->car) }}" alt="{{ $this->getCarName($booking) }}" class="h-full w-full object-contain p-1" loading="lazy" />
                                            </div>

                                            <div class="min-w-0">
                                                <p class="max-w-[180px] truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $this->getCarName($booking) }}</p>

                                                @if($booking->car?->plate_number)
                                                    <p class="mt-0.5 text-xs text-gray-400">{{ $booking->car->plate_number }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Renter --}}
                                    <td class="px-6 py-4">
                                        <p class="max-w-[180px] truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ $booking->renter_name }}</p>
                                    </td>

                                    {{-- Booking --}}
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">{{ $this->getBookingReference($booking) }}</span>
                                    </td>

                                    {{-- Rental Period --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-2">
                                            <x-filament::icon icon="heroicon-o-calendar-days" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />

                                            <div>
                                                <p class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('M d, Y') }}</p>
                                                <p class="mt-1 text-xs text-gray-400">to {{ \Carbon\Carbon::parse($booking->end_datetime)->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Balance --}}
                                    <td class="px-6 py-4 text-right">
                                        <p class="whitespace-nowrap text-sm font-bold text-amber-600 dark:text-amber-400">₱{{ number_format($booking->balance, 2) }}</p>
                                    </td>

                                    {{-- Action --}}
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ $this->getBookingUrl($booking->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-primary-500/20 dark:hover:bg-primary-500/10 dark:hover:text-primary-400" title="Open booking">
                                            <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                {{-- MOBILE CARDS --}}
                <div class="divide-y divide-gray-100 dark:divide-white/5 md:hidden">
                    @foreach($outstanding as $booking)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 dark:border-white/5 dark:bg-white/[0.03]">
                                    <img src="{{ $this->getCarImageUrl($booking->car) }}" alt="{{ $this->getCarName($booking) }}" class="h-full w-full object-contain p-1" loading="lazy" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $booking->renter_name }}</p>
                                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $this->getCarName($booking) }}</p>
                                        </div>

                                        <p class="shrink-0 text-sm font-bold text-amber-600 dark:text-amber-400">₱{{ number_format($booking->balance, 2) }}</p>
                                    </div>

                                    <div class="mt-3 flex items-center gap-1.5 text-[11px] text-gray-400">
                                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-3.5 w-3.5" />
                                        <span>{{ \Carbon\Carbon::parse($booking->start_datetime)->format('M d') }} → {{ \Carbon\Carbon::parse($booking->end_datetime)->format('M d, Y') }}</span>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <span class="truncate text-[11px] text-gray-400">{{ $this->getBookingReference($booking) }}</span>

                                        <a href="{{ $this->getBookingUrl($booking->id) }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400">
                                            Open
                                            <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-3.5 w-3.5" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>


                {{-- PAGINATION --}}
                <div class="border-t border-gray-100 px-5 py-4 dark:border-white/5 sm:px-6">
                    {{ $outstanding->links() }}
                </div>

            @else

                {{-- Empty State --}}
                <div class="flex min-h-[240px] flex-col items-center justify-center p-8 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-6 w-6" />
                    </div>

                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">No outstanding balances</p>
                    <p class="mt-1 text-sm text-gray-400">All approved bookings are fully paid.</p>
                </div>

            @endif
        </section>

    </div>

</x-filament-panels::page>