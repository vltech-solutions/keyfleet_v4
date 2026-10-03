<x-filament::page>

    @php
        $record = $this->record;

        $start = $record->start_date
            ? \Carbon\Carbon::parse($record->start_date)
            : null;

        $end = $record->end_date
            ? \Carbon\Carbon::parse($record->end_date)
            : null;

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

        $image = $record->car?->image
            ? \Illuminate\Support\Facades\Storage::url($record->car->image)
            : \Illuminate\Support\Facades\Storage::url('images/default-car.png');

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


    <div
        x-data="{
            receiptOpen: false,
            receiptUrl: '',
            documentOpen: false,
            documentUrl: '',
            documentIsImage: true,

            openReceipt(url) {
                this.receiptUrl = url;
                this.receiptOpen = true;
            },

            openDocument(url, isImage = true) {
                this.documentUrl = url;
                this.documentIsImage = isImage;
                this.documentOpen = true;
            }
        }"
        class="space-y-5"
    >

        {{-- Reservation Header --}}
        <div class="flex flex-col gap-3 border-b border-gray-200 pb-4 dark:border-white/10 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                    Reservation
                </p>

                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">

                    <h1 class="text-xl font-semibold text-gray-950 dark:text-white">
                        #{{ $record->reservation_number }}
                    </h1>

                    <span class="text-sm text-gray-400">
                        {{ $record->created_at?->format('M d, Y · h:i A') }}
                    </span>

                </div>

            </div>


            <div class="flex items-center gap-2 text-sm font-medium {{ $status['class'] }}">
                <span class="h-2 w-2 rounded-full {{ $status['dot'] }}"></span>

                {{ $status['label'] }}
            </div>

        </div>


        {{-- Main Record --}}
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

            <div class="grid grid-cols-1 lg:grid-cols-12">

                {{-- Vehicle --}}
                <div class="border-b border-gray-200 p-5 dark:border-white/10 lg:col-span-4 lg:border-b-0 lg:border-r">

                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                        Reserved Vehicle
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
                                {{ trim(
                                    ($record->car?->brand ?? '')
                                    . ' '
                                    . ($record->car?->model ?? '')
                                ) ?: '—' }}
                            </p>


                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">

                                @if($record->car?->plate_number)
                                    <span>
                                        {{ $record->car->plate_number }}
                                    </span>
                                @endif

                                @if($record->car?->year)
                                    <span>
                                        {{ $record->car->year }}
                                    </span>
                                @endif

                                @if($record->car?->color)
                                    <span>
                                        {{ $record->car->color }}
                                    </span>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Details --}}
                <div class="lg:col-span-8">

                    {{-- Schedule --}}
                    <div class="border-b border-gray-200 p-5 dark:border-white/10">

                        <div class="flex items-center justify-between gap-4">

                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                                Reservation Schedule
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
                                    {{ $record->pickup_address ?: 'Office Garage' }}
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
                                    {{ $record->return_address ?: 'Office Garage' }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-5 flex flex-wrap gap-x-8 gap-y-3 border-t border-gray-100 pt-4 dark:border-white/5">

                            <div>
                                <span class="text-xs text-gray-400">
                                    Destination
                                </span>

                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $record->destination ?: '—' }}
                                </span>
                            </div>


                            <div>
                                <span class="text-xs text-gray-400">
                                    Driving
                                </span>

                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $record->with_driver ? 'With Driver' : 'Self Drive' }}
                                </span>
                            </div>


                            <div>
                                <span class="text-xs text-gray-400">
                                    Source
                                </span>

                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $record->source?->source ?? 'Standard' }}
                                </span>
                            </div>

                        </div>

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
                                    {{ $record->customer?->customer_name ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-gray-400">
                                    Contact
                                </p>

                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $record->customer?->contact_number ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-gray-400">
                                    Email
                                </p>

                                <p class="mt-1 break-all text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $record->customer?->email ?: '—' }}
                                </p>
                            </div>

                        </div>


                        @if($record->customer?->address)

                            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/5">

                                <p class="text-xs text-gray-400">
                                    Address
                                </p>

                                <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    {{ $record->customer->address }}
                                </p>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </section>


        {{-- Reservation Fee --}}
        @if(
            ($record->reservation_fee ?? 0) > 0 ||
            $record->fund_type_id
        )

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

                <div class="border-b border-gray-100 px-5 py-3.5 dark:border-white/5">

                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        Reservation Fee
                    </p>

                </div>


                <div class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-white/5 sm:grid-cols-3 sm:divide-x sm:divide-y-0">

                    <div class="px-5 py-4">

                        <p class="text-xs text-gray-400">
                            Payment Method
                        </p>

                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $record->fundType?->name ?? '—' }}
                        </p>


                        @if($record->fundType?->account_name)

                            <p class="mt-2 text-xs text-gray-400">
                                {{ $record->fundType->account_name }}
                            </p>

                        @endif


                        @if($record->fundType?->account_number)

                            <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $record->fundType->account_number }}
                            </p>

                        @endif

                    </div>


                    <div class="px-5 py-4">

                        <p class="text-xs text-gray-400">
                            Amount Paid
                        </p>

                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($record->reservation_fee ?? 0, 2) }}
                        </p>

                    </div>


                    <div class="px-5 py-4">

                        <p class="text-xs text-gray-400">
                            Payment Receipt
                        </p>


                        @if($record->reservation_fee_receipt)

                            @php
                                $receiptUrl = \Illuminate\Support\Facades\Storage::disk('s3')
                                    ->temporaryUrl(
                                        $record->reservation_fee_receipt,
                                        now()->addMinutes(15)
                                    );
                            @endphp

                            <button
                                type="button"
                                @click="openReceipt(@js($receiptUrl))"
                                class="mt-2 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                            >
                                View receipt
                            </button>

                        @else

                            <p class="mt-1 text-sm text-gray-400">
                                No receipt uploaded
                            </p>

                        @endif

                    </div>

                </div>

            </section>

        @endif


        {{-- Decision Info --}}
        @if($record->datetime_declined || $record->datetime_cancelled)

            <section class="border-l-2 border-rose-500 py-1 pl-4">

                <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                    {{ $record->datetime_declined ? 'Reservation Declined' : 'Reservation Cancelled' }}
                </p>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ $record->datetime_declined
                        ? ($record->decline_reason ?: 'No reason provided')
                        : ($record->cancellation_reason ?: 'No reason provided') }}
                </p>

            </section>

        @endif


        {{-- Customer Requirements --}}
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/5">

                <div>

                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Verification Documents
                    </h2>

                    <p class="mt-0.5 text-xs text-gray-400">
                        Customer documents submitted for this reservation.
                    </p>

                </div>


                <span class="text-xs text-gray-400">
                    {{ $record->customer?->requirements?->count() ?? 0 }}
                    document(s)
                </span>

            </div>


            @if(
                ! $record->customer ||
                $record->customer->requirements?->isEmpty()
            )

                <div class="px-5 py-12 text-center">

                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        No documents uploaded
                    </p>

                    <p class="mt-1 text-xs text-gray-400">
                        Customer verification documents will appear here.
                    </p>

                </div>

            @else

                <div class="divide-y divide-gray-100 dark:divide-white/5">

                    @foreach($record->customer->requirements as $requirement)

                        @php
                            $documentUrl = \Illuminate\Support\Facades\Storage::disk('s3')
                                ->temporaryUrl(
                                    $requirement->path,
                                    now()->addMinutes(15)
                                );

                            $extension = strtolower(
                                pathinfo(
                                    $requirement->path,
                                    PATHINFO_EXTENSION
                                )
                            );

                            $isImage = in_array(
                                $extension,
                                ['jpg', 'jpeg', 'png', 'webp']
                            );
                        @endphp


                        <div class="flex items-center gap-4 px-5 py-4">

                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/[0.025]">

                                @if($isImage)

                                    <img
                                        src="{{ $documentUrl }}"
                                        class="h-full w-full object-cover"
                                        alt=""
                                    />

                                @else

                                    <x-filament::icon
                                        icon="heroicon-o-document"
                                        class="h-5 w-5 text-gray-400"
                                    />

                                @endif

                            </div>


                            <div class="min-w-0 flex-1">

                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $requirement->requirementType?->label ?? 'Document' }}
                                </p>

                                <p class="mt-0.5 text-xs uppercase text-gray-400">
                                    {{ $extension ?: 'file' }}
                                </p>

                            </div>


                            <button
                                type="button"
                                @click="openDocument(
                                    @js($documentUrl),
                                    @js($isImage)
                                )"
                                class="shrink-0 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                            >
                                View
                            </button>

                        </div>

                    @endforeach

                </div>

            @endif

        </section>


        {{-- Receipt Modal --}}
        <div
            x-show="receiptOpen"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="receiptOpen = false"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-black/80 p-4"
        >

            <div
                @click.outside="receiptOpen = false"
                class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white dark:bg-gray-900"
            >

                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10">

                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Payment Receipt
                    </h3>

                    <button
                        type="button"
                        @click="receiptOpen = false"
                        class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    >
                        <x-filament::icon
                            icon="heroicon-o-x-mark"
                            class="h-5 w-5"
                        />
                    </button>

                </div>


                <div class="min-h-0 flex-1 overflow-auto bg-gray-50 p-4 dark:bg-black/20">

                    <img
                        x-show="receiptUrl"
                        :src="receiptUrl"
                        class="mx-auto max-h-[70vh] max-w-full object-contain"
                        alt="Payment Receipt"
                    />

                </div>

            </div>

        </div>


        {{-- Document Modal --}}
        <div
            x-show="documentOpen"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="documentOpen = false"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-black/80 p-4"
        >

            <div
                @click.outside="documentOpen = false"
                class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white dark:bg-gray-900"
            >

                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10">

                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Verification Document
                    </h3>

                    <button
                        type="button"
                        @click="documentOpen = false"
                        class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    >
                        <x-filament::icon
                            icon="heroicon-o-x-mark"
                            class="h-5 w-5"
                        />
                    </button>

                </div>


                <div class="min-h-0 flex-1 overflow-auto bg-gray-50 p-4 dark:bg-black/20">

                    <template x-if="documentIsImage">
                        <img
                            :src="documentUrl"
                            class="mx-auto max-h-[72vh] max-w-full object-contain"
                            alt=""
                        />
                    </template>


                    <template x-if="! documentIsImage">

                        <div class="py-16 text-center">

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Preview is not available for this file type.
                            </p>

                            <a
                                :href="documentUrl"
                                target="_blank"
                                class="mt-3 inline-block text-sm font-medium text-primary-600 dark:text-primary-400"
                            >
                                Open document
                            </a>

                        </div>

                    </template>

                </div>

            </div>

        </div>

    </div>


    @once
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endonce

</x-filament::page>