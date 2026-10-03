<x-filament-panels::page>
    @php
        $report = $this->report;
        $totals = $report['totals'];
        $reportNames = [
            'revenue' => 'SaaS revenue',
            'subscriptions' => 'Subscriptions',
            'agents' => 'Agent performance',
            'commissions' => 'Commissions',
        ];
    @endphp

    <div class="relative space-y-6">
        <div wire:loading.flex wire:target="reportType,period,planId,agentId,subscriptionStatus,commissionStatus,customStart,customEnd" class="absolute inset-0 z-50 items-start justify-center rounded-3xl bg-white/60 pt-40 backdrop-blur-[1px] dark:bg-gray-950/60">
            <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-lg dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
                <x-filament::loading-indicator class="h-5 w-5" /> Updating SaaS report…
            </div>
        </div>

        <header class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-600 dark:text-amber-400">KeyFleet business intelligence</p>
                    <h1 class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">SaaS reports</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $this->range['label'] }} · All money values are PHP.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ \App\Filament\Admin\Pages\Dashboard::getUrl() }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" /> Dashboard
                    </a>
                    <button type="button" wire:click="exportCsv" class="inline-flex items-center gap-2 rounded-xl bg-gray-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200">
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" /> Export CSV
                    </button>
                </div>
            </div>

            <div class="mt-6 flex gap-2 overflow-x-auto border-b border-gray-100 pb-4 dark:border-white/10">
                @foreach($reportNames as $key => $name)
                    <button type="button" wire:click="$set('reportType', '{{ $key }}')" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition {{ $reportType === $key ? 'bg-amber-500 text-gray-950 shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10' }}">
                        {{ $name }}
                    </button>
                @endforeach
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                <label>
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date range</span>
                    <select wire:model.live="period" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
                        @foreach(\App\Services\AdminAnalyticsService::PERIODS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </label>

                @if(in_array($reportType, ['revenue', 'subscriptions'], true))
                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</span>
                        <select wire:model.live="planId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
                            <option value="all">All plans</option>
                            @foreach($this->plans as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                    </label>
                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subscription state</span>
                        <select wire:model.live="subscriptionStatus" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
                            <option value="all">All states</option><option value="active">Active</option><option value="expired">Expired</option><option value="refunded">Refunded</option>
                        </select>
                    </label>
                @elseif($reportType === 'commissions')
                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Agent</span>
                        <select wire:model.live="agentId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
                            <option value="all">All agents</option>
                            @foreach($this->agents as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                    </label>
                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Commission state</span>
                        <select wire:model.live="commissionStatus" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
                            @foreach($this->commissionStatusOptions() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </label>
                @endif

                @if($period === 'custom')
                    <label><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</span><input type="date" wire:model.live="customStart" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100" /></label>
                    <label><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</span><input type="date" wire:model.live="customEnd" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100" /></label>
                @endif
            </div>
        </header>

        @if($reportType === 'agents')
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['label' => 'Referrals', 'value' => number_format($totals['referrals'])],
                    ['label' => 'Conversions', 'value' => number_format($totals['conversions'])],
                    ['label' => 'Attributed revenue', 'value' => '₱'.number_format($totals['attributed_revenue'], 2)],
                    ['label' => 'Commission expense', 'value' => '₱'.number_format($totals['commission_total'], 2)],
                ] as $card)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900"><p class="text-xs uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $card['value'] }}</p></div>
                @endforeach
            </section>
        @elseif($reportType === 'commissions')
            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900"><p class="text-xs uppercase tracking-wide text-gray-400">Commission records</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($totals['commissions']) }}</p></div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900"><p class="text-xs uppercase tracking-wide text-gray-400">Attributed revenue</p><p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($totals['attributed_revenue'], 2) }}</p></div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900"><p class="text-xs uppercase tracking-wide text-gray-400">Commission expense</p><p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">₱{{ number_format($totals['commission_total'], 2) }}</p></div>
            </section>
        @else
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['label' => 'Transactions', 'value' => number_format($totals['transactions'])],
                    ['label' => 'Gross billings', 'value' => '₱'.number_format($totals['gross'], 2)],
                    ['label' => 'Net SaaS revenue', 'value' => '₱'.number_format($totals['net_revenue'], 2)],
                    ['label' => 'Refunds', 'value' => '₱'.number_format($totals['refunds'], 2)],
                ] as $card)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900"><p class="text-xs uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $card['value'] }}</p></div>
                @endforeach
            </section>
        @endif

        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-white/10">
                <h2 class="font-semibold text-gray-950 dark:text-white">{{ $reportNames[$reportType] }} report</h2>
                <p class="mt-1 text-xs text-gray-400">The report and CSV export respect the selected filters.</p>
            </div>
            <div class="overflow-x-auto">
                @if($reportType === 'agents')
                    <table class="w-full min-w-[950px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-400 dark:bg-white/[0.025]"><tr><th class="px-6 py-3">Agent</th><th class="px-4 py-3">Program</th><th class="px-4 py-3 text-right">Referrals</th><th class="px-4 py-3 text-right">Conversions</th><th class="px-4 py-3 text-right">Conversion</th><th class="px-4 py-3 text-right">Attributed revenue</th><th class="px-6 py-3 text-right">Commission</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @forelse($report['rows'] as $row)
                                <tr class="hover:bg-amber-50/50 dark:hover:bg-white/[0.025]"><td class="px-6 py-4"><p class="font-semibold text-gray-900 dark:text-white">{{ $row['name'] }}</p><p class="mt-1 text-xs text-gray-400">{{ ucfirst($row['status']) }}</p></td><td class="px-4 py-4">{{ $row['program'] }}</td><td class="px-4 py-4 text-right">{{ number_format($row['referrals']) }}</td><td class="px-4 py-4 text-right">{{ number_format($row['conversions']) }}</td><td class="px-4 py-4 text-right">{{ number_format($row['conversion_rate'], 1) }}%</td><td class="px-4 py-4 text-right font-semibold">₱{{ number_format($row['attributed_revenue'], 2) }}</td><td class="px-6 py-4 text-right text-amber-600">₱{{ number_format($row['commissions'], 2) }}</td></tr>
                            @empty<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No agent activity matches these filters.</td></tr>@endforelse
                        </tbody>
                    </table>
                @elseif($reportType === 'commissions')
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-400 dark:bg-white/[0.025]"><tr><th class="px-6 py-3">Earned</th><th class="px-4 py-3">Agent</th><th class="px-4 py-3">Subscriber</th><th class="px-4 py-3 text-right">Attributed revenue</th><th class="px-4 py-3 text-right">Rate</th><th class="px-4 py-3 text-right">Commission</th><th class="px-6 py-3">Status</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @forelse($report['rows'] as $commission)
                                <tr class="hover:bg-amber-50/50 dark:hover:bg-white/[0.025]"><td class="px-6 py-4"><p class="font-semibold text-gray-900 dark:text-white">{{ $commission->earned_at?->format('M j, Y') }}</p><p class="mt-1 text-xs text-gray-400">SUB-{{ $commission->subscription_id }}</p></td><td class="px-4 py-4">{{ $commission->agent?->user?->name ?? $commission->agent?->user?->email ?? '—' }}</td><td class="px-4 py-4">{{ $commission->company?->name ?? '—' }}</td><td class="px-4 py-4 text-right">₱{{ number_format($commission->commissionable_amount, 2) }}</td><td class="px-4 py-4 text-right">{{ number_format($commission->commission_rate, 2) }}%</td><td class="px-4 py-4 text-right font-semibold text-amber-600">₱{{ number_format($commission->commission_amount, 2) }}</td><td class="px-6 py-4"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">{{ ucfirst($commission->status) }}</span></td></tr>
                            @empty<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No commissions match these filters.</td></tr>@endforelse
                        </tbody>
                    </table>
                @else
                    <table class="w-full min-w-[1150px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-400 dark:bg-white/[0.025]"><tr><th class="px-6 py-3">Paid / subscriber</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3">Cycle</th><th class="px-4 py-3 text-right">Gross</th><th class="px-4 py-3 text-right">Net</th><th class="px-4 py-3 text-right">Refund</th><th class="px-4 py-3 text-right">Gateway fee</th><th class="px-6 py-3">Status</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @forelse($report['rows'] as $subscription)
                                <tr class="hover:bg-amber-50/50 dark:hover:bg-white/[0.025]"><td class="px-6 py-4"><p class="font-semibold text-gray-900 dark:text-white">{{ $subscription->company?->name ?? '—' }}</p><p class="mt-1 text-xs text-gray-400">{{ $subscription->paid_at?->format('M j, Y g:i A') }}</p></td><td class="px-4 py-4 font-medium">{{ $subscription->plan?->name ?? '—' }}</td><td class="px-4 py-4 text-gray-500">{{ str($subscription->planPrice?->billing_cycle)->replace('_', ' ')->headline() }}</td><td class="px-4 py-4 text-right">₱{{ number_format($subscription->total_due, 2) }}</td><td class="px-4 py-4 text-right font-semibold text-emerald-600">₱{{ number_format((float) $subscription->net_amount - (float) $subscription->refund_amount, 2) }}</td><td class="px-4 py-4 text-right text-rose-600">₱{{ number_format($subscription->refund_amount, 2) }}</td><td class="px-4 py-4 text-right">₱{{ number_format($subscription->paymongo_fee, 2) }}</td><td class="px-6 py-4"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">{{ $this->subscriptionState($subscription) }}</span></td></tr>
                            @empty<tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">No subscription transactions match these filters.</td></tr>@endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
