@php
    $record = $getRecord();

    $totalDue = (float) ($record->total_due ?? 0);
    $paid = (float) ($record->paid_amount ?? 0);
    $balance = (float) ($record->balance ?? 0);

    $percentage = $totalDue > 0
        ? min(($paid / $totalDue) * 100, 100)
        : 0;

    $isSettled = $balance <= 0;
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

    {{-- Header --}}
    <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                Payment Ledger
            </p>

            <h2 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                Booking {{ $record->booking_id ?: '#' . $record->id }}
            </h2>
        </div>


        <div class="flex items-center gap-2 text-sm font-medium
            {{ $isSettled
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-rose-600 dark:text-rose-400' }}"
        >
            <span class="h-2 w-2 rounded-full
                {{ $isSettled ? 'bg-emerald-500' : 'bg-rose-500' }}">
            </span>

            {{ $isSettled ? 'Fully Settled' : 'Balance Due' }}
        </div>

    </div>


    {{-- Financial Summary --}}
    <div class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-white/5 sm:grid-cols-3 sm:divide-x sm:divide-y-0">

        <div class="px-5 py-4">
            <p class="text-xs text-gray-400">
                Total Due
            </p>

            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                ₱{{ number_format($totalDue, 2) }}
            </p>
        </div>


        <div class="px-5 py-4">
            <p class="text-xs text-gray-400">
                Amount Paid
            </p>

            <p class="mt-1 text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format($paid, 2) }}
            </p>
        </div>


        <div class="px-5 py-4">
            <p class="text-xs text-gray-400">
                Remaining Balance
            </p>

            <p class="mt-1 text-lg font-semibold
                {{ $balance > 0
                    ? 'text-rose-600 dark:text-rose-400'
                    : 'text-gray-900 dark:text-white' }}"
            >
                ₱{{ number_format(max($balance, 0), 2) }}
            </p>
        </div>

    </div>


    {{-- Payment Progress --}}
    <div class="border-t border-gray-100 px-5 py-4 dark:border-white/5">

        <div class="mb-2 flex items-center justify-between text-xs">

            <span class="text-gray-400">
                Payment progress
            </span>

            <span class="font-medium text-gray-600 dark:text-gray-300">
                {{ number_format($percentage, 1) }}%
            </span>

        </div>


        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">

            <div
                class="h-full rounded-full
                    {{ $isSettled ? 'bg-emerald-500' : 'bg-primary-500' }}"
                style="width: {{ $percentage }}%"
            ></div>

        </div>

    </div>


    {{-- Transaction Header --}}
    <div class="flex items-center justify-between border-t border-gray-200 px-5 py-4 dark:border-white/10">

        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Transactions
            </h3>

            <p class="mt-0.5 text-xs text-gray-400">
                Payments recorded against this booking.
            </p>
        </div>


        <span class="text-xs text-gray-400">
            {{ $record->payments->count() }}
            {{ \Illuminate\Support\Str::plural('entry', $record->payments->count()) }}
        </span>

    </div>


    @if($record->payments->isNotEmpty())

        {{-- Desktop --}}
        <div class="hidden border-t border-gray-100 dark:border-white/5 md:block">

            <table class="w-full text-left">

                <thead class="bg-gray-50/60 dark:bg-white/[0.02]">

                    <tr class="text-[11px] font-medium uppercase tracking-wider text-gray-400">

                        <th class="px-5 py-3">
                            Date
                        </th>

                        <th class="px-5 py-3">
                            Fund
                        </th>

                        <th class="px-5 py-3">
                            Notes
                        </th>

                        <th class="px-5 py-3 text-right">
                            Amount
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100 dark:divide-white/5">

                    @foreach($record->payments as $payment)

                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">

                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $payment->payment_date
                                        ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y')
                                        : '—' }}
                                </p>

                            </td>


                            <td class="px-5 py-4">

                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $payment->fundType?->name ?? 'Direct Settlement' }}
                                </p>

                            </td>


                            <td class="px-5 py-4">

                                <p class="max-w-lg text-sm text-gray-500 dark:text-gray-400">
                                    {{ $payment->payment_notes ?: '—' }}
                                </p>

                            </td>


                            <td class="whitespace-nowrap px-5 py-4 text-right">

                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    ₱{{ number_format($payment->amount ?? 0, 2) }}
                                </span>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        {{-- Mobile --}}
        <div class="divide-y divide-gray-100 border-t border-gray-100 dark:divide-white/5 dark:border-white/5 md:hidden">

            @foreach($record->payments as $payment)

                <article class="px-4 py-4">

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $payment->fundType?->name ?? 'Direct Settlement' }}
                            </p>

                            <p class="mt-1 text-xs text-gray-400">
                                {{ $payment->payment_date
                                    ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y')
                                    : 'No payment date' }}
                            </p>

                        </div>


                        <p class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white">
                            ₱{{ number_format($payment->amount ?? 0, 2) }}
                        </p>

                    </div>


                    @if($payment->payment_notes)

                        <p class="mt-3 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_notes }}
                        </p>

                    @endif

                </article>

            @endforeach

        </div>

    @else

        {{-- Empty --}}
        <div class="border-t border-gray-100 px-5 py-12 text-center dark:border-white/5">

            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                No payments recorded
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Payment entries will appear here once recorded.
            </p>

        </div>

    @endif


    {{-- Footer Summary --}}
    @if($record->payments->isNotEmpty())

        <div class="flex flex-col gap-2 border-t border-gray-200 bg-gray-50/60 px-5 py-4 dark:border-white/10 dark:bg-white/[0.02] sm:flex-row sm:items-center sm:justify-between">

            <span class="text-xs text-gray-400">
                Total recorded payments
            </span>

            <span class="text-base font-semibold text-gray-900 dark:text-white">
                ₱{{ number_format($record->payments->sum('amount'), 2) }}
            </span>

        </div>

    @endif

</div>