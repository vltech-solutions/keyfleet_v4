@php
    use Filament\Facades\Filament;
    use Carbon\Carbon;
    
    $net = $totalIncome - $totalExpenses;
    $isPositive = $net >= 0;
    
    // Calculate percentages for progress bars
    $totalCombined = $totalIncome + $totalExpenses;
    $incomePercent = $totalCombined > 0 ? ($totalIncome / $totalCombined) * 100 : 0;
    $expensePercent = $totalCombined > 0 ? ($totalExpenses / $totalCombined) * 100 : 0;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Note and Export Button -->
        <div class="flex items-center justify-between p-4 text-sm bg-blue-50 rounded-lg dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Note:</strong> This report includes only expenses that were deducted from funds.</span>
            </div>
            <button wire:click="exportPdf" style="display: none"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </button>
        </div>

        <!-- Filters Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <form wire:submit.prevent>
                {{ $this->form }}
            </form>
        </div>

        <!-- Statistics Cards - Inspired by Fleet Report -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Income Card -->
            <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</h3>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                        ₱{{ number_format($totalIncome, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Gross revenue collected</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Total Expenses Card -->
            <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</h3>
                    <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                        ₱{{ number_format($totalExpenses, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Total cash outflow</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500/10">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Net Cash Flow Card -->
            <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200 {{ $isPositive ? 'border-l-4 border-green-500' : 'border-l-4 border-red-500' }}">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Cash Flow</h3>
                    <p class="mt-1 text-2xl font-bold {{ $isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $isPositive ? '+' : '' }}₱{{ number_format($net, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">{{ $isPositive ? 'Positive cash flow' : 'Negative cash flow' }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-full {{ $isPositive ? 'bg-green-500/10' : 'bg-red-500/10' }}">
                    <svg class="w-7 h-7 {{ $isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isPositive ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6' }}"/>
                    </svg>
                </div>
            </div>

            <!-- Total Transactions Card -->
            <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</h3>
                    <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($total) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Combined income & expenses</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div x-data class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
            <!-- Table Header with Stats -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transaction Ledger</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Showing {{ $total > 0 ? (($page - 1) * $perPage + 1) : '0' }} 
                            to {{ min($page * $perPage, $total) }} of {{ number_format($total) }} transactions
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Per page:</span>
                        <select wire:model.live="perPage" class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Fund Type</th>
                            <th scope="col" class="px-6 py-3">Transaction</th>
                            <th scope="col" class="px-6 py-3">Description</th>
                            <th scope="col" class="px-6 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ Carbon::parse($record['date'])->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ ucfirst($record['fund_name'] ?? 'N/A') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $isIncome = $record['type'] === 'income';
                                    $badgeClass = $isIncome 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ ucfirst($record['type']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $company = Filament::getTenant()->slug;
                                @endphp
                                @if ($record['type'] === 'income' && isset($record['booking_id']) && $record['booking_id'])
                                    <span class="text-gray-700 dark:text-gray-300">
                                        {{ $record['description'] && $record['description'] != '' ? $record['description'] : 'Booking payment' }}
                                    </span>
                                    from 
                                    <a href="{{ url('/app/' . $company . '/bookings/' . $record['booking_id'] . '/edit') }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline font-medium">
                                        {{ $record['renter_name'] ?? 'Unknown' }}
                                    </a>
                                @else
                                    <span class="text-gray-700 dark:text-gray-300">
                                        {{ $record['description'] ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-medium
                                {{ $record['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $record['type'] === 'income' ? '+' : '-' }}₱{{ number_format($record['amount'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h11.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V17a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400">No transactions found for the selected filters.</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Try adjusting your filter criteria</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer with Pagination -->
            <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-semibold text-gray-900 dark:text-white">Showing</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $total > 0 ? (($page - 1) * $perPage + 1) : '0' }}
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white">to {{ min($page * $perPage, $total) }} of {{ number_format($total) }} results</span>
                    </div>
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
                            <button wire:click="$set('page', {{ $page - 1 }})" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700" aria-label="Previous">
                                <svg class="w-5 h-5 transition duration-75" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                        {{-- Numbered page buttons --}}
                        @for ($i = $startPage; $i <= $endPage; $i++)
                            <button wire:click="$set('page', {{ $i }})" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                {{ $i === $page
                                    ? 'text-primary-600 bg-gray-100 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-primary-400'
                                    : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700' }}
                                {{ $i === $startPage && $page === 1 ? 'rounded-l-md' : '' }}
                                {{ $i === $endPage && $page >= $lastPage ? 'rounded-r-md' : '' }}" aria-current="{{ $i === $page ? 'page' : false }}">
                                {{ $i }}
                            </button>
                        @endfor
                        {{-- Next button --}}
                        @if ($page < $lastPage)
                            <button wire:click="$set('page', {{ $page + 1 }})" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700" aria-label="Next">
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
</x-filament-panels:page>