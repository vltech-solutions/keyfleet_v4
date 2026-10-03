@php
    $record = $getRecord();

    $start = $record->start_datetime
        ? \Carbon\Carbon::parse($record->start_datetime)
        : null;

    $end = $record->end_datetime
        ? \Carbon\Carbon::parse($record->end_datetime)
        : null;

    $minutes = $start && $end ? $start->diffInMinutes($end) : null;
    $hours = $minutes !== null ? (int) floor($minutes / 60) : null;

    if ($hours === null) {
        $duration = '—';
    } elseif ($hours < 24) {
        $duration = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
    } else {
        $days = intdiv($hours, 24);
        $remaining = $hours % 24;

        $duration = $days . ' ' . ($days === 1 ? 'day' : 'days');

        if ($remaining) {
            $duration .= ' ' . $remaining . ' hr';
        }
    }

    $image = $record->car?->image
        ? \Illuminate\Support\Facades\Storage::url($record->car->image)
        : \Illuminate\Support\Facades\Storage::url('images/default-car.png');

    $status = match (true) {
        $record->status === 'cancelled' => [
            'label' => 'Cancelled',
            'class' => 'text-rose-600 dark:text-rose-400',
            'dot' => 'bg-rose-500',
        ],

        $start?->isFuture() => [
            'label' => 'Upcoming',
            'class' => 'text-primary-600 dark:text-primary-400',
            'dot' => 'bg-primary-500',
        ],

        $start && $end && $start->lte(now()) && $end->gte(now()) => [
            'label' => 'On Trip',
            'class' => 'text-amber-600 dark:text-amber-400',
            'dot' => 'bg-amber-500',
        ],

        $end?->isPast() => [
            'label' => 'Completed',
            'class' => 'text-emerald-600 dark:text-emerald-400',
            'dot' => 'bg-emerald-500',
        ],

        default => [
            'label' => ucfirst($record->status ?? 'Booking'),
            'class' => 'text-gray-600 dark:text-gray-300',
            'dot' => 'bg-gray-400',
        ],
    };

    $charges = [
        'Extension' => $record->extend_due ?? 0,
        'Delivery' => $record->delivery_fee ?? 0,
        'Driver' => $record->driver_fee ?? 0,
        'Fuel' => $record->fuel_charge ?? 0,
        'Out of Bounds' => $record->out_of_bounds ?? 0,
        'RFID' => $record->rfid ?? 0,
        'Damage' => $record->damages ?? 0,
        'Car Wash' => $record->carwash_fee ?? 0,
        'Insurance' => $record->insurance ?? 0,
    ];

    $visibleCharges = collect($charges)
        ->filter(fn ($value) => (float) $value > 0);
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

    {{-- Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-start sm:justify-between">

        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                Booking No.
            </p>

            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                    {{ $record->booking_id ?: '#' . $record->id }}
                </h2>

                <span class="text-sm text-gray-400">
                    {{ $record->renter_name }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-semibold {{ $status['class'] }}">
            <span class="h-2 w-2 rounded-full {{ $status['dot'] }}"></span>
            {{ $status['label'] }}
        </div>

    </div>


    {{-- Main Information --}}
    <div class="grid grid-cols-1 lg:grid-cols-12">

        {{-- Vehicle --}}
        <div class="border-b border-gray-200 p-5 dark:border-white/10 lg:col-span-4 lg:border-b-0 lg:border-r">

            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                Vehicle
            </p>

            <div class="mt-4 flex items-center gap-4 lg:block">

                <div class="flex h-28 shrink-0 items-center justify-center rounded-lg bg-gray-50 dark:bg-white/[0.025] lg:h-40">
                    <img
                        src="{{ $image }}"
                        alt="{{ $record->car?->name ?? 'Vehicle' }}"
                        class="max-h-full object-contain p-3"
                    />
                </div>

                <div class="min-w-0 lg:mt-4">

                    <p class="truncate text-base font-semibold text-gray-900 dark:text-white">
                        {{ $record->car?->name ?? 'Vehicle unavailable' }}
                    </p>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ trim(($record->car?->brand ?? '') . ' ' . ($record->car?->model ?? '')) }}
                    </p>

                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">

                        @if($record->car?->plate_number)
                            <span>{{ $record->car->plate_number }}</span>
                        @endif

                        @if($record->car?->year)
                            <span>{{ $record->car->year }}</span>
                        @endif

                        @if($record->car?->color)
                            <span>{{ $record->car->color }}</span>
                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- Rental Details --}}
        <div class="lg:col-span-8">

            {{-- Schedule --}}
            <div class="border-b border-gray-200 p-5 dark:border-white/10">

                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                        Rental Schedule
                    </p>

                    <span class="text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ $duration }}
                    </span>
                </div>


                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <p class="text-xs text-gray-400">
                            Pickup
                        </p>

                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $start?->format('M d, Y · h:i A') ?? '—' }}
                        </p>

                        <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            {{ $record->delivery_address ?: 'Garage Headquarters' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs text-gray-400">
                            Return
                        </p>

                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $end?->format('M d, Y · h:i A') ?? '—' }}
                        </p>

                        <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            {{ $record->return_address ?: 'Garage Headquarters' }}
                        </p>
                    </div>

                </div>


                @if($record->destination)
                    <div class="mt-5 border-t border-gray-100 pt-4 dark:border-white/5">
                        <span class="text-xs text-gray-400">
                            Destination
                        </span>

                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $record->destination }}
                        </span>
                    </div>
                @endif

            </div>


            {{-- Customer --}}
            <div class="p-5">

                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                    Customer
                </p>


                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">

                    <div>
                        <p class="text-xs text-gray-400">
                            Name
                        </p>

                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $record->renter_name ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">
                            Contact
                        </p>

                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $record->contact_number ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">
                            Address
                        </p>

                        <p class="mt-1 text-sm leading-5 text-gray-600 dark:text-gray-300">
                            {{ $record->renter_address ?: '—' }}
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Pricing --}}
    <div class="border-t border-gray-200 dark:border-white/10">

        <div class="px-5 py-4">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                Pricing
            </p>
        </div>


        <div class="grid grid-cols-2 border-t border-gray-100 dark:border-white/5 sm:grid-cols-4">

            <div class="border-r border-gray-100 p-4 dark:border-white/5">
                <p class="text-xs text-gray-400">
                    Daily Rate
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    ₱{{ number_format($record->daily_rate ?? 0, 2) }}
                </p>
            </div>


            <div class="border-r border-gray-100 p-4 dark:border-white/5">
                <p class="text-xs text-gray-400">
                    Rental Days
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ number_format($record->days_rented ?? 0) }}
                </p>
            </div>


            <div class="border-r border-gray-100 p-4 dark:border-white/5">
                <p class="text-xs text-gray-400">
                    Rent Amount
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    ₱{{ number_format($record->total_rent_due ?? 0, 2) }}
                </p>
            </div>


            <div class="p-4">
                <p class="text-xs text-gray-400">
                    Discount
                </p>

                <p class="mt-1 text-sm font-semibold
                    {{ ($record->discount ?? 0) > 0
                        ? 'text-rose-600 dark:text-rose-400'
                        : 'text-gray-900 dark:text-white' }}"
                >
                    -₱{{ number_format($record->discount ?? 0, 2) }}
                </p>
            </div>

        </div>


        {{-- Additional Charges --}}
        @if($visibleCharges->isNotEmpty())

            <div class="border-t border-gray-100 px-5 py-4 dark:border-white/5">

                <p class="text-xs text-gray-400">
                    Additional Charges
                </p>

                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-3">

                    @foreach($visibleCharges as $label => $value)

                        <div>
                            <span class="text-xs text-gray-400">
                                {{ $label }}
                            </span>

                            <span class="ml-1 text-xs font-semibold text-gray-700 dark:text-gray-200">
                                ₱{{ number_format($value, 2) }}
                            </span>
                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        {{-- Final --}}
        <div class="flex flex-col gap-4 border-t border-gray-200 bg-gray-50/60 px-5 py-4 dark:border-white/10 dark:bg-white/[0.02] sm:flex-row sm:items-center sm:justify-between">

            <div class="flex flex-wrap gap-x-8 gap-y-3">

                @if(($record->security_deposit ?? 0) > 0)
                    <div>
                        <p class="text-xs text-gray-400">
                            Security Deposit
                        </p>

                        <p class="mt-1 text-sm font-semibold text-amber-600 dark:text-amber-400">
                            ₱{{ number_format($record->security_deposit, 2) }}
                        </p>
                    </div>
                @endif


                @if($record->with_driver)
                    <div>
                        <p class="text-xs text-gray-400">
                            Driver
                        </p>

                        <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                            Included
                        </p>
                    </div>
                @endif

            </div>


            <div class="sm:text-right">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                    Grand Total
                </p>

                <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    ₱{{ number_format($record->total_due ?? 0, 2) }}
                </p>
            </div>

        </div>

    </div>

</div>