@php
    use Filament\Facades\Filament;

   
    $net = $totalIncome - $totalExpenses; // if you need it
@endphp
<x-filament-panels::page>
    <span class="text-xs"><b>Note:</b> This list includes only expenses that were deducted from funds.</span>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>
    
    <div class="grid w-full grid-cols-1 gap-6 mt-4 md:grid-cols-2">

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Income</div>
                <div class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                    ₱{{ number_format($totalIncome, 2) }}
                </div>
                <div class="text-sm text-green-500/80">
                    Gross revenue collected
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l-3-3m3 3l3-3M4 4h16v16H4V4z" />
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Expense</div>
                <div class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                    ₱{{ number_format($totalExpenses, 2) }}
                </div>
                <div class="text-sm text-red-500/80">
                    Total cash outflow
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500/10">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V8m0 0l3 3m-3-3l-3 3M4 4h16v16H4V4z" />
                </svg>
            </div>
        </div>

    </div>


    <div x-data>
        <div
            class="overflow-hidden bg-white divide-y divide-gray-200 shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10"
            >
            <div class="relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
                <table class="w-full divide-y divide-gray-200 table-auto text-start dark:divide-white/5 whitespace-nowrap">
                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr class="text-sm font-semibold text-left bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white">
                            <th class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Date</th>
                            <th class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Fund Type</th>
                            <th class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Transaction</th>
                            <th class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Description</th>
                            <th class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($records as $record)
                        <tr>
                            <td class="px-3 py-2 text-sm leading-6 fi-ta-text-item-label text-gray-950 dark:text-white ">{{ \Carbon\Carbon::parse($record['date'])->format('M d, Y') }}</td>
                            <td class="px-3 py-2 text-sm leading-6 fi-ta-text-item-label text-gray-950 dark:text-white ">{{ ucfirst($record['fund_name']) }}</td>
                            <td class="px-3 py-2 text-sm leading-6">
                                @php
                                $isIncome = $record['type'] === 'income';
                                // Set colors dynamically like Filament does
                                $color50 = $isIncome ? 'var(--success-50)' : 'var(--danger-50)';
                                $color400 = $isIncome ? 'var(--success-400)' : 'var(--danger-400)';
                                $color600 = $isIncome ? 'var(--success-600)' : 'var(--danger-600)';
                                @endphp
                                <span
                                    style="--c-50:{{ $color50 }};--c-400:{{ $color400 }};--c-600:{{ $color600 }};"
                                    class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-md fi-badge gap-x-1 ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30"
                                    >
                                <span class="truncate">
                                {{ ucfirst($record['type']) }}
                                </span>
                                </span>
                            </td>
                            <td class="px-3 py-2 text-sm leading-6 fi-ta-text-item-label text-gray-950 dark:text-white ">
                                @php
                                $company = Filament::getTenant()->slug;
                                @endphp
                                @if ($record['type'] === 'income' && isset($record['booking_id']))
                                {{ $record['description'] && $record['description'] != '' ? $record['description'] : 'Booking payment' }} from <a
                                    href="{{ url('/app/' . $company . '/bookings/' . $record['booking_id'] . '/edit') }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-blue-600 hover:underline"
                                    >
                                {{ $record['renter_name'] ?? '' }}
                                </a>
                                @else
                                {{ $record['description'] ?? '-' }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm leading-6 text-right fi-ta-text-item-label
                                {{ $record['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $record['type'] === 'income' ? '+' : '-' }}₱{{ number_format($record['amount'], 2) }}
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-2 text-sm leading-6 text-center text-gray-500 fi-ta-text-item-label text-gray-950 dark:text-white dark:text-gray-400">
                                No records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-semibold text-gray-900 dark:text-white">Showing</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                        {{ $total > 0 ? (($page - 1) * $perPage + 1) : '0' }}
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white">to {{ min($page * $perPage, $total) }} of {{ $total }} results</span>
                    </div>
                </div>
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&amp;:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500" style="display: none;">
                    <!--[if BLOCK]><![endif]-->        
                    <div class="flex items-center border-gray-200 fi-input-wrp-prefix gap-x-3 ps-3 border-e pe-3 dark:border-white/10">
                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]-->                <span class="text-sm text-gray-500 fi-input-wrp-label whitespace-nowrap dark:text-gray-400">
                        Per page
                        </span>
                        <!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <!--[if ENDBLOCK]><![endif]-->
                    <div class="flex-1 min-w-0 fi-input-wrp-input">
                        <select class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 text-base text-gray-950 transition duration-75 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] dark:text-white dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6 [&amp;_optgroup]:bg-white [&amp;_optgroup]:dark:bg-gray-900 [&amp;_option]:bg-white [&amp;_option]:dark:bg-gray-900 ps-3" wire:model.live="perPage">
                            <!--[if BLOCK]><![endif]-->                            
                            <option value="10">
                                10
                            </option>
                            <option value="25">
                                25
                            </option>
                            <option value="50">
                                50
                            </option>
                            <option value="100">
                                100
                            </option>
                            <!--[if ENDBLOCK]><![endif]-->
                        </select>
                    </div>
                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                </div>
                @if($total > 0 && $total > $perPage)
                    <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        @php
                        $lastPage = (int) ceil($total / $perPage);
                        $startPage = max($page - 2, 1);
                        $endPage = min($page + 2, $lastPage);
                        @endphp
                        {{-- Previous button --}}
                        @if ($page > 1)
                        <button
                            wire:click="$set('page', {{ $page - 1 }})"
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
                            aria-label="Previous"
                            >
                            <svg
                                class="w-5 h-5 transition duration-75"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true"
                                >
                                <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        @endif
                        {{-- Numbered page buttons --}}
                        @for ($i = $startPage; $i <= $endPage; $i++)
                        <button
                            wire:click="$set('page', {{ $i }})"
                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                            {{ $i === $page
                                ? 'text-primary-600 bg-gray-100 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-primary-400'
                                : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50
                                dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400
                                dark:hover:bg-gray-700' }}
                            {{ $i === $startPage && $page === 1 ? 'rounded-l-md' : '' }}
                            {{ $i === $endPage && $page >= $lastPage ? 'rounded-r-md' : '' }}"
                            aria-current="{{ $i === $page ? 'page' : false }}"
                        >
                            {{ $i }}
                        </button>


                        @endfor
                        {{-- Next button --}}
                        @if ($page < $lastPage)
                        <button
                        @if ($page >= $lastPage) disabled @endif
                        wire:click="$set('page', {{ $page + 1 }})"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
                        aria-label="Next"
                        >
                        <svg class="w-5 h-5 text-gray-400 transition duration-75 fi-pagination-item-icon group-hover/button:text-gray-500 dark:text-gray-500 dark:group-hover/button:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
                        </svg>
                        </button>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>