<x-filament-panels::page>

    @php
        $totalRevenue = $this->getTotalRevenue();
        $totalBookings = $this->getTotalBookings();
        $averageRevenue = $this->getAverageRevenuePerBooking();
        $topCar = $this->getTopCar();

        $selectedCar = $carId !== 'all'
            ? \App\Models\Car::find($carId)
            : null;

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

        $vehicles = $this->vehicleRows;
    @endphp


    <div class="space-y-6">

        {{-- NOTE --}}
        <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.025]">
            <x-filament::icon
                icon="heroicon-o-information-circle"
                class="mt-0.5 h-5 w-5 shrink-0 text-primary-500"
            />

            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Vehicle Revenue Report
                </p>

                <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Revenue is based on approved booking totals. Outstanding balances are included in the reported revenue.
                </p>
            </div>
        </div>


        {{-- REPORT FILTERS --}}
        <section class="relative z-20 overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

            <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-4 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
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
                            Select a vehicle and reporting period.
                        </p>
                    </div>
                </div>

                <span class="text-xs text-gray-400">
                    Updates automatically
                </span>
            </div>


            <div class="p-5 sm:p-6">

                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

                    @php
                        $vehicleOptions = \App\Models\Car::query()
                            ->orderBy('brand')
                            ->orderBy('name')
                            ->get()
                            ->map(function ($car) {
                                return [
                                    'value' => (string) $car->id,

                                    'label' => $car->name,

                                    'description' => collect([
                                        trim(($car->brand ?? '') . ' ' . ($car->model ?? '')),
                                        $car->plate_number,
                                    ])
                                        ->filter()
                                        ->implode(' · '),
                                ];
                            })
                            ->prepend([
                                'value' => 'all',
                                'label' => 'All Vehicles',
                                'description' => 'Show revenue for the entire fleet',
                            ])
                            ->values()
                            ->all();
                    @endphp

                    <x-searchable-select
                        model="carId"
                        label="Vehicle"
                        placeholder="All Vehicles"
                        search-placeholder="Search vehicle, brand, model, plate..."
                        :options="$vehicleOptions"
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


                {{-- Custom Period --}}
                @if($period === 'custom')
                    <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/5 dark:bg-white/[0.025]">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Date From
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="startDate"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
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
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                />
                            </div>

                        </div>

                    </div>
                @endif


                {{-- Filter Context --}}
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-gray-100 pt-4 text-xs dark:border-white/5">

                    <div class="flex items-center gap-2">
                        <span class="text-gray-400">
                            Vehicle
                        </span>

                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            {{ $selectedCar?->name ?? 'All Vehicles' }}
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

                </div>

            </div>
        </section>


        {{-- REPORT SUMMARY --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Revenue --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Revenue
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            ₱{{ number_format($totalRevenue, 2) }}
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-banknotes"
                        class="h-6 w-6 text-primary-500"
                    />

                </div>

                <p class="mt-5 text-xs text-gray-400">
                    Approved booking value in selected period
                </p>
            </div>


            {{-- Bookings --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Bookings
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($totalBookings) }}
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-calendar-days"
                        class="h-6 w-6 text-gray-400"
                    />

                </div>

                <p class="mt-5 text-xs text-gray-400">
                    Approved bookings in selected period
                </p>
            </div>


            {{-- Average Booking Revenue --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Average Revenue / Booking
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            ₱{{ number_format($averageRevenue, 2) }}
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-calculator"
                        class="h-6 w-6 text-gray-400"
                    />

                </div>

                <p class="mt-5 text-xs text-gray-400">
                    Average approved booking value
                </p>
            </div>

        </section>


        {{-- TOP VEHICLE --}}
        @if($carId === 'all')
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="h-14 w-20 shrink-0 overflow-hidden rounded-xl bg-gray-100 dark:bg-white/5">
                            @if($topCar?->image)
                                <img
                                    src="{{ Storage::url($topCar->image) }}"
                                    alt="{{ $topCar->name }}"
                                    class="h-full w-full object-cover"
                                />
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-filament::icon
                                        icon="heroicon-o-truck"
                                        class="h-6 w-6 text-gray-400"
                                    />
                                </div>
                            @endif
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                                Top Revenue Vehicle
                            </p>

                            <h2 class="mt-1 font-semibold text-gray-950 dark:text-white">
                                {{ $topCar?->name ?? 'No booking data yet' }}
                            </h2>

                            @if($topCar)
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $topCar->brand }}
                                    {{ $topCar->model }}

                                    @if($topCar->plate_number)
                                        · {{ $topCar->plate_number }}
                                    @endif
                                </p>
                            @endif
                        </div>

                    </div>


                    <div class="text-left sm:text-right">
                        <p class="text-xs text-gray-400">
                            Based on total booking revenue
                        </p>
                    </div>

                </div>

            </section>
        @endif


        {{-- VEHICLE REVENUE DETAILS --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

            {{-- Header --}}
            <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Vehicle Revenue Details
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Revenue and approved booking performance by vehicle.
                    </p>
                </div>

                <span class="text-xs text-gray-400">
                    {{ number_format($vehicles->total()) }}
                    {{ $vehicles->total() === 1 ? 'vehicle' : 'vehicles' }}
                </span>

            </div>


            @if($vehicles->count())

                {{-- DESKTOP TABLE --}}
                <div class="hidden overflow-x-auto md:block">

                    <table class="w-full">

                        <thead class="border-b border-gray-100 bg-gray-50/70 dark:border-white/5 dark:bg-white/[0.02]">

                            <tr>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Vehicle
                                </th>

                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Plate
                                </th>

                                <th class="px-6 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Bookings
                                </th>

                                <th class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    Revenue
                                </th>
                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">

                            @foreach($vehicles as $vehicle)

                                @php
                                    $vehicleRevenue =
                                        (float) ($vehicle->filtered_bookings_total_due ?? 0);

                                    $bookingCount =
                                        (int) ($vehicle->filtered_bookings_count ?? 0);
                                @endphp

                                <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.025]">

                                    {{-- Vehicle --}}
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-4">

                                            <div class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden">

                                                <img
                                                    src="{{ $this->getCarImageUrl($vehicle) }}"
                                                    alt="{{ $vehicle->name }}"
                                                    class="h-full w-full object-contain p-1"
                                                    loading="lazy"
                                                />

                                            </div>


                                            <div class="min-w-0">

                                                <p class="max-w-[260px] truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $vehicle->name }}
                                                </p>

                                                <p class="mt-1 max-w-[300px] truncate text-xs text-gray-500 dark:text-gray-400">
                                                    {{ trim(
                                                        ($vehicle->brand ?? '')
                                                        . ' '
                                                        . ($vehicle->model ?? '')
                                                    ) }}

                                                    @if($vehicle->year)
                                                        · {{ $vehicle->year }}
                                                    @endif
                                                </p>

                                                <div class="mt-1.5 flex flex-wrap items-center gap-2 text-[11px] text-gray-400">

                                                    @if($vehicle->color)
                                                        <span>
                                                            {{ $vehicle->color }}
                                                        </span>
                                                    @endif

                                                    @if($vehicle->seat_count)
                                                        <span>
                                                            •
                                                        </span>

                                                        <span>
                                                            {{ $vehicle->seat_count }} seats
                                                        </span>
                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Plate --}}
                                    <td class="px-6 py-4">

                                        @if($vehicle->plate_number)
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                                {{ $vehicle->plate_number }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-300 dark:text-gray-600">
                                                —
                                            </span>
                                        @endif

                                    </td>


                                    {{-- Bookings --}}
                                    <td class="px-6 py-4 text-center">

                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($bookingCount) }}
                                        </p>

                                        <p class="mt-0.5 text-[11px] text-gray-400">
                                            {{ $bookingCount === 1 ? 'booking' : 'bookings' }}
                                        </p>

                                    </td>


                                    {{-- Revenue --}}
                                    <td class="px-6 py-4 text-right">

                                        <p class="whitespace-nowrap text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                            ₱{{ number_format($vehicleRevenue, 2) }}
                                        </p>

                                        @if($bookingCount > 0)
                                            <p class="mt-1 whitespace-nowrap text-[11px] text-gray-400">
                                                ₱{{ number_format($vehicleRevenue / $bookingCount, 2) }}
                                                avg.
                                            </p>
                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- MOBILE CARDS --}}
                <div class="divide-y divide-gray-100 dark:divide-white/5 md:hidden">

                    @foreach($vehicles as $vehicle)

                        @php
                            $vehicleRevenue =
                                (float) ($vehicle->filtered_bookings_total_due ?? 0);

                            $bookingCount =
                                (int) ($vehicle->filtered_bookings_count ?? 0);
                        @endphp

                        <article class="p-4">

                            {{-- Vehicle --}}
                            <div class="flex items-start gap-3">

                                <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-white/[0.025]">

                                    <img
                                        src="{{ $this->getCarImageUrl($vehicle) }}"
                                        alt="{{ $vehicle->name }}"
                                        class="h-full w-full object-contain p-1"
                                        loading="lazy"
                                    />

                                </div>


                                <div class="min-w-0 flex-1">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $vehicle->name }}
                                            </p>

                                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                                {{ trim(
                                                    ($vehicle->brand ?? '')
                                                    . ' '
                                                    . ($vehicle->model ?? '')
                                                ) }}

                                                @if($vehicle->year)
                                                    · {{ $vehicle->year }}
                                                @endif
                                            </p>

                                        </div>


                                        @if($vehicle->plate_number)
                                            <span class="shrink-0 rounded-full bg-gray-100 px-2 py-1 text-[10px] font-medium text-gray-500 dark:bg-white/5 dark:text-gray-300">
                                                {{ $vehicle->plate_number }}
                                            </span>
                                        @endif

                                    </div>


                                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-400">

                                        @if($vehicle->color)
                                            <span>
                                                {{ $vehicle->color }}
                                            </span>
                                        @endif

                                        @if($vehicle->seat_count)
                                            <span>
                                                {{ $vehicle->seat_count }} seats
                                            </span>
                                        @endif

                                    </div>

                                </div>

                            </div>


                            {{-- Stats --}}
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

                                    <p class="mt-1 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                        ₱{{ number_format($vehicleRevenue, 2) }}
                                    </p>

                                </div>

                            </div>


                            @if($bookingCount > 0)

                                <div class="mt-3 flex items-center justify-between text-xs">

                                    <span class="text-gray-400">
                                        Average / booking
                                    </span>

                                    <span class="font-medium text-gray-700 dark:text-gray-200">
                                        ₱{{ number_format(
                                            $vehicleRevenue / $bookingCount,
                                            2
                                        ) }}
                                    </span>

                                </div>

                            @endif

                        </article>

                    @endforeach

                </div>


                {{-- PAGINATION --}}
                @if($vehicles->hasPages())

                    <div class="border-t border-gray-100 px-4 py-4 dark:border-white/5 sm:px-6">

                        {{ $vehicles->links() }}

                    </div>

                @endif

            @else

                {{-- EMPTY STATE --}}
                <div class="flex min-h-[240px] flex-col items-center justify-center p-8 text-center">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-white/5">

                        <x-filament::icon
                            icon="heroicon-o-truck"
                            class="h-6 w-6"
                        />

                    </div>

                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        No vehicle revenue found
                    </p>

                    <p class="mt-1 max-w-sm text-sm text-gray-400">
                        No approved bookings match the selected vehicle and period.
                    </p>

                </div>

            @endif

        </section>

    </div>

</x-filament-panels::page>