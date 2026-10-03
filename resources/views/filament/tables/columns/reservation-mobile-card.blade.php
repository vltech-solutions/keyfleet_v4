@php
    $record = $getRecord();

    $start = $record->start_date
        ? \Carbon\Carbon::parse($record->start_date)
        : null;

    $end = $record->end_date
        ? \Carbon\Carbon::parse($record->end_date)
        : null;

    $image = $record->car?->image
        ? \Illuminate\Support\Facades\Storage::url($record->car->image)
        : \Illuminate\Support\Facades\Storage::url('images/default-car.png');

    $hours = $start && $end
        ? (int) floor($start->diffInMinutes($end) / 60)
        : null;

    if ($hours === null) {
        $duration = '—';
    } elseif ($hours < 24) {
        $duration = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
    } else {
        $days = intdiv($hours, 24);
        $remaining = $hours % 24;

        $duration = $days . ' ' . ($days === 1 ? 'day' : 'days');

        if ($remaining) {
            $duration .= ' · ' . $remaining . ' hr';
        }
    }

    $status = match ($record->status) {
        'approved' => [
            'label' => 'Approved',
            'class' => 'text-emerald-600 dark:text-emerald-400',
            'dot' => 'bg-emerald-500',
        ],

        'declined' => [
            'label' => 'Declined',
            'class' => 'text-rose-600 dark:text-rose-400',
            'dot' => 'bg-rose-500',
        ],

        'cancelled' => [
            'label' => 'Cancelled',
            'class' => 'text-gray-500 dark:text-gray-400',
            'dot' => 'bg-gray-400',
        ],

        default => [
            'label' => 'Pending',
            'class' => 'text-amber-600 dark:text-amber-400',
            'dot' => 'bg-amber-500',
        ],
    };
@endphp

<div class="w-full min-w-0 px-3 py-4">

    {{-- Main --}}
    <div class="flex items-start gap-3">

        <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/[0.025]">

            <img
                src="{{ $image }}"
                alt="{{ $record->car?->name ?? 'Vehicle' }}"
                class="h-full w-full object-contain p-1"
                loading="lazy"
            />

        </div>


        <div class="min-w-0 flex-1">

            <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">

                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $record->car?->name ?? 'Vehicle unavailable' }}
                    </p>

                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $record->customer?->customer_name ?? 'Unknown customer' }}
                    </p>

                </div>


                <div class="flex shrink-0 items-center gap-1.5 text-[10px] font-semibold {{ $status['class'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                    {{ $status['label'] }}
                </div>

            </div>


            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-gray-400">

                <span>
                    {{ $record->reservation_number }}
                </span>

                @if($record->car?->plate_number)
                    <span>•</span>

                    <span>
                        {{ $record->car->plate_number }}
                    </span>
                @endif

                <span>•</span>

                <span>
                    {{ $duration }}
                </span>

            </div>

        </div>

    </div>


    {{-- Schedule --}}
    <div class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-3 dark:border-white/5">

        <div>

            <p class="text-[10px] uppercase tracking-wider text-gray-400">
                Pickup
            </p>

            <p class="mt-1 text-xs font-medium text-gray-800 dark:text-gray-200">
                {{ $start?->format('M d, Y') ?? '—' }}
            </p>

            <p class="mt-0.5 text-xs text-gray-400">
                {{ $start?->format('h:i A') ?? '—' }}
            </p>

        </div>


        <div>

            <p class="text-[10px] uppercase tracking-wider text-gray-400">
                Return
            </p>

            <p class="mt-1 text-xs font-medium text-gray-800 dark:text-gray-200">
                {{ $end?->format('M d, Y') ?? '—' }}
            </p>

            <p class="mt-0.5 text-xs text-gray-400">
                {{ $end?->format('h:i A') ?? '—' }}
            </p>

        </div>

    </div>


    @if(($record->reservation_fee ?? 0) > 0)

        <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-white/5">

            <span class="text-xs text-gray-400">
                Reservation Fee
            </span>

            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                ₱{{ number_format($record->reservation_fee, 2) }}
            </span>

        </div>

    @endif


    @if($record->datetime_cancelled || $record->datetime_declined)

        <div class="mt-3 border-t border-gray-100 pt-3 dark:border-white/5">

            @if($record->datetime_cancelled)

                <p class="text-xs leading-5 text-rose-600 dark:text-rose-400">
                    Cancelled:
                    {{ $record->cancellation_reason ?: 'No reason provided' }}
                </p>

            @elseif($record->datetime_declined)

                <p class="text-xs leading-5 text-rose-600 dark:text-rose-400">
                    Declined:
                    {{ $record->decline_reason ?: 'No reason provided' }}
                </p>

            @endif

        </div>

    @endif

</div>