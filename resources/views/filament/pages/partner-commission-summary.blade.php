<x-filament::page>

    @php
        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $partnerOptions = \App\Models\Partners::query()
            ->withCount('cars')
            ->orderBy('name')
            ->get()
            ->map(function ($partner) {
                return [
                    'value' => (string) $partner->id,
                    'label' => $partner->name,
                    'description' => $partner->cars_count
                        . ' '
                        . ($partner->cars_count === 1 ? 'vehicle' : 'vehicles'),
                ];
            })
            ->prepend([
                'value' => 'all',
                'label' => 'All Partners',
                'description' => 'Include all fleet partners',
            ])
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | Commission Distribution
        |--------------------------------------------------------------------------
        */

        $split = $this->commissionSplit;

        $splitGradient = $split['total'] > 0
            ? 'conic-gradient(#10b981 0% '
                . $split['partner_percentage']
                . '%, #f59e0b '
                . $split['partner_percentage']
                . '% 100%)'
            : 'conic-gradient(#e5e7eb 0% 100%)';


        /*
        |--------------------------------------------------------------------------
        | Partner Performance
        |--------------------------------------------------------------------------
        */

        $partnerPerformance = $this->partnerPerformance;

        $maxPartnerRevenue = max(
            1,
            collect($partnerPerformance)->max('revenue') ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | Period Label
        |--------------------------------------------------------------------------
        */

        $periodLabel = match ($period) {
            'this_month' => now()->format('F Y'),

            'this_year' => now()->format('Y'),

            'all_time' => 'All Time',

            'custom' => $startDate && $endDate
                ? \Carbon\Carbon::parse($startDate)->format('M d, Y')
                    . ' – '
                    . \Carbon\Carbon::parse($endDate)->format('M d, Y')
                : 'Custom Period',

            default => 'Selected Period',
        };


        /*
        |--------------------------------------------------------------------------
        | Selected Partner
        |--------------------------------------------------------------------------
        */

        $selectedPartner = $partnerId !== 'all'
            ? \App\Models\Partners::find($partnerId)
            : null;


        /*
        |--------------------------------------------------------------------------
        | Car Breakdown
        |--------------------------------------------------------------------------
        */

        $paginatedCars = $this->paginatedCarBreakdown;
    @endphp


    <div class="space-y-6">

        {{-- REPORT NOTE --}}
        <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.025]">

            <x-filament::icon
                icon="heroicon-o-information-circle"
                class="mt-0.5 h-5 w-5 shrink-0 text-primary-500"
            />

            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Partner Commission Report
                </p>

                <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Only fully paid and approved partner bookings with recorded
                    commission values are included in this report.
                </p>
            </div>

        </div>


        {{-- REPORT FILTERS --}}
        <section
            class="relative z-20 overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"
        >

            {{-- Filter Header --}}
            <div class="flex flex-col gap-2 rounded-t-2xl border-b border-gray-100 px-5 py-4 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div class="flex items-center gap-3">

                    <x-filament::icon
                        icon="heroicon-o-adjustments-horizontal"
                        class="h-5 w-5 text-gray-400"
                    />

                    <div>
                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                            Report Filters
                        </h2>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Filter commission results by partner and reporting period.
                        </p>
                    </div>

                </div>


                <span class="text-xs text-gray-400">
                    Updates automatically
                </span>

            </div>


            <div class="p-5 sm:p-6">

                {{-- Main Filters --}}
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

                    {{-- Partner --}}
                    <x-searchable-select
                        model="partnerId"
                        label="Partner"
                        placeholder="All Partners"
                        search-placeholder="Search partner..."
                        empty-text="No partner found"
                        :options="$partnerOptions"
                    />


                    {{-- Reporting Period --}}
                    <div>

                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Reporting Period
                        </label>

                        <div class="grid grid-cols-2 rounded-xl bg-gray-100 p-1 dark:bg-white/5 sm:grid-cols-4">

                            <button
                                type="button"
                                wire:click="setPeriod('this_month')"
                                class="rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ $period === 'this_month'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                This Month
                            </button>


                            <button
                                type="button"
                                wire:click="setPeriod('this_year')"
                                class="rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ $period === 'this_year'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                This Year
                            </button>


                            <button
                                type="button"
                                wire:click="setPeriod('all_time')"
                                class="rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ $period === 'all_time'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                All Time
                            </button>


                            <button
                                type="button"
                                wire:click="setPeriod('custom')"
                                class="rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ $period === 'custom'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                Custom
                            </button>

                        </div>

                    </div>

                </div>


                {{-- Custom Date --}}
                @if($period === 'custom')

                    <div class="relative mt-5 overflow-visible rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/5 dark:bg-white/[0.025]">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Date From
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="startDate"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm
                                        focus:border-primary-500 focus:ring-primary-500
                                        dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                />
                            </div>


                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Date To
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="endDate"
                                    min="{{ $startDate }}"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm
                                        focus:border-primary-500 focus:ring-primary-500
                                        dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                />
                            </div>

                        </div>

                    </div>

                @endif


                {{-- Current Filter Context --}}
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-gray-100 pt-4 text-xs dark:border-white/5">

                    <div class="flex items-center gap-2">

                        <span class="text-gray-400">
                            Partner
                        </span>

                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            {{ $selectedPartner?->name ?? 'All Partners' }}
                        </span>

                    </div>


                    <span class="hidden h-3 w-px bg-gray-200 dark:bg-white/10 sm:block"></span>


                    <div class="flex items-center gap-2">

                        <span class="text-gray-400">
                            Period
                        </span>

                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            {{ $periodLabel }}
                        </span>

                    </div>


                    <span class="hidden h-3 w-px bg-gray-200 dark:bg-white/10 sm:block"></span>


                    <div class="flex items-center gap-2">

                        <span class="text-gray-400">
                            Qualified Bookings
                        </span>

                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            {{ number_format($this->qualifiedBookings) }}
                        </span>

                    </div>

                </div>

            </div>

        </section>


        {{-- KPI SUMMARY --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Tie-Up Revenue --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Tie-Up Revenue
                        </p>

                        <p class="mt-2 truncate text-2xl font-semibold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                            ₱{{ number_format($tieUpRevenue, 2) }}
                        </p>

                    </div>


                    <x-filament::icon
                        icon="heroicon-o-banknotes"
                        class="h-6 w-6 shrink-0 text-primary-500"
                    />

                </div>


                <p class="mt-5 text-xs text-gray-400">
                    Fully paid approved partner bookings
                </p>

            </div>


            {{-- Partner Income --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Partner Income
                        </p>

                        <p class="mt-2 truncate text-2xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400 sm:text-3xl">
                            ₱{{ number_format($partnerCommission, 2) }}
                        </p>

                    </div>


                    <x-filament::icon
                        icon="heroicon-o-user-group"
                        class="h-6 w-6 shrink-0 text-emerald-500"
                    />

                </div>


                <p class="mt-5 text-xs text-gray-400">
                    Earnings allocated to fleet partners
                </p>

            </div>


            {{-- Company Commission --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Company Commission
                        </p>

                        <p class="mt-2 truncate text-2xl font-semibold tracking-tight text-amber-600 dark:text-amber-400 sm:text-3xl">
                            ₱{{ number_format($companyEarnings, 2) }}
                        </p>

                    </div>


                    <x-filament::icon
                        icon="heroicon-o-building-office-2"
                        class="h-6 w-6 shrink-0 text-amber-500"
                    />

                </div>


                <p class="mt-5 text-xs text-gray-400">
                    Company's share from tie-up bookings
                </p>

            </div>

        </section>


        {{-- COMMISSION ANALYSIS --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Commission Distribution --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-4">

                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Commission Distribution
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Distribution of earnings between partners and the company.
                    </p>
                </div>


                <div class="mt-7 flex justify-center">

                    <div
                        class="relative h-44 w-44 rounded-full"
                        style="background: {{ $splitGradient }};"
                    >

                        <div class="absolute inset-[22px] flex flex-col items-center justify-center rounded-full bg-white dark:bg-gray-900">

                            <span class="text-xs text-gray-400">
                                Distributed
                            </span>

                            <strong class="mt-1 max-w-[120px] truncate text-lg font-semibold text-gray-950 dark:text-white">
                                ₱{{ number_format($split['total'], 2) }}
                            </strong>

                        </div>

                    </div>

                </div>


                <div class="mt-7 space-y-4">

                    {{-- Partner Share --}}
                    <div class="flex items-center justify-between gap-4">

                        <div class="flex min-w-0 items-center gap-2">

                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm bg-emerald-500"></span>

                            <span class="truncate text-sm text-gray-500 dark:text-gray-300">
                                Partner Income
                            </span>

                        </div>


                        <div class="shrink-0 text-right">

                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($split['partner_percentage'], 1) }}%
                            </p>

                            <p class="mt-0.5 text-xs text-gray-400">
                                ₱{{ number_format($split['partner'], 2) }}
                            </p>

                        </div>

                    </div>


                    {{-- Company Share --}}
                    <div class="flex items-center justify-between gap-4">

                        <div class="flex min-w-0 items-center gap-2">

                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm bg-amber-500"></span>

                            <span class="truncate text-sm text-gray-500 dark:text-gray-300">
                                Company Commission
                            </span>

                        </div>


                        <div class="shrink-0 text-right">

                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($split['company_percentage'], 1) }}%
                            </p>

                            <p class="mt-0.5 text-xs text-gray-400">
                                ₱{{ number_format($split['company'], 2) }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Top Partner Performance --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-8">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <h2 class="font-semibold text-gray-950 dark:text-white">
                            Top Partner Performance
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Highest revenue-generating partners for the selected period.
                        </p>

                    </div>


                    <span class="shrink-0 text-xs text-gray-400">
                        Top {{ count($partnerPerformance) }}
                    </span>

                </div>


                <div class="mt-7 space-y-6">

                    @forelse($partnerPerformance as $partner)

                        @php
                            $partnerBarWidth = $maxPartnerRevenue > 0
                                ? ($partner['revenue'] / $maxPartnerRevenue) * 100
                                : 0;
                        @endphp

                        <div>

                            <div class="mb-2 flex items-start justify-between gap-4">

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $partner['name'] }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-400">
                                        {{ number_format($partner['bookings']) }}
                                        {{ $partner['bookings'] === 1 ? 'booking' : 'bookings' }}

                                        ·

                                        {{ number_format($partner['cars']) }}
                                        {{ $partner['cars'] === 1 ? 'vehicle' : 'vehicles' }}
                                    </p>

                                </div>


                                <p class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white">
                                    ₱{{ number_format($partner['revenue'], 2) }}
                                </p>

                            </div>


                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">

                                <div
                                    class="h-full rounded-full bg-primary-500"
                                    style="width: {{ min(100, $partnerBarWidth) }}%"
                                ></div>

                            </div>


                            <div class="mt-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-xs">

                                <span class="text-gray-400">
                                    Partner:
                                    <strong class="font-medium text-emerald-600 dark:text-emerald-400">
                                        ₱{{ number_format($partner['partner_income'], 2) }}
                                    </strong>
                                </span>


                                <span class="text-gray-400">
                                    Company:
                                    <strong class="font-medium text-amber-600 dark:text-amber-400">
                                        ₱{{ number_format($partner['company_income'], 2) }}
                                    </strong>
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="flex min-h-[260px] flex-col items-center justify-center text-center">

                            <x-filament::icon
                                icon="heroicon-o-chart-bar"
                                class="h-8 w-8 text-gray-300 dark:text-gray-600"
                            />

                            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">
                                No partner performance data
                            </p>

                            <p class="mt-1 max-w-sm text-xs text-gray-400">
                                No qualified bookings match the selected filters.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </section>


        {{-- PARTNER SUMMARY --}}
        <section>

            <div class="mb-4">

                <h2 class="font-semibold text-gray-950 dark:text-white">
                    Partner Summary
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Revenue, income, and commission totals grouped by partner.
                </p>

            </div>


            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

                {{ $this->table }}

            </div>

        </section>


        {{-- CAR REVENUE BREAKDOWN --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

            {{-- Header --}}
            <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>

                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Car Revenue Breakdown
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Revenue and commission performance by partner vehicle.
                    </p>

                    <p class="mt-1 text-xs text-gray-400">
                        Showing
                        {{ $paginatedCars->total > 0
                            ? (($paginatedCars->page - 1) * $paginatedCars->perPage + 1)
                            : 0 }}
                        to
                        {{ min(
                            $paginatedCars->page * $paginatedCars->perPage,
                            $paginatedCars->total
                        ) }}
                        of
                        {{ number_format($paginatedCars->total) }}
                        vehicles
                    </p>

                </div>


                <div class="flex items-center gap-2">

                    <span class="text-xs text-gray-400">
                        Rows
                    </span>

                    <select
                        wire:model.live="carPerPage"
                        class="rounded-lg border-gray-300 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-gray-700 shadow-sm
                            focus:border-primary-500 focus:ring-primary-500
                            dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>

                </div>

            </div>


            @if($paginatedCars->total > 0)

                {{-- DESKTOP TABLE --}}
                <div class="hidden overflow-x-auto md:block">

                    <table class="w-full">

                        <thead class="border-b border-gray-100 bg-gray-50/70 dark:border-white/5 dark:bg-white/[0.02]">

                            <tr>

                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Vehicle
                                </th>

                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Partner
                                </th>

                                <th class="px-6 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Bookings
                                </th>

                                <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Revenue
                                </th>

                                <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Partner Income
                                </th>

                                <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Company Commission
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">

                            @foreach($paginatedCars->items as $car)

                                @php
                                    $bookingCount = $car->bookings->count();

                                    $carRevenue = (float) $car->bookings
                                        ->sum('paid_amount');

                                    $carPartnerIncome = (float) $car->bookings
                                        ->sum('partner_commission');

                                    $carCompanyIncome = (float) $car->bookings
                                        ->sum('company_earnings');
                                @endphp


                                <tr
                                    wire:key="partner-car-row-{{ $car->id }}"
                                    class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.025]"
                                >

                                    {{-- Vehicle --}}
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-4">

                                            <div class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-white/[0.025]">

                                                @if($car->image)

                                                    <img
                                                        src="{{ \Illuminate\Support\Facades\Storage::url($car->image) }}"
                                                        alt="{{ $car->name }}"
                                                        class="h-full w-full object-contain p-1"
                                                        loading="lazy"
                                                    />

                                                @else

                                                    <x-filament::icon
                                                        icon="heroicon-o-truck"
                                                        class="h-6 w-6 text-gray-300 dark:text-gray-600"
                                                    />

                                                @endif

                                            </div>


                                            <div class="min-w-0">

                                                <p class="max-w-[220px] truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $car->name }}
                                                </p>


                                                @if(
                                                    $car->brand
                                                    || $car->model
                                                    || $car->year
                                                )

                                                    <p class="mt-1 max-w-[260px] truncate text-xs text-gray-500 dark:text-gray-400">

                                                        {{ trim(
                                                            ($car->brand ?? '')
                                                            . ' '
                                                            . ($car->model ?? '')
                                                        ) }}

                                                        @if($car->year)
                                                            · {{ $car->year }}
                                                        @endif

                                                    </p>

                                                @endif


                                                @if($car->plate_number ?? null)

                                                    <p class="mt-1 text-[11px] text-gray-400">
                                                        {{ $car->plate_number }}
                                                    </p>

                                                @endif

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Partner --}}
                                    <td class="px-6 py-4">

                                        @if($car->partner)

                                            <span class="inline-flex rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                                {{ $car->partner->name }}
                                            </span>

                                        @else

                                            <span class="text-sm text-gray-400">
                                                —
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Booking Count --}}
                                    <td class="px-6 py-4 text-center">

                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($bookingCount) }}
                                        </p>

                                    </td>


                                    {{-- Revenue --}}
                                    <td class="px-6 py-4 text-right">

                                        <p class="whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                            ₱{{ number_format($carRevenue, 2) }}
                                        </p>

                                    </td>


                                    {{-- Partner Income --}}
                                    <td class="px-6 py-4 text-right">

                                        <p class="whitespace-nowrap text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                            ₱{{ number_format($carPartnerIncome, 2) }}
                                        </p>

                                    </td>


                                    {{-- Company Commission --}}
                                    <td class="px-6 py-4 text-right">

                                        <p class="whitespace-nowrap text-sm font-semibold text-amber-600 dark:text-amber-400">
                                            ₱{{ number_format($carCompanyIncome, 2) }}
                                        </p>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- MOBILE CARDS --}}
                <div class="divide-y divide-gray-100 dark:divide-white/5 md:hidden">

                    @foreach($paginatedCars->items as $car)

                        @php
                            $bookingCount = $car->bookings->count();

                            $carRevenue = (float) $car->bookings
                                ->sum('paid_amount');

                            $carPartnerIncome = (float) $car->bookings
                                ->sum('partner_commission');

                            $carCompanyIncome = (float) $car->bookings
                                ->sum('company_earnings');
                        @endphp


                        <article
                            wire:key="partner-car-card-{{ $car->id }}"
                            class="p-4"
                        >

                            {{-- Vehicle Header --}}
                            <div class="flex items-start gap-3">

                                <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-white/[0.025]">

                                    @if($car->image)

                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url($car->image) }}"
                                            alt="{{ $car->name }}"
                                            class="h-full w-full object-contain p-1"
                                            loading="lazy"
                                        />

                                    @else

                                        <x-filament::icon
                                            icon="heroicon-o-truck"
                                            class="h-6 w-6 text-gray-300 dark:text-gray-600"
                                        />

                                    @endif

                                </div>


                                <div class="min-w-0 flex-1">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $car->name }}
                                            </p>


                                            @if($car->brand || $car->model)

                                                <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                                    {{ trim(
                                                        ($car->brand ?? '')
                                                        . ' '
                                                        . ($car->model ?? '')
                                                    ) }}

                                                    @if($car->year)
                                                        · {{ $car->year }}
                                                    @endif
                                                </p>

                                            @endif

                                        </div>


                                        @if($car->plate_number ?? null)

                                            <span class="shrink-0 rounded-full bg-gray-100 px-2 py-1 text-[10px] font-medium text-gray-500 dark:bg-white/5 dark:text-gray-300">
                                                {{ $car->plate_number }}
                                            </span>

                                        @endif

                                    </div>


                                    @if($car->partner)

                                        <div class="mt-2">

                                            <span class="inline-flex rounded-full bg-primary-50 px-2 py-1 text-[10px] font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                                {{ $car->partner->name }}
                                            </span>

                                        </div>

                                    @endif

                                </div>

                            </div>


                            {{-- Main Stats --}}
                            <div class="mt-4 grid grid-cols-2 divide-x divide-gray-100 rounded-xl bg-gray-50/70 py-3 dark:divide-white/5 dark:bg-white/[0.025]">

                                <div class="px-4">

                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                                        Bookings
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($bookingCount) }}
                                    </p>

                                </div>


                                <div class="px-4 text-right">

                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                                        Revenue
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                        ₱{{ number_format($carRevenue, 2) }}
                                    </p>

                                </div>

                            </div>


                            {{-- Commission Breakdown --}}
                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">

                                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2.5 dark:border-white/5">

                                    <span class="text-xs text-gray-400">
                                        Partner Income
                                    </span>

                                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        ₱{{ number_format($carPartnerIncome, 2) }}
                                    </span>

                                </div>


                                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2.5 dark:border-white/5">

                                    <span class="text-xs text-gray-400">
                                        Company
                                    </span>

                                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                                        ₱{{ number_format($carCompanyIncome, 2) }}
                                    </span>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- PAGINATION --}}
                <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                    <p class="text-xs text-gray-400">

                        Showing

                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ $paginatedCars->total > 0
                                ? (($paginatedCars->page - 1) * $paginatedCars->perPage + 1)
                                : 0 }}
                        </span>

                        –

                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ min(
                                $paginatedCars->page * $paginatedCars->perPage,
                                $paginatedCars->total
                            ) }}
                        </span>

                        of

                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ number_format($paginatedCars->total) }}
                        </span>

                    </p>


                    @if($paginatedCars->total > $paginatedCars->perPage)

                        @php
                            $lastPage = (int) $paginatedCars->lastPage;
                            $currentPage = (int) $paginatedCars->page;

                            $startPage = max(
                                $currentPage - 2,
                                1
                            );

                            $endPage = min(
                                $currentPage + 2,
                                $lastPage
                            );
                        @endphp


                        <div class="flex items-center justify-between gap-2 sm:justify-end">

                            {{-- Previous --}}
                            <button
                                type="button"
                                wire:click="$set('carPage', {{ max(1, $currentPage - 1) }})"
                                @disabled($currentPage <= 1)
                                class="inline-flex h-9 items-center justify-center gap-1 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition
                                    hover:bg-gray-50
                                    disabled:pointer-events-none
                                    disabled:opacity-40
                                    dark:border-white/10
                                    dark:text-gray-300
                                    dark:hover:bg-white/5"
                            >
                                <x-filament::icon
                                    icon="heroicon-m-chevron-left"
                                    class="h-4 w-4"
                                />

                                <span class="sm:hidden">
                                    Prev
                                </span>
                            </button>


                            {{-- Mobile Page Counter --}}
                            <span class="text-xs text-gray-500 sm:hidden">
                                Page {{ $currentPage }} of {{ $lastPage }}
                            </span>


                            {{-- Desktop Pages --}}
                            <div class="hidden items-center gap-1 sm:flex">

                                @for($i = $startPage; $i <= $endPage; $i++)

                                    <button
                                        type="button"
                                        wire:click="$set('carPage', {{ $i }})"
                                        class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-xs font-semibold transition
                                            {{ $i === $currentPage
                                                ? 'bg-primary-600 text-white'
                                                : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5' }}"
                                    >
                                        {{ $i }}
                                    </button>

                                @endfor

                            </div>


                            {{-- Next --}}
                            <button
                                type="button"
                                wire:click="$set('carPage', {{ min($lastPage, $currentPage + 1) }})"
                                @disabled($currentPage >= $lastPage)
                                class="inline-flex h-9 items-center justify-center gap-1 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition
                                    hover:bg-gray-50
                                    disabled:pointer-events-none
                                    disabled:opacity-40
                                    dark:border-white/10
                                    dark:text-gray-300
                                    dark:hover:bg-white/5"
                            >
                                <span class="sm:hidden">
                                    Next
                                </span>

                                <x-filament::icon
                                    icon="heroicon-m-chevron-right"
                                    class="h-4 w-4"
                                />
                            </button>

                        </div>

                    @endif

                </div>

            @else

                {{-- EMPTY STATE --}}
                <div class="flex min-h-[260px] flex-col items-center justify-center p-8 text-center">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-white/5">

                        <x-filament::icon
                            icon="heroicon-o-truck"
                            class="h-6 w-6"
                        />

                    </div>


                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        No vehicle commission data
                    </p>

                    <p class="mt-1 max-w-sm text-sm text-gray-400">
                        No fully paid approved partner bookings match the selected filters.
                    </p>

                </div>

            @endif

        </section>

    </div>

</x-filament::page>