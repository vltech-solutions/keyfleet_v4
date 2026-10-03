@php
    use Filament\Facades\Filament;
    use Carbon\Carbon;

    $net = $totalIncome - $totalExpenses;
    $isPositive = $net >= 0;

    $periodLabel = match ($filter_period) {
        'yearly' => (string) $year,

        'custom' => $date_from && $date_to
            ? Carbon::parse($date_from)->format('M d, Y')
                . ' – '
                . Carbon::parse($date_to)->format('M d, Y')
            : 'Custom Period',

        default => $month !== 'all'
            ? Carbon::create()
                ->month((int) $month)
                ->format('F') . ' ' . $year
            : 'All Months ' . $year,
    };

    $typeLabel = match ($type) {
        'income' => 'Income Only',
        'expense' => 'Expenses Only',
        default => 'All Transactions',
    };

    $fundLabel = $fund_type === 'all'
        ? 'All Funds'
        : \App\Models\FundType::find($fund_type)?->name ?? 'Selected Fund';
@endphp

<x-filament-panels::page>

    <div class="space-y-6">

        {{-- REPORT NOTE --}}
        <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.025]">
            <x-filament::icon
                icon="heroicon-o-information-circle"
                class="mt-0.5 h-5 w-5 shrink-0 text-primary-500"
            />

            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Income Flow Report
                </p>

                <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Expenses shown in this report include only transactions deducted from company funds.
                    Partner's Fund transactions are excluded.
                </p>
            </div>
        </div>


        {{-- REPORT FILTERS --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

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
                            Refine the transactions included in this report.
                        </p>
                    </div>
                </div>

                <div class="text-xs text-gray-400">
                    Updates automatically
                </div>
            </div>


            <div class="p-5 sm:p-6">

                {{-- Primary filters --}}
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

                    {{-- Report Period --}}
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Report Period
                        </label>

                        <div class="inline-flex w-full rounded-xl bg-gray-100 p-1 dark:bg-white/5">
                            <button
                                type="button"
                                wire:click="$set('filter_period', 'monthly')"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $filter_period === 'monthly'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                <x-filament::icon
                                    icon="heroicon-o-calendar-days"
                                    class="h-4 w-4"
                                />
                                Monthly
                            </button>

                            <button
                                type="button"
                                wire:click="$set('filter_period', 'yearly')"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $filter_period === 'yearly'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                Yearly
                            </button>

                            <button
                                type="button"
                                wire:click="$set('filter_period', 'custom')"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $filter_period === 'custom'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                Custom
                            </button>
                        </div>
                    </div>


                    {{-- Transaction Type --}}
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Transaction Type
                        </label>

                        <div class="inline-flex w-full rounded-xl bg-gray-100 p-1 dark:bg-white/5">
                            <button
                                type="button"
                                wire:click="$set('type', 'all')"
                                class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $type === 'all'
                                        ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                All
                            </button>

                            <button
                                type="button"
                                wire:click="$set('type', 'income')"
                                class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $type === 'income'
                                        ? 'bg-white text-emerald-600 shadow-sm dark:bg-gray-800 dark:text-emerald-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                Income
                            </button>

                            <button
                                type="button"
                                wire:click="$set('type', 'expense')"
                                class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $type === 'expense'
                                        ? 'bg-white text-rose-600 shadow-sm dark:bg-gray-800 dark:text-rose-400'
                                        : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}"
                            >
                                Expenses
                            </button>
                        </div>
                    </div>


                    {{-- Fund --}}
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Fund
                        </label>

                        <select
                            wire:model.live="fund_type"
                            class="block w-full rounded-xl border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="all">All Funds</option>

                            @foreach(
                                \App\Models\FundType::query()
                                    ->where('company_id', Filament::getTenant()?->id)
                                    ->where('name', '!=', "Partner's Fund")
                                    ->orderBy('name')
                                    ->get()
                                as $fund
                            )
                                <option value="{{ $fund->id }}">
                                    {{ $fund->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>


                {{-- Date controls --}}
                <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/5 dark:bg-white/[0.02]">

                    @if($filter_period === 'monthly')
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Month
                                </label>

                                <select
                                    wire:model.live="month"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    <option value="all">All Months</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Year
                                </label>

                                <select
                                    wire:model.live="year"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    @foreach(range(now()->year, 2000) as $optionYear)
                                        <option value="{{ $optionYear }}">
                                            {{ $optionYear }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    @elseif($filter_period === 'yearly')

                        <div class="max-w-sm">
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                Report Year
                            </label>

                            <select
                                wire:model.live="year"
                                class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                            >
                                @foreach(range(now()->year, 2000) as $optionYear)
                                    <option value="{{ $optionYear }}">
                                        {{ $optionYear }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    @else

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Date From
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="date_from"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Date To
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="date_to"
                                    min="{{ $date_from }}"
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                                />
                            </div>

                        </div>

                    @endif

                </div>

            </div>
        </section>


        {{-- REPORT CONTEXT --}}
        <div class="flex flex-col gap-3 border-b border-gray-200 pb-4 dark:border-white/10 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                    Selected Report
                </p>

                <h2 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                    {{ $periodLabel }}
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $typeLabel }} · {{ $fundLabel }}
                </p>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ number_format($total) }}
                {{ Str::plural('transaction', $total) }}
            </div>
        </div>


        {{-- FINANCIAL SUMMARY --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Total Income --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Income
                        </p>

                        <p class="mt-3 truncate text-2xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400 sm:text-3xl">
                            ₱{{ number_format($totalIncome, 2) }}
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-arrow-down-left"
                        class="h-6 w-6 shrink-0 text-emerald-500"
                    />
                </div>

                <p class="mt-6 text-xs text-gray-400">
                    Cash received from rental transactions
                </p>
            </div>


            {{-- Total Expenses --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Expenses
                        </p>

                        <p class="mt-3 truncate text-2xl font-semibold tracking-tight text-rose-600 dark:text-rose-400 sm:text-3xl">
                            ₱{{ number_format($totalExpenses, 2) }}
                        </p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-arrow-up-right"
                        class="h-6 w-6 shrink-0 text-rose-500"
                    />
                </div>

                <p class="mt-6 text-xs text-gray-400">
                    Cash deducted from company funds
                </p>
            </div>


            {{-- Net Cash Flow --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Net Cash Flow
                        </p>

                        <p class="mt-3 truncate text-2xl font-semibold tracking-tight sm:text-3xl
                            {{ $isPositive
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-rose-600 dark:text-rose-400' }}"
                        >
                            {{ $isPositive ? '+' : '-' }}₱{{ number_format(abs($net), 2) }}
                        </p>
                    </div>

                    <x-filament::icon
                        :icon="$isPositive
                            ? 'heroicon-o-arrow-trending-up'
                            : 'heroicon-o-arrow-trending-down'"
                        class="h-6 w-6 shrink-0
                            {{ $isPositive
                                ? 'text-emerald-500'
                                : 'text-rose-500' }}"
                    />
                </div>

                <div class="mt-6 flex items-center gap-2">
                    <span
                        class="h-2 w-2 rounded-full
                            {{ $isPositive
                                ? 'bg-emerald-500'
                                : 'bg-rose-500' }}"
                    ></span>

                    <p class="text-xs text-gray-400">
                        {{ $isPositive
                            ? 'Positive cash flow'
                            : 'Negative cash flow' }}
                    </p>
                </div>
            </div>

        </section>


        {{-- TRANSACTION LEDGER --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

            {{-- Header --}}
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-5 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        Transaction Ledger
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Cash inflows and outflows included in the selected report.
                    </p>
                </div>


                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">
                        Rows
                    </span>

                    <select
                        wire:model.live="perPage"
                        class="rounded-lg border-gray-300 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

            </div>


            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left">

                    <thead class="border-b border-gray-100 bg-gray-50/80 dark:border-white/5 dark:bg-white/[0.02]">
                        <tr class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">

                            <th class="whitespace-nowrap px-5 py-3.5 sm:px-6">
                                Date
                            </th>

                            <th class="whitespace-nowrap px-5 py-3.5">
                                Fund
                            </th>

                            <th class="min-w-[280px] px-5 py-3.5">
                                Description
                            </th>

                            <th class="whitespace-nowrap px-5 py-3.5 text-right">
                                Cash In
                            </th>

                            <th class="whitespace-nowrap px-5 py-3.5 text-right sm:pr-6">
                                Cash Out
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">

                        @forelse($records as $record)

                            @php
                                $isIncome = $record['type'] === 'income';
                                $companySlug = Filament::getTenant()?->slug;
                            @endphp

                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.025]">

                                {{-- Date --}}
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-800 dark:text-gray-200 sm:px-6">
                                    {{ Carbon::parse($record['date'])->format('M d, Y') }}
                                </td>


                                {{-- Fund --}}
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $record['fund_name'] ?? 'N/A' }}
                                    </span>
                                </td>


                                {{-- Description --}}
                                <td class="px-5 py-4">

                                    <div class="flex items-start gap-2.5">
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full
                                            {{ $isIncome ? 'bg-emerald-500' : 'bg-rose-500' }}">
                                        </span>

                                        <div class="min-w-0">

                                            @if($isIncome && filled($record['booking_id'] ?? null))

                                                <p class="text-sm text-gray-700 dark:text-gray-200">
                                                    {{ filled($record['description'] ?? null)
                                                        ? $record['description']
                                                        : 'Booking payment' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-400">
                                                    Booking payment from

                                                    <a
                                                        href="{{ url('/app/' . $companySlug . '/bookings/' . $record['booking_id'] . '/edit') }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="font-medium text-primary-600 hover:underline dark:text-primary-400"
                                                    >
                                                        {{ $record['renter_name'] ?? 'Unknown renter' }}
                                                    </a>
                                                </p>

                                            @else

                                                <p class="text-sm text-gray-700 dark:text-gray-200">
                                                    {{ $record['description'] ?? 'Expense transaction' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-400">
                                                    Expense
                                                </p>

                                            @endif

                                        </div>
                                    </div>

                                </td>


                                {{-- Cash In --}}
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold">
                                    @if($isIncome)
                                        <span class="text-emerald-600 dark:text-emerald-400">
                                            ₱{{ number_format($record['amount'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>


                                {{-- Cash Out --}}
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold sm:pr-6">
                                    @unless($isIncome)
                                        <span class="text-rose-600 dark:text-rose-400">
                                            ₱{{ number_format($record['amount'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endunless
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">

                                    <x-filament::icon
                                        icon="heroicon-o-document-magnifying-glass"
                                        class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600"
                                    />

                                    <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">
                                        No transactions found
                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        Try changing the report filters.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>


                    @if($total > 0)
                        <tfoot class="border-t border-gray-200 bg-gray-50/80 dark:border-white/10 dark:bg-white/[0.025]">

                            <tr>
                                <td colspan="3" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    Report Totals
                                </td>

                                <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    ₱{{ number_format($totalIncome, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-rose-600 dark:text-rose-400 sm:pr-6">
                                    ₱{{ number_format($totalExpenses, 2) }}
                                </td>
                            </tr>

                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td colspan="4" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    Net Cash Flow
                                </td>

                                <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-bold
                                    {{ $isPositive
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-rose-600 dark:text-rose-400' }} sm:pr-6">
                                    {{ $isPositive ? '+' : '-' }}₱{{ number_format(abs($net), 2) }}
                                </td>
                            </tr>

                        </tfoot>
                    @endif

                </table>
            </div>


            {{-- Pagination --}}
            <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <p class="text-xs text-gray-400">
                    Showing
                    <strong class="font-medium text-gray-700 dark:text-gray-300">
                        {{ $total > 0 ? (($page - 1) * $perPage + 1) : 0 }}
                    </strong>
                    to
                    <strong class="font-medium text-gray-700 dark:text-gray-300">
                        {{ min($page * $perPage, $total) }}
                    </strong>
                    of
                    <strong class="font-medium text-gray-700 dark:text-gray-300">
                        {{ number_format($total) }}
                    </strong>
                </p>


                @if($total > $perPage)
                    @php
                        $lastPage = (int) ceil($total / $perPage);
                        $startPage = max($page - 2, 1);
                        $endPage = min($page + 2, $lastPage);
                    @endphp

                    <div class="flex items-center gap-1">

                        <button
                            type="button"
                            wire:click="$set('page', {{ max(1, $page - 1) }})"
                            @disabled($page <= 1)
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-40 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5"
                        >
                            <x-filament::icon
                                icon="heroicon-m-chevron-left"
                                class="h-4 w-4"
                            />
                        </button>


                        @for($i = $startPage; $i <= $endPage; $i++)
                            <button
                                type="button"
                                wire:click="$set('page', {{ $i }})"
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold transition
                                    {{ $i === $page
                                        ? 'bg-primary-600 text-white'
                                        : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5' }}"
                            >
                                {{ $i }}
                            </button>
                        @endfor


                        <button
                            type="button"
                            wire:click="$set('page', {{ min($lastPage, $page + 1) }})"
                            @disabled($page >= $lastPage)
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-40 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5"
                        >
                            <x-filament::icon
                                icon="heroicon-m-chevron-right"
                                class="h-4 w-4"
                            />
                        </button>

                    </div>
                @endif

            </div>

        </section>

    </div>

</x-filament-panels::page>