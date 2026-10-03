@php
    $booking = $getRecord();

    $start = $booking->start_datetime
        ? \Carbon\Carbon::parse($booking->start_datetime)
        : null;

    $end = $booking->end_datetime
        ? \Carbon\Carbon::parse($booking->end_datetime)
        : null;

    $image = $booking->car?->image
        ? \Illuminate\Support\Facades\Storage::url($booking->car->image)
        : \Illuminate\Support\Facades\Storage::url('images/default-car.png');

    $status = match (true) {
        $booking->status === 'cancelled' => [
            'label' => 'Cancelled',
            'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
        ],

        $start?->isFuture() => [
            'label' => 'Upcoming',
            'class' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300',
        ],

        $start && $end && $start->lte(now()) && $end->gte(now()) => [
            'label' => 'On Trip',
            'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        ],

        $end?->isPast() => [
            'label' => 'Completed',
            'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        ],

        default => [
            'label' => ucfirst($booking->status ?? 'Booking'),
            'class' => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300',
        ],
    };

    $hours = $start && $end
        ? $start->diffInHours($end)
        : null;

    $duration = match (true) {
        $hours === null => null,
        $hours < 24 => $hours . ' ' . ($hours === 1 ? 'hour' : 'hours'),
        default => floor($hours / 24)
            . ' '
            . (floor($hours / 24) === 1 ? 'day' : 'days')
            . ($hours % 24 ? ' · ' . ($hours % 24) . ' hr' : ''),
    };
@endphp

@once
    <style>
        @media (max-width: 767px) {
            /*
             * Let the mobile booking column consume the whole table width.
             */
            .fi-ta-table {
                width: 100% !important;
                table-layout: fixed !important;
            }

            /*
             * Filament still reserves a column for row actions.
             * Remove that column completely on mobile.
             */
            .fi-ta-actions-header-cell,
            .fi-ta-table tbody > tr > td:has(.fi-ta-actions) {
                display: none !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
                padding: 0 !important;
                border: 0 !important;
            }

            /*
             * Filament may create a colgroup width for the actions column.
             */
            .fi-ta-table colgroup col:last-child {
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
                visibility: collapse !important;
            }

            /*
             * Force the actual mobile ViewColumn cell to take the
             * remaining full width.
             */
            .fi-ta-table tbody > tr > td:has(.booking-mobile-card) {
                width: 100% !important;
                min-width: 100% !important;
                max-width: none !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .booking-mobile-card {
                display: block !important;
                width: 100% !important;
                max-width: none !important;
            }
        }
    </style>
@endonce

<div class="booking-mobile-card w-full min-w-0 py-2">

    {{-- Top --}}
    <div class="flex items-start gap-3">

        <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-white/[0.025]">
            <img
                src="{{ $image }}"
                alt="{{ $booking->car?->name ?? 'Vehicle' }}"
                class="h-full w-full object-contain p-1"
                loading="lazy"
            />
        </div>


        <div class="min-w-0 flex-1">

            <div class="flex items-start justify-between gap-2">

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $booking->car?->name ?? 'Vehicle unavailable' }}
                    </p>

                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $booking->renter_name }}
                    </p>
                </div>


                <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-semibold {{ $status['class'] }}">
                    {{ $status['label'] }}
                </span>

            </div>


            @if($booking->car?->plate_number || $duration)
                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-gray-400">

                    @if($booking->car?->plate_number)
                        <span>
                            {{ $booking->car->plate_number }}
                        </span>
                    @endif

                    @if($booking->car?->plate_number && $duration)
                        <span>•</span>
                    @endif

                    @if($duration)
                        <span>
                            {{ $duration }}
                        </span>
                    @endif

                </div>
            @endif

        </div>

    </div>


    {{-- Rental Period --}}
    <div class="mt-4 rounded-xl border border-gray-100 px-3 py-3 dark:border-white/5">

        <div class="flex items-start gap-2">

            <x-filament::icon
                icon="heroicon-o-calendar-days"
                class="mt-0.5 h-4 w-4 shrink-0 text-gray-400"
            />

            <div class="min-w-0">

                <p class="text-xs font-medium text-gray-700 dark:text-gray-200">
                    {{ $start?->format('M d, Y · h:i A') ?? 'No pickup schedule' }}
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    to {{ $end?->format('M d, Y · h:i A') ?? 'No return schedule' }}
                </p>

            </div>

        </div>

    </div>


    {{-- Financial Summary --}}
    <div class="mt-3 grid grid-cols-3 divide-x divide-gray-100 rounded-xl bg-gray-50/70 py-3 dark:divide-white/5 dark:bg-white/[0.025]">

        <div class="min-w-0 px-2 text-center">

            <p class="text-[9px] font-semibold uppercase tracking-wider text-gray-400">
                Total
            </p>

            <p class="mt-1 truncate text-xs font-semibold text-gray-900 dark:text-white">
                ₱{{ number_format($booking->total_due ?? 0, 2) }}
            </p>

        </div>


        <div class="min-w-0 px-2 text-center">

            <p class="text-[9px] font-semibold uppercase tracking-wider text-gray-400">
                Paid
            </p>

            <p class="mt-1 truncate text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format($booking->paid_amount ?? 0, 2) }}
            </p>

        </div>


        <div class="min-w-0 px-2 text-center">

            <p class="text-[9px] font-semibold uppercase tracking-wider text-gray-400">
                Balance
            </p>

            @if(($booking->balance ?? 0) > 0)

                <p class="mt-1 truncate text-xs font-semibold text-rose-600 dark:text-rose-400">
                    ₱{{ number_format($booking->balance, 2) }}
                </p>

            @else

                <p class="mt-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                    Paid
                </p>

            @endif

        </div>

    </div>

</div>