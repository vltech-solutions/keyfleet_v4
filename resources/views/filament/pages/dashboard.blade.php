<x-filament-panels::page>

    @php
        $stats = $this->bookingStats;
        $finance = $this->financialSummary;
        $trend = $this->bookingTrend;
        $upcomingBookings = $this->upcomingBookings;
        $funds = $this->funds;
        $fundTotal = $this->fundTotal;
        $financialTrend = $this->financialTrend;
        $utilization = $this->fleetUtilization;
        $sources = $this->bookingSources;
    @endphp

    <div class="space-y-6">

        {{-- HERO --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full bg-primary-500/10 blur-3xl dark:bg-primary-400/10"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-primary-500/5 blur-3xl"></div>

            <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">{{ $this->getGreeting() }}, there!</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">Here's a quick overview of your rental operations and financial performance.</p>
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


        @php
            $bookingItems = [
                ['label' => 'Upcoming', 'hint' => 'Scheduled', 'count' => $stats['upcoming']['count'], 'balance' => $stats['upcoming']['receivables'], 'balanceLabel' => 'Expected receivable', 'dot' => 'bg-primary-500'],
                ['label' => 'On Trip', 'hint' => 'Active rentals', 'count' => $stats['ongoing']['count'], 'balance' => $stats['ongoing']['receivables'], 'balanceLabel' => 'Outstanding', 'dot' => 'bg-amber-500'],
                ['label' => 'Completed', 'hint' => 'Finished trips', 'count' => $stats['finished']['count'], 'balance' => $stats['finished']['receivables'], 'balanceLabel' => 'Still receivable', 'dot' => 'bg-emerald-500'],
            ];

            $margin = $finance['revenue'] > 0
                ? ($finance['profit'] / $finance['revenue']) * 100
                : 0;
        @endphp

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Financial Snapshot --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">Financial Snapshot</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revenue and profitability.</p>
                    </div>

                    <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                        @foreach(['all' => 'All', 'current_year' => now()->year, 'current_month' => now()->format('M')] as $key => $label)
                            <button
                                wire:click="$set('financialFilter', '{{ $key }}')"
                                class="rounded-md px-2.5 py-1.5 text-xs font-medium transition
                                    {{ $financialFilter === $key
                                        ? 'bg-white text-gray-950 shadow-sm dark:bg-gray-800 dark:text-white'
                                        : 'text-gray-500 dark:text-gray-400' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-xs text-gray-400">Net Profit</p>

                    <div class="mt-1 flex items-end justify-between gap-3">
                        <p class="text-3xl font-semibold tracking-tight {{ $finance['profit'] >= 0 ? 'text-gray-950 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                            ₱{{ number_format($finance['profit'], 2) }}
                        </p>

                        <span class="text-xs font-medium {{ $margin >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($margin, 1) }}% margin
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-3 border-t border-gray-100 pt-5 dark:border-white/5">
                    <div>
                        <p class="text-[11px] text-gray-400">Revenue</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($finance['revenue'], 2) }}
                        </p>
                    </div>

                    <div class="border-l border-gray-100 pl-4 dark:border-white/5">
                        <p class="text-[11px] text-gray-400">Expenses</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($finance['expenses'], 2) }}
                        </p>
                    </div>

                    <div class="border-l border-gray-100 pl-4 dark:border-white/5">
                        <p class="text-[11px] text-gray-400">Bookings</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ number_format($finance['bookings']) }}
                        </p>
                    </div>
                </div>
            </div>


            {{-- Booking Summary --}}
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-7">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-white/5">
                    <h2 class="font-semibold text-gray-950 dark:text-white">Booking Summary</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current rental activity at a glance.</p>
                </div>

                <div class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-white/5 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    @foreach($bookingItems as $item)
                        <div class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full {{ $item['dot'] }}"></span>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $item['label'] }}</span>
                            </div>

                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-4xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                    {{ number_format($item['count']) }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $item['hint'] }}</span>
                            </div>

                            <div class="mt-6">
                                <p class="text-[11px] text-gray-400">{{ $item['balanceLabel'] }}</p>
                                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    ₱{{ number_format($item['balance'], 2) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </section>


        {{-- FINANCIAL PERFORMANCE + AVAILABILITY --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Financial Performance --}}
            @php
                $positiveMax = max(1, collect($financialTrend)->max(fn ($m) => max($m['revenue'], $m['expenses'], max($m['profit'], 0))));
                $negativeMax = abs(min(0, collect($financialTrend)->min('profit')));
                $financialRange = max(1, $positiveMax + $negativeMax);
                $baselineBottom = ($negativeMax / $financialRange) * 100;
                $baselineTop = ($positiveMax / $financialRange) * 100;
            @endphp

            <div class="relative overflow-visible rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-8">

                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Financial Performance</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revenue, expenses and profit by month.</p>
                        <p class="mt-1 text-xs text-gray-400">Hover a month to view exact values.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm bg-primary-500"></i>Revenue</span>
                            <span class="flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm bg-rose-500"></i>Expenses</span>
                            <span class="flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></i>Profit</span>
                        </div>

                        <select wire:model.live="bookingYear" class="rounded-xl border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200">
                            @foreach(range(now()->year, 2020) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="relative mt-7 overflow-visible">
                    <div class="grid h-64 grid-cols-12 gap-1 sm:gap-2">
                        @foreach($financialTrend as $month)
                            @php
                                $rh = ($month['revenue'] / $financialRange) * 100;
                                $eh = ($month['expenses'] / $financialRange) * 100;
                                $ph = (abs($month['profit']) / $financialRange) * 100;

                                $tooltipPosition = match (true) {
                                    $loop->index <= 1 => 'left-0',
                                    $loop->index >= 10 => 'right-0',
                                    default => 'left-1/2 -translate-x-1/2',
                                };
                            @endphp

                            <div class="group relative flex min-w-0 flex-col">

                                <div class="pointer-events-none absolute top-0 z-50 hidden w-48 rounded-xl border border-gray-200 bg-white p-3 text-xs shadow-xl group-hover:block dark:border-white/10 dark:bg-gray-800 {{ $tooltipPosition }}">
                                    <p class="mb-2 font-semibold text-gray-900 dark:text-white">{{ $month['month'] }} {{ $bookingYear }}</p>

                                    <div class="space-y-1.5">
                                        <div class="flex justify-between gap-3">
                                            <span class="text-gray-500 dark:text-gray-400">Revenue</span>
                                            <strong class="text-primary-600 dark:text-primary-400">₱{{ number_format($month['revenue'], 2) }}</strong>
                                        </div>

                                        <div class="flex justify-between gap-3">
                                            <span class="text-gray-500 dark:text-gray-400">Expenses</span>
                                            <strong class="text-rose-600 dark:text-rose-400">₱{{ number_format($month['expenses'], 2) }}</strong>
                                        </div>

                                        <div class="border-t border-gray-100 pt-1.5 dark:border-white/10">
                                            <div class="flex justify-between gap-3">
                                                <span class="text-gray-500 dark:text-gray-400">{{ $month['profit'] >= 0 ? 'Profit' : 'Loss' }}</span>
                                                <strong class="{{ $month['profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">₱{{ number_format(abs($month['profit']), 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative mt-auto h-52">
                                    <div class="absolute inset-x-0 top-1/4 border-t border-dashed border-gray-100 dark:border-white/5"></div>
                                    <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-gray-100 dark:border-white/5"></div>
                                    <div class="absolute inset-x-0 top-3/4 border-t border-dashed border-gray-100 dark:border-white/5"></div>
                                    <div class="absolute inset-x-0 border-t border-gray-300 dark:border-white/15" style="bottom: {{ $baselineBottom }}%"></div>

                                    <div class="absolute inset-0 flex items-stretch justify-center gap-px sm:gap-0.5">
                                        <div class="relative h-full min-w-0 flex-1">
                                            @if($month['revenue'] > 0)
                                                <div class="absolute inset-x-0 rounded-t bg-primary-500 transition group-hover:bg-primary-600" style="bottom: {{ $baselineBottom }}%; height: {{ $rh }}%;"></div>
                                            @endif
                                        </div>

                                        <div class="relative h-full min-w-0 flex-1">
                                            @if($month['expenses'] > 0)
                                                <div class="absolute inset-x-0 rounded-t bg-rose-500 transition group-hover:bg-rose-600" style="bottom: {{ $baselineBottom }}%; height: {{ $eh }}%;"></div>
                                            @endif
                                        </div>

                                        <div class="relative h-full min-w-0 flex-1">
                                            @if($month['profit'] > 0)
                                                <div class="absolute inset-x-0 rounded-t bg-emerald-500 transition group-hover:bg-emerald-600" style="bottom: {{ $baselineBottom }}%; height: {{ $ph }}%;"></div>
                                            @elseif($month['profit'] < 0)
                                                <div class="absolute inset-x-0 rounded-b bg-amber-500 transition group-hover:bg-amber-600" style="top: {{ $baselineTop }}%; height: {{ $ph }}%;"></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <span class="mt-2 truncate text-center text-[9px] font-medium text-gray-400 sm:text-[11px]">{{ $month['month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($negativeMax > 0)
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
                        <span class="h-2 w-2 rounded-sm bg-amber-500"></span>
                        Bars below the baseline represent a loss.
                    </div>
                @endif
            </div>


            {{-- CAR AVAILABILITY --}}
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
                        <span wire:loading.remove wire:target="checkAvailability">Check Availability</span>
                        <span wire:loading wire:target="checkAvailability">Checking...</span>
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


         {{-- BOOKING INSIGHTS --}}
        @php
            $sourceStops = [];
            $cursor = 0;

            foreach ($sources['items'] as $source) {
                $end = $cursor + $source['percentage'];
                $sourceStops[] = "{$source['color']} {$cursor}% {$end}%";
                $cursor = $end;
            }

            $sourceGradient = count($sourceStops) ? 'conic-gradient(' . implode(', ', $sourceStops) . ')' : 'conic-gradient(#e5e7eb 0% 100%)';
        @endphp

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Booking Sources --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-3">

                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Booking Sources
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Where bookings came from.
                    </p>
                </div>


                {{-- Doughnut --}}
                <div class="mt-6 flex justify-center">
                    <div
                        class="relative h-36 w-36 rounded-full"
                        style="background: {{ $sourceGradient }};"
                    >
                        <div class="absolute inset-[18px] flex flex-col items-center justify-center rounded-full bg-white dark:bg-gray-900">
                            <strong class="text-2xl font-semibold text-gray-950 dark:text-white">
                                {{ number_format($sources['total']) }}
                            </strong>

                            <span class="text-[11px] text-gray-400">
                                bookings
                            </span>
                        </div>
                    </div>
                </div>


                {{-- Sources --}}
                <div class="mt-6 space-y-3">
                    @forelse($sources['items'] as $source)

                        @php
                            $isOthers = $source['name'] === 'Others';
                        @endphp

                        <div
                            class="group relative flex items-center justify-between gap-3
                                {{ $isOthers ? 'border-t border-gray-100 pt-3 dark:border-white/5' : '' }}"
                        >
                            <div class="flex min-w-0 items-center gap-2">

                                <span
                                    class="h-2.5 w-2.5 shrink-0 rounded-sm"
                                    style="background-color: {{ $source['color'] }};"
                                ></span>

                                <span class="truncate text-xs {{ $isOthers ? 'font-medium text-gray-500 dark:text-gray-400' : 'text-gray-600 dark:text-gray-300' }}">
                                    {{ $source['name'] }}
                                </span>

                            </div>


                            <div class="flex shrink-0 items-center gap-2">

                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($source['count']) }}
                                </span>

                                <span class="w-9 text-right text-[10px] text-gray-400">
                                    {{ $source['percentage'] }}%
                                </span>

                            </div>


                            {{-- Tooltip --}}
                            <div class="pointer-events-none absolute bottom-full right-0 z-20 mb-2 hidden rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs shadow-lg group-hover:block dark:border-white/10 dark:bg-gray-800">

                                <strong class="text-gray-900 dark:text-white">
                                    {{ $source['name'] }}
                                </strong>

                                <p class="mt-1 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ number_format($source['count']) }}
                                    bookings · {{ $source['percentage'] }}%
                                </p>

                            </div>

                        </div>

                    @empty

                        <p class="py-6 text-center text-sm text-gray-400">
                            No booking source data.
                        </p>

                    @endforelse
                </div>

            </div>


            {{-- Fleet Utilization --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-3">
                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">Fleet Utilization</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vehicles currently on active rentals.</p>
                </div>

                <div class="mt-7 flex items-center justify-center">
                    <div class="relative h-36 w-36">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="8" class="text-gray-100 dark:text-white/5" />

                            <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="8" pathLength="100" stroke-linecap="round" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $utilization['percentage'] }}" class="text-primary-500 transition-all" />
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <strong class="text-2xl text-gray-950 dark:text-white">{{ $utilization['percentage'] }}%</strong>
                            <span class="text-[11px] text-gray-400">in use</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="border-r border-gray-100 text-center dark:border-white/10">
                        <p class="text-xs text-gray-400">On Trip</p>
                        <p class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ $utilization['active'] }}</p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs text-gray-400">Available</p>
                        <p class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ $utilization['idle'] }}</p>
                    </div>
                </div>
            </div>


            {{-- Booking Activity --}}
            @php
                $bookingMax = max(1, collect($trend)->max('count'));
                $bookingX = fn ($index) => 50 + ($index * 100);
                $bookingY = fn ($count) => 230 - (($count / $bookingMax) * 180);
                $bookingPoints = collect($trend)->map(fn ($month, $index) => $bookingX($index) . ',' . $bookingY($month['count']))->implode(' ');
                $bookingAreaPoints = '50,230 ' . $bookingPoints . ' 1150,230';
            @endphp

            <div class="relative overflow-visible rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Booking Activity</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monthly booking volume for {{ $bookingYear }}.</p>
                    </div>

                    <select wire:model.live="bookingYear" class="w-fit rounded-lg border-gray-300 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200">
                        @foreach(range(now()->year, 2020) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative mt-7 overflow-visible">
                    <svg viewBox="0 0 1200 260" class="h-64 w-full overflow-visible" preserveAspectRatio="none">
                        @foreach([50, 95, 140, 185, 230] as $gridY)
                            <line x1="50" y1="{{ $gridY }}" x2="1150" y2="{{ $gridY }}" stroke="currentColor" stroke-width="1" stroke-dasharray="5 5" class="text-gray-100 dark:text-white/5" />
                        @endforeach

                        <polygon points="{{ $bookingAreaPoints }}" fill="currentColor" class="text-primary-500 opacity-[0.07]" />

                        <polyline points="{{ $bookingPoints }}" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" class="text-primary-500" />

                        @foreach($trend as $month)
                            @php
                                $cx = $bookingX($loop->index);
                                $cy = $bookingY($month['count']);
                                $xPercent = ($cx / 1200) * 100;

                                $tooltipPosition = match (true) {
                                    $loop->index <= 1 => 'left-0',
                                    $loop->index >= 10 => 'right-0',
                                    default => 'left-1/2 -translate-x-1/2',
                                };
                            @endphp

                            <g class="group cursor-pointer">
                                {{-- Hover hit area --}}
                                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="18" fill="transparent" />

                                {{-- Point --}}
                                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="2" fill="currentColor" stroke="currentColor" stroke-width="1" vector-effect="non-scaling-stroke" class="text-primary-500 transition group-hover:text-primary-600" />
                            </g>
                        @endforeach
                    </svg>


                    {{-- Tooltips layer (HTML, positioned over SVG) --}}
                    <div class="pointer-events-none absolute inset-0">
                        @foreach($trend as $month)
                            @php
                                $cx = $bookingX($loop->index);
                                $cy = $bookingY($month['count']);
                                $xPercent = ($cx / 1200) * 100;
                                $yPercent = ($cy / 260) * 100;

                                $tooltipPosition = match (true) {
                                    $loop->index <= 1 => 'translate-x-0',
                                    $loop->index >= 10 => '-translate-x-full',
                                    default => '-translate-x-1/2',
                                };
                            @endphp

                            <div
                                class="group absolute -translate-y-full pb-3"
                                style="left: {{ $xPercent }}%; top: {{ $yPercent }}%;"
                            >
                                <div class="pointer-events-auto opacity-0 transition-opacity duration-150 group-hover:opacity-100">
                                    <div class="w-44 rounded-xl border border-gray-200 bg-white p-3 text-xs shadow-xl dark:border-white/10 dark:bg-gray-800 {{ $tooltipPosition }}">
                                        <p class="mb-2 font-semibold text-gray-900 dark:text-white">
                                            {{ $month['month'] }} {{ $bookingYear }}
                                        </p>

                                        <div class="space-y-1.5">
                                            <div class="flex justify-between gap-3">
                                                <span class="text-gray-500 dark:text-gray-400">Bookings</span>
                                                <strong class="text-primary-600 dark:text-primary-400">
                                                    {{ number_format($month['count']) }}
                                                </strong>
                                            </div>

                                            @if(isset($month['revenue']))
                                                <div class="flex justify-between gap-3">
                                                    <span class="text-gray-500 dark:text-gray-400">Revenue</span>
                                                    <strong class="text-emerald-600 dark:text-emerald-400">
                                                        ₱{{ number_format($month['revenue'], 2) }}
                                                    </strong>
                                                </div>
                                            @endif

                                            @if(isset($month['percentage']))
                                                <div class="border-t border-gray-100 pt-1.5 dark:border-white/10">
                                                    <div class="flex justify-between gap-3">
                                                        <span class="text-gray-500 dark:text-gray-400">Share</span>
                                                        <strong class="text-gray-900 dark:text-white">
                                                            {{ number_format($month['percentage'], 1) }}%
                                                        </strong>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Tooltip arrow --}}
                                        <div class="absolute -bottom-1 h-2 w-2 rotate-45 border-b border-r border-gray-200 bg-white dark:border-white/10 dark:bg-gray-800
                                            {{ $loop->index <= 1 ? 'left-4' : ($loop->index >= 10 ? 'right-4' : 'left-1/2 -translate-x-1/2') }}"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>


                    {{-- Month labels --}}
                    <div class="grid grid-cols-12 px-1">
                        @foreach($trend as $month)
                            <span class="text-center text-[10px] font-medium text-gray-400 sm:text-[11px]">{{ $month['month'] }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-white/5">
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span class="h-2 w-2 rounded-full bg-primary-500"></span>
                        Approved bookings
                    </div>

                    <span class="text-xs text-gray-400">{{ number_format(collect($trend)->sum('count')) }} total in {{ $bookingYear }}</span>
                </div>
            </div>

        </section>


        {{-- UPCOMING BOOKINGS + FUNDS --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Upcoming --}}
            <div id="upcoming" class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-7">
                <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Upcoming Bookings</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your next scheduled rentals.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ count($upcomingBookings) }}</span>

                        <a href="{{ $this->getRentalOperationsUrl() }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:border-primary-500/20 dark:hover:bg-primary-500/10 dark:hover:text-primary-300">
                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                            Rental Operations
                        </a>
                    </div>
                </div>

                @forelse($upcomingBookings as $booking)
                    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 last:border-b-0 transition hover:bg-gray-50/70 dark:border-white/5 dark:hover:bg-white/[0.025] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-16 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl">
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
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-8 w-8 text-gray-400" />
                        <p class="mt-4 text-sm font-semibold text-gray-700 dark:text-gray-200">No upcoming bookings</p>
                        <p class="mt-1 text-sm text-gray-400">Your next scheduled rentals will appear here.</p>
                    </div>
                @endforelse
            </div>


            {{-- Funds --}}
            @php
                $visibleFunds = collect($funds)->take(4);
                $remainingFunds = collect($funds)->slice(4);
                $remainingBalance = $remainingFunds->sum('balance');
            @endphp

            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Available Funds</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">₱{{ number_format($fundTotal, 2) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ count($funds) }} active {{ count($funds) === 1 ? 'fund' : 'funds' }}</p>
                    </div>

                    <x-filament::icon icon="heroicon-o-wallet" class="h-6 w-6 text-primary-500" />
                </div>

                <div class="mt-7 space-y-4">
                    @forelse($visibleFunds as $fund)
                        @php
                            $percentage = $fundTotal > 0 ? ($fund['balance'] / $fundTotal) * 100 : 0;
                        @endphp

                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-4">
                                <p class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-gray-200" title="{{ $fund['name'] }}">{{ $fund['name'] }}</p>
                                <p class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($fund['balance'], 2) }}</p>
                            </div>

                            <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-primary-500" style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 p-6 text-center dark:border-white/10">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No available fund balance.</p>
                        </div>
                    @endforelse

                    @if($remainingFunds->isNotEmpty())
                        @php
                            $otherPercentage = $fundTotal > 0 ? ($remainingBalance / $fundTotal) * 100 : 0;
                        @endphp

                        <div class="border-t border-gray-100 pt-4 dark:border-white/5">
                            <div class="mb-1.5 flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-2">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Other Funds</p>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-white/5 dark:text-gray-400">{{ $remainingFunds->count() }}</span>
                                </div>

                                <p class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($remainingBalance, 2) }}</p>
                            </div>

                            <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-gray-400 dark:bg-gray-500" style="width: {{ min(100, $otherPercentage) }}%"></div>
                            </div>
                        </div>
                    @endif
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
                        <x-filament::icon icon="heroicon-o-truck" class="h-5 w-5 text-primary-500" />
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Available {{ $carTypeName }}</h2>
                    </div>

                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $dateTime ? \Carbon\Carbon::parse($dateTime)->format('F j, Y · g:i A') : '' }}</p>
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
                            <div class="relative flex h-44 items-center justify-center overflow-hidden bg-gray-50 dark:bg-white/[0.025]">
                                <img src="{{ $car['image'] }}" alt="{{ $car['name'] }}" class="h-full w-full object-contain p-4 transition duration-300 group-hover:scale-[1.03]" loading="lazy" />

                                @if($car['type'])
                                    <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-primary-700 shadow-sm backdrop-blur dark:bg-gray-900/90 dark:text-primary-300">{{ $car['type'] }}</span>
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
                                        <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-200">{{ $car['coding'] ? ucfirst(strtolower($car['coding'])) : 'No Coding' }}</p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>


            {{-- Modal Footer --}}
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-white/5 sm:px-6">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ count($availableCars) }} {{ count($availableCars) === 1 ? 'available vehicle' : 'available vehicles' }}</span>

                <button type="button" @click="open = false" class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500">Close</button>
            </div>
        </div>
    </div>

</x-filament-panels::page>