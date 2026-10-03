<x-filament-panels::page>

    @php
        $report = $this->report_data;
        $trend = $this->chart_data;
        $mix = $this->performance_mix;
        $topVehicles = $this->top_vehicles;

        $vehicleOptions = \App\Models\Car::query()
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
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Utilization Trend
        |--------------------------------------------------------------------------
        */
        $chartWidth = 700;
        $chartTop = 20;
        $chartBottom = 180;
        $chartLeft = 25;
        $chartRight = 675;
        $chartSteps = max(1, count($trend) - 1);

        $trendPoints = collect($trend)->map(function ($item, $index) use (
            $chartLeft,
            $chartRight,
            $chartTop,
            $chartBottom,
            $chartSteps
        ) {
            $x = $chartLeft + (($chartRight - $chartLeft) * ($index / $chartSteps));
            $y = $chartBottom - (($item['rate'] / 100) * ($chartBottom - $chartTop));

            return "{$x},{$y}";
        })->implode(' ');

        $trendAreaPoints = count($trend)
            ? "{$chartLeft},{$chartBottom} {$trendPoints} {$chartRight},{$chartBottom}"
            : '';

        /*
        |--------------------------------------------------------------------------
        | Performance Mix
        |--------------------------------------------------------------------------
        */
        $mixColors = [
            'Excellent' => '#10b981',
            'Good' => '#3b82f6',
            'Moderate' => '#f59e0b',
            'Low' => '#f43f5e',
        ];

        $mixStops = [];
        $mixCursor = 0;

        foreach ($mix['items'] as $label => $count) {
            $percentage = $mix['total'] > 0
                ? ($count / $mix['total']) * 100
                : 0;

            $end = $mixCursor + $percentage;

            $mixStops[] = "{$mixColors[$label]} {$mixCursor}% {$end}%";
            $mixCursor = $end;
        }

        $mixGradient = $mix['total'] > 0
            ? 'conic-gradient(' . implode(', ', $mixStops) . ')'
            : 'conic-gradient(#e5e7eb 0% 100%)';
    @endphp


    <div class="space-y-6">

        {{-- REPORT FILTERS --}}
        <section class="relative z-20 overflow-visible rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            {{-- Header --}}
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
                            Choose the fleet scope and utilization period.
                        </p>
                    </div>
                </div>

                <span class="text-xs text-gray-400">
                    Updates automatically
                </span>
            </div>


            <div class="p-5 sm:p-6">

                {{-- Primary Controls --}}
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                    {{-- Scope --}}
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Report Scope
                        </label>

                        <div class="inline-flex w-full rounded-xl bg-gray-100 p-1 dark:bg-white/5">

                            <button
                                type="button"
                                wire:click="$set('data.view_type', 'whole')"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ ($data['view_type'] ?? 'whole') === 'whole'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                <x-filament::icon
                                    icon="heroicon-o-truck"
                                    class="h-4 w-4"
                                />

                                Whole Fleet
                            </button>


                            <button
                                type="button"
                                wire:click="$set('data.view_type', 'per_car')"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ ($data['view_type'] ?? 'whole') === 'per_car'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                <x-filament::icon
                                    icon="heroicon-o-key"
                                    class="h-4 w-4"
                                />

                                Per Vehicle
                            </button>

                        </div>
                    </div>


                    {{-- Period --}}
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Reporting Period
                        </label>

                        <div class="inline-flex w-full rounded-xl bg-gray-100 p-1 dark:bg-white/5">

                            <button
                                type="button"
                                wire:click="$set('data.period', 'monthly')"
                                class="flex-1 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ ($data['period'] ?? 'monthly') === 'monthly'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                Monthly
                            </button>


                            <button
                                type="button"
                                wire:click="$set('data.period', 'semi_annual')"
                                class="flex-1 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ ($data['period'] ?? 'monthly') === 'semi_annual'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                6 Months
                            </button>


                            <button
                                type="button"
                                wire:click="$set('data.period', 'annual')"
                                class="flex-1 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                    {{ ($data['period'] ?? 'monthly') === 'annual'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                12 Months
                            </button>

                        </div>
                    </div>

                </div>


                {{-- Contextual Controls --}}
                <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/5 dark:bg-white/[0.025]">

                    <div
                        class="grid grid-cols-1 gap-4
                            {{ ($data['view_type'] ?? 'whole') === 'per_car'
                                ? 'md:grid-cols-2'
                                : '' }}"
                    >

                        {{-- Month --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ in_array(($data['period'] ?? 'monthly'), ['semi_annual', 'annual'])
                                    ? 'Starting Month'
                                    : 'Report Month' }}
                            </label>

                            <input
                                type="month"
                                wire:model.live="data.date"
                                class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm
                                    focus:border-primary-500 focus:ring-primary-500
                                    dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </div>


                        {{-- Vehicle --}}
                        @if(($data['view_type'] ?? 'whole') === 'per_car')
                            <x-searchable-select
                                model="data.car_id"
                                label="Vehicle"
                                placeholder="Select vehicle"
                                search-placeholder="Search vehicle, brand, model, plate..."
                                empty-text="No vehicle found"
                                :options="$vehicleOptions"
                            />
                        @endif

                    </div>


                    {{-- Current Filter Context --}}
                    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-gray-200/70 pt-4 text-xs dark:border-white/5">

                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">
                                Scope
                            </span>

                            <span class="font-medium text-gray-700 dark:text-gray-200">
                                {{ ($data['view_type'] ?? 'whole') === 'per_car'
                                    ? 'Per Vehicle'
                                    : 'Whole Fleet' }}
                            </span>
                        </div>


                        <span class="hidden h-3 w-px bg-gray-200 dark:bg-white/10 sm:block"></span>


                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">
                                Period
                            </span>

                            <span class="font-medium text-gray-700 dark:text-gray-200">
                                {{ match($data['period'] ?? 'monthly') {
                                    'semi_annual' => '6 Months',
                                    'annual' => '12 Months',
                                    default => 'Monthly',
                                } }}
                            </span>
                        </div>


                        @if(filled($data['date'] ?? null))
                            <span class="hidden h-3 w-px bg-gray-200 dark:bg-white/10 sm:block"></span>

                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">
                                    Starting
                                </span>

                                <span class="font-medium text-gray-700 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($data['date'] . '-01')->format('F Y') }}
                                </span>
                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </section>


        {{-- SUMMARY --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Fleet Utilization --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Fleet Utilization Rate
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($report['fleet_rate'], 1) }}%
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-chart-pie"
                        class="h-6 w-6 text-primary-500"
                    />
                </div>

                <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
                    <div
                        class="h-full rounded-full bg-primary-500"
                        style="width: {{ min(100, $report['fleet_rate']) }}%"
                    ></div>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    {{ $report['remark'] }}
                </p>
            </div>


            {{-- Total Vehicles --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Vehicles
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($report['total_cars']) }}
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-truck"
                        class="h-6 w-6 text-gray-400"
                    />
                </div>

                <p class="mt-8 text-xs text-gray-400">
                    Analyzed for selected period
                </p>
            </div>


            {{-- Status --}}
            @php
                $isHealthy = $report['fleet_rate'] >= 70;
            @endphp

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Status
                        </p>

                        <p class="mt-2 text-xl font-semibold
                            {{ $isHealthy
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $isHealthy ? 'Healthy Operations' : 'Attention Needed' }}
                        </p>
                    </div>

                    <x-filament::icon
                        :icon="$isHealthy ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'"
                        class="h-6 w-6 {{ $isHealthy
                            ? 'text-emerald-500'
                            : 'text-amber-500' }}"
                    />
                </div>

                <div class="mt-7 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full {{ $isHealthy ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>

                    <p class="text-xs text-gray-400">
                        {{ $isHealthy
                            ? 'Fleet utilization is within a healthy range.'
                            : 'Fleet utilization is below the target range.' }}
                    </p>
                </div>
            </div>

        </section>


        {{-- TREND + PERFORMANCE MIX --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- Utilization Trend --}}
            <div class="relative overflow-visible rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-8">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">
                            Utilization Trend
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Fleet utilization across the selected period.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span class="h-2 w-2 rounded-full bg-primary-500"></span>
                        Utilization
                    </div>
                </div>


                @if(count($trend))
                    <div class="mt-7" x-data="{ hovered: null }">

                        <div class="relative">
                            <svg viewBox="0 0 700 210" class="h-64 w-full overflow-visible" preserveAspectRatio="none">

                                @foreach([0, 25, 50, 75, 100] as $tick)
                                    @php $gridY = $chartBottom - (($tick / 100) * ($chartBottom - $chartTop)); @endphp

                                    <line
                                        x1="{{ $chartLeft }}" y1="{{ $gridY }}"
                                        x2="{{ $chartRight }}" y2="{{ $gridY }}"
                                        stroke="currentColor" stroke-width="1"
                                        stroke-dasharray="4 5"
                                        vector-effect="non-scaling-stroke"
                                        class="text-gray-100 dark:text-white/5"
                                    />
                                @endforeach

                                <polygon
                                    points="{{ $trendAreaPoints }}"
                                    fill="currentColor"
                                    class="text-primary-500 opacity-[0.06]"
                                />

                                <polyline
                                    points="{{ $trendPoints }}"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    vector-effect="non-scaling-stroke"
                                    class="text-primary-500"
                                />

                                @foreach($trend as $item)
                                    @php
                                        $x = $chartLeft + (($chartRight - $chartLeft) * ($loop->index / $chartSteps));
                                        $y = $chartBottom - (($item['rate'] / 100) * ($chartBottom - $chartTop));
                                    @endphp

                                    <g
                                        class="cursor-pointer"
                                        @mouseenter="hovered = {{ $loop->index }}"
                                        @mouseleave="hovered = null"
                                    >
                                        {{-- Hover target --}}
                                        <circle cx="{{ $x }}" cy="{{ $y }}" r="14" fill="transparent" />

                                        {{-- Point --}}
                                        <circle
                                            cx="{{ $x }}"
                                            cy="{{ $y }}"
                                            r="3"
                                            fill="currentColor"
                                            class="text-primary-500"
                                            vector-effect="non-scaling-stroke"
                                        />
                                    </g>
                                @endforeach
                            </svg>

                            {{-- HTML Tooltips --}}
                            @foreach($trend as $item)
                                @php
                                    $x = $chartLeft + (($chartRight - $chartLeft) * ($loop->index / $chartSteps));
                                    $y = $chartBottom - (($item['rate'] / 100) * ($chartBottom - $chartTop));
                                    $xPercent = ($x / 700) * 100;
                                    $yPercent = ($y / 210) * 100;

                                    $tooltipPosition = match (true) {
                                        $loop->first => 'translate-x-0',
                                        $loop->last => '-translate-x-full',
                                        default => '-translate-x-1/2',
                                    };
                                @endphp

                                <div
                                    x-cloak
                                    x-show="hovered === {{ $loop->index }}"
                                    x-transition.opacity
                                    class="pointer-events-none absolute z-20 -translate-y-full pb-3 {{ $tooltipPosition }}"
                                    style="left: {{ $xPercent }}%; top: {{ $yPercent }}%;"
                                >
                                    <div class="w-36 rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-lg dark:border-white/10 dark:bg-gray-800">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                            {{ $item['label'] }}
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                            {{ number_format($item['rate'], 1) }}%
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div
                            class="grid px-1"
                            style="grid-template-columns: repeat({{ max(1, count($trend)) }}, minmax(0, 1fr));"
                        >
                            @foreach($trend as $item)
                                <span class="text-center text-[10px] font-medium text-gray-400 sm:text-[11px]">
                                    {{ $item['short'] }}
                                </span>
                            @endforeach
                        </div>

                    </div>
                @else
                    <div class="flex h-64 items-center justify-center">
                        <p class="text-sm text-gray-400">No utilization data available.</p>
                    </div>
                @endif

            </div>


            {{-- Performance Mix --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-4">

                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Fleet Performance
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Utilization distribution by vehicle.
                    </p>
                </div>


                <div class="mt-7 flex justify-center">
                    <div
                        class="relative h-40 w-40 rounded-full"
                        style="background: {{ $mixGradient }};"
                    >
                        <div class="absolute inset-[20px] flex flex-col items-center justify-center rounded-full bg-white dark:bg-gray-900">
                            <strong class="text-3xl font-semibold text-gray-950 dark:text-white">
                                {{ number_format($mix['total']) }}
                            </strong>

                            <span class="text-xs text-gray-400">
                                vehicles
                            </span>
                        </div>
                    </div>
                </div>


                <div class="mt-7 space-y-3">
                    @foreach($mix['items'] as $label => $count)
                        <div class="flex items-center justify-between gap-4">

                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2.5 w-2.5 rounded-sm"
                                    style="background-color: {{ $mixColors[$label] }};"
                                ></span>

                                <span class="text-sm text-gray-500 dark:text-gray-300">
                                    {{ $label }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-400">
                                    {{ $mix['total'] > 0
                                        ? number_format(($count / $mix['total']) * 100, 1)
                                        : 0 }}%
                                </span>

                                <strong class="min-w-5 text-right text-sm text-gray-900 dark:text-white">
                                    {{ $count }}
                                </strong>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>

        </section>


        {{-- TOP VEHICLES --}}
        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Top Vehicle Utilization
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Highest utilized vehicles for the selected period.
                    </p>
                </div>

                <span class="text-xs text-gray-400">
                    Top {{ count($topVehicles) }}
                </span>
            </div>


            <div class="mt-7 space-y-5">

                @forelse($topVehicles as $vehicle)

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-4">

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $vehicle['name'] }}
                                </p>

                                @if($vehicle['plate'])
                                    <p class="mt-0.5 text-xs text-gray-400">
                                        {{ $vehicle['plate'] }}
                                    </p>
                                @endif
                            </div>


                            <span class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($vehicle['rate'], 1) }}%
                            </span>

                        </div>


                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
                            <div
                                class="h-full rounded-full bg-primary-500"
                                style="width: {{ min(100, $vehicle['rate']) }}%"
                            ></div>
                        </div>
                    </div>

                @empty

                    <div class="py-10 text-center">
                        <x-filament::icon
                            icon="heroicon-o-truck"
                            class="mx-auto h-7 w-7 text-gray-300 dark:text-gray-600"
                        />

                        <p class="mt-3 text-sm text-gray-400">
                            No vehicle utilization data available.
                        </p>
                    </div>

                @endforelse

            </div>

        </section>


        {{-- VEHICLE DETAILS --}}
        <section>
            <div class="mb-4">
                <h2 class="font-semibold text-gray-950 dark:text-white">
                    Vehicle Utilization Details
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Detailed utilization results for every vehicle in the report.
                </p>
            </div>

            {{ $this->table }}
        </section>

    </div>

</x-filament-panels::page>