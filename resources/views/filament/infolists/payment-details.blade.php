@php
    $record = $getRecord();
    $percentage = $record->total_due > 0 ? min(($record->paid_amount / $record->total_due) * 100, 100) : 0;
@endphp

<div class="space-y-8">
    {{-- Summary Cards: Enhanced with Glass Effects and Depth --}}
    <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 xl:grid-cols-3">
    
    <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400  ">Total Commitment</h3>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                ₱{{ number_format($record->total_due, 2) }}
            </p>
            <div class="mt-2">
                <span class="text-[10px] font-bold px-2 py-0.5 bg-gray-100 dark:bg-white/5 text-gray-500 rounded-full">Gross Amount</span>
            </div>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-500/10">
            <x-heroicon-o-calculator class="w-7 h-7 text-gray-400" />
        </div>
    </div>

    <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
        <div>
            <h3 class="text-sm font-medium text-emerald-500 dark:text-emerald-400  ">Total Remitted</h3>
            <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format($record->paid_amount, 2) }}
            </p>
            <div class="mt-2">
                <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-full">
                    {{ number_format($percentage, 1) }}% Covered
                </span>
            </div>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-emerald-500/10">
            <x-heroicon-o-check-badge class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
        </div>
    </div>

    <div class="relative overflow-hidden flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
        <div class="z-10">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400  ">Outstanding Payables</h3>
            <p class="mt-1 text-2xl font-bold {{ $record->balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                ₱{{ number_format($record->balance, 2) }}
            </p>
            <div class="mt-2">
                <span class="text-[10px] font-bold px-2 py-0.5 {{ $record->balance > 0 ? 'bg-rose-500/10 text-rose-600' : 'bg-gray-500/10 text-gray-500' }} rounded-full">
                    {{ $record->balance > 0 ? 'Payment Pending' : 'Fully Settled' }}
                </span>
            </div>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-full {{ $record->balance > 0 ? 'bg-rose-500/10' : 'bg-gray-500/10' }} z-10">
            <x-heroicon-o-credit-card class="w-7 h-7 {{ $record->balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}" />
        </div>

        <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-100 dark:bg-white/5">
            <div class="h-full bg-emerald-500 transition-all duration-1000 ease-out shadow-[0_-2px_10px_rgba(16,185,129,0.3)]" style="width: {{ $percentage }}%"></div>
        </div>
    </div>

</div>

    {{-- Payments Table: Refined Data Grid --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-[2rem] shadow-sm overflow-hidden transition-colors">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-white/5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="w-2 h-6 bg-primary-500 rounded-full"></div>
                <h3 class="font-bold text-lg tracking-tight text-gray-800 dark:text-gray-200">Transaction History</h3>
            </div>
            <span class="text-sm font-bold px-3 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-full text-gray-500">
                {{ count($record->payments) }} Record(s)
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-sm font-bold   text-gray-400 border-b border-gray-100 dark:border-white/5">
                        <th class="px-8 py-4">Fund Source</th>
                        <th class="px-8 py-4">Settled Amount</th>
                        <th class="px-8 py-4 text-center">Posting Date</th>
                        <th class="px-8 py-4">Internal Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @forelse($record->payments as $payment)
                        <tr class="group  transition-all">
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-[11px] font-bold  bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-primary-500/20">
                                    {{ $payment->fundType?->name ?? 'Direct Settlement' }}
                                </span>
                            </td>
                            <td class="px-8 py-5 font-bold text-gray-900 dark:text-white tracking-tight">
                                ₱{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">
                                        {{ date('M d, Y', strtotime($payment->payment_date)) }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-400  ">Verified Posting</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-gray-300" />
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 max-w-[200px] truncate italic" title="{{ $payment->payment_notes }}">
                                        {{ $payment->payment_notes ?: 'No additional context' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-full ring-8 ring-gray-50/50 dark:ring-white/[0.02]">
                                        <x-heroicon-o-banknotes class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                    </div>
                                    <div class="max-w-xs">
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200 ">Awaiting First Payment</p>
                                        <p class="text-xs font-medium text-gray-400 mt-1">No transaction logs have been generated for this ledger yet.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer: Subtle Branding/Info --}}
        <div class="px-8 py-4 bg-gray-50/50 dark:bg-gray-900 border-t border-gray-100 dark:border-white/5">
            <p class="text-xs font-bold text-gray-400 flex items-center gap-1.5 ">
                <x-heroicon-s-shield-check class="w-3.5 h-3.5" /> All payments are processed through secure channels
            </p>
        </div>
    </div>
</div>