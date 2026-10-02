<x-filament-panels::page>

    @php
        $stats = $this->bookingStats;
        $finance = $this->financialSummary;
        $trend = $this->bookingTrend;
        $upcomingBookings = $this->upcomingBookings;
        $funds = $this->funds;
        $fundTotal = $this->fundTotal;
    @endphp

    <div class="space-y-6">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white px-5 py-6 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:px-7 sm:py-7">
            <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full bg-primary-500/10 blur-3xl dark:bg-primary-400/10"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-primary-500/5 blur-3xl"></div>

            <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:border-primary-500/20 dark:bg-primary-500/10 dark:text-primary-300">
                        <span class="h-2 w-2 rounded-full bg-primary-500"></span>
                        {{ $this->getTenantName() }}
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                        {{ $this->getGreeting() }}, there!
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Here's a quick overview of your rental operations and financial performance.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="#availability" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                        Check Availability
                    </a>

                    <a href="#upcoming" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                        Upcoming Bookings
                    </a>
                </div>
            </div>
        </section>


        {{-- BOOKING STATUS --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Upcoming --}}
            <article class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Bookings</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($stats['upcoming']['count']) }}</p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 ring-1 ring-primary-100 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/20">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-white/5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-400">Receivables</span>
                        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">₱{{ number_format($stats['upcoming']['receivables'], 2) }}</span>
                    </div>
                </div>
            </article>


            {{-- Ongoing --}}
            <article class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active / On-Trip</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($stats['ongoing']['count']) }}</p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-white/5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-400">Outstanding Balance</span>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">₱{{ number_format($stats['ongoing']['receivables'], 2) }}</span>
                    </div>
                </div>
            </article>


            {{-- Finished --}}
            <article class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Trips</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($stats['finished']['count']) }}</p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-white/5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-400">Still Receivable</span>
                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($stats['finished']['receivables'], 2) }}</span>
                    </div>
                </div>
            </article>
        </section>


        {{-- FINANCIAL SUMMARY --}}
        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Financial Summary</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revenue, expenses and profitability.</p>
                </div>

                <div class="inline-flex w-fit rounded-xl bg-gray-100 p-1 dark:bg-white/5">
                    <button type="button" wire:click="$set('financialFilter', 'all')" class="rounded-lg px-3 py-2 text-xs font-semibold transition sm:px-4 {{ $financialFilter === 'all' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">All</button>
                    <button type="button" wire:click="$set('financialFilter', 'current_year')" class="rounded-lg px-3 py-2 text-xs font-semibold transition sm:px-4 {{ $financialFilter === 'current_year' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">{{ now()->year }}</button>
                    <button type="button" wire:click="$set('financialFilter', 'current_month')" class="rounded-lg px-3 py-2 text-xs font-semibold transition sm:px-4 {{ $financialFilter === 'current_month' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">{{ now()->format('M') }}</button>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- BOOKINGS --}}
                <div class="rounded-2xl border border-gray-100 bg-gray-50/80 p-4 dark:border-white/5 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Bookings</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-200/70 text-gray-600 dark:bg-white/5 dark:text-gray-300">
                            <x-filament::icon icon="heroicon-o-book-open" class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-4 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($finance['bookings']) }}</p>
                </div>

                {{-- REVENUE --}}
                <div class="rounded-2xl border border-gray-100 bg-gray-50/80 p-4 dark:border-white/5 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Revenue</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-banknotes" class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-4 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">₱{{ number_format($finance['revenue'], 2) }}</p>
                </div>

                {{-- EXPENSE --}}
                <div class="rounded-2xl border border-gray-100 bg-gray-50/80 p-4 dark:border-white/5 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Expenses</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                            <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-4 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">₱{{ number_format($finance['expenses'], 2) }}</p>
                </div>

                {{-- PROFIT --}}
                <div class="rounded-2xl border border-primary-100 bg-primary-50/70 p-4 dark:border-primary-500/10 dark:bg-primary-500/[0.07]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary-700 dark:text-primary-300">Net Profit</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-chart-bar" class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-4 text-2xl font-bold tracking-tight text-primary-700 dark:text-primary-300">₱{{ number_format($finance['profit'], 2) }}</p>
                </div>
            </div>
        </section>


        {{-- GRAPH + AVAILABILITY --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Booking Activity --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-8">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Booking Activity</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monthly booking volume.</p>
                    </div>

                    <select wire:model.live="bookingYear" class="rounded-xl border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200">
                        @foreach(range(now()->year, 2020) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-8 flex h-64 items-end gap-2 sm:gap-3">
                    @foreach($trend as $month)
                        <div class="group flex min-w-0 flex-1 flex-col items-center justify-end gap-2">
                            <div class="text-xs font-semibold text-gray-500 opacity-0 transition group-hover:opacity-100 dark:text-gray-300">{{ $month['count'] }}</div>

                            <div class="relative flex h-48 w-full items-end justify-center">
                                <div class="absolute inset-0 rounded-xl bg-gray-50 dark:bg-white/[0.025]"></div>

                                @if($month['percentage'] > 0)
                                    <div class="relative w-full max-w-8 rounded-lg bg-primary-500 transition-all duration-300 group-hover:bg-primary-600" style="height: {{ $month['percentage'] }}%;"></div>
                                @endif
                            </div>

                            <span class="text-[11px] font-medium text-gray-400">{{ $month['month'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>


            {{-- Availability --}}
            <div id="availability" class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-truck" class="h-5 w-5" />
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Car Availability</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Check your fleet instantly.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Car Type</label>
                        <select wire:model="carType" class="w-full rounded-xl border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200">
                            <option value="">All vehicle types</option>
                            @foreach($this->carTypes as $id => $type)
                                <option value="{{ $id }}">{{ $type }}</option>
                            @endforeach
                        </select>

                        @error('carType')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Date & Time</label>
                        <input type="datetime-local" wire:model="dateTime" class="w-full rounded-xl border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200" />

                        @error('dateTime')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="button" wire:click="checkAvailability" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-wait disabled:opacity-60">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                        <span wire:loading.remove>Check Availability</span>
                        <span wire:loading>Checking...</span>
                    </button>
                </div>

                <div class="mt-6 rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 p-4 dark:border-white/10 dark:bg-white/[0.025]">
                    <div class="flex gap-3">
                        <x-filament::icon icon="heroicon-o-information-circle" class="mt-0.5 h-5 w-5 shrink-0 text-primary-500" />
                        <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">Select a vehicle type and schedule to see available cars in your fleet.</p>
                    </div>
                </div>
            </div>
        </section>


        {{-- UPCOMING BOOKINGS + FUNDS --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Upcoming --}}
            <div id="upcoming" class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-7">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Upcoming Bookings</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your next scheduled rentals.</p>
                    </div>

                    <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ count($upcomingBookings) }}</span>
                </div>

                @forelse($upcomingBookings as $booking)
                    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 last:border-b-0 transition hover:bg-gray-50/70 dark:border-white/5 dark:hover:bg-white/[0.025] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-16 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gray-100 dark:bg-white/5">
                                <img src="{{ $booking['car_image'] }}" alt="{{ $booking['car_name'] }}" class="h-full w-full object-contain p-1" loading="lazy" />
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $booking['renter_name'] }}</p>

                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $booking['start']->format('M d, h:i A') }}</span>
                                    <span>→</span>
                                    <span>{{ $booking['end']->format('M d, h:i A') }}</span>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                        <x-filament::icon icon="heroicon-o-clock" class="h-3 w-3" />
                                        {{ $booking['duration'] }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 dark:text-gray-300">
                                        <x-filament::icon icon="heroicon-o-truck" class="h-3.5 w-3.5" />
                                        {{ $booking['car_name'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($booking['balance'] > 0)
                            <div class="shrink-0 sm:text-right">
                                <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">Balance</p>
                                <p class="mt-1 text-sm font-semibold text-primary-600 dark:text-primary-400">₱{{ number_format($booking['balance'], 2) }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="flex min-h-[280px] flex-col items-center justify-center p-8 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/5">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="h-7 w-7" />
                        </div>

                        <p class="mt-4 text-sm font-semibold text-gray-700 dark:text-gray-200">No upcoming bookings</p>
                        <p class="mt-1 text-sm text-gray-400">Your next scheduled rentals will appear here.</p>
                    </div>
                @endforelse
            </div>


            {{-- Funds --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Available Funds</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">₱{{ number_format($fundTotal, 2) }}</p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-wallet" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-7 space-y-5">
                    @forelse($funds as $fund)
                        @php
                            $percentage = $fundTotal > 0 ? ($fund['balance'] / $fundTotal) * 100 : 0;
                        @endphp

                        <div>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $fund['name'] }}</p>
                                <p class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($fund['balance'], 2) }}</p>
                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-primary-500 transition-all" style="width: {{ min(100, $percentage) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 p-6 text-center dark:border-white/10">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No available fund balance.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>


    {{-- AVAILABLE CARS MODAL --}}
    <div x-data="{ open: @entangle('showAvailableCarsModal') }" x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm">
        <div x-show="open" x-transition @click.outside="open = false" class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">

            {{-- Modal Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:px-6">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-truck" class="h-4 w-4" />
                        </div>

                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Available {{ $carTypeName }}</h2>
                    </div>

                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $dateTime ? \Carbon\Carbon::parse($dateTime)->format('F j, Y · g:i A') : '' }}
                    </p>
                </div>

                <button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-200">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>


            {{-- Modal Body --}}
            <div class="overflow-y-auto p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($availableCars as $car)
                        <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:-translate-y-0.5 hover:border-primary-200 hover:shadow-lg dark:border-white/10 dark:bg-gray-900 dark:hover:border-primary-500/30">

                            {{-- Car Image --}}
                            <div class="relative flex h-44 items-center justify-center overflow-hidden bg-gray-50 dark:bg-white/[0.025]">
                                <img src="{{ $car['image'] }}" alt="{{ $car['name'] }}" class="h-full w-full object-contain p-4 transition duration-300 group-hover:scale-[1.03]" loading="lazy" />

                                @if($car['type'])
                                    <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-primary-700 shadow-sm backdrop-blur dark:bg-gray-900/90 dark:text-primary-300">
                                        {{ $car['type'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-5">
                                <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $car['name'] }}</h3>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst(strtolower($car['brand'] ?? '')) }}
                                    {{ ucfirst(strtolower($car['model'] ?? '')) }}
                                    {{ $car['year'] }}
                                </p>

                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    @if($car['fuel_type'])
                                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Fuel</p>
                                            <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-200">{{ $car['fuel_type'] }}</p>
                                        </div>
                                    @endif

                                    @if($car['seat_count'])
                                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Capacity</p>
                                            <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-200">{{ $car['seat_count'] }} seats</p>
                                        </div>
                                    @endif

                                    @if($car['transmission'])
                                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Transmission</p>
                                            <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-200">{{ $car['transmission'] }}</p>
                                        </div>
                                    @endif

                                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Coding</p>
                                        <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-200">
                                            {{ $car['coding'] ? ucfirst(strtolower($car['coding'])) : 'No Coding' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>


            {{-- Modal Footer --}}
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-white/5 sm:px-6">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ count($availableCars) }} available {{ count($availableCars) === 1 ? 'vehicle' : 'vehicles' }}
                </span>

                <button type="button" @click="open = false" class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500">
                    Close
                </button>
            </div>
        </div>
    </div>

</x-filament-panels::page>