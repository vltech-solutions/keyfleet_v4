<x-filament-panels::page>
    @php
        $data = $this->analytics;
        $summary = $data['summary'];
        $recurring = $data['recurring'];
        $agent = $data['agent'];
        $trend = $data['trend'];
        $changeText = function (?float $change): string {
            if ($change === null) {
                return 'No comparable prior value';
            }

            return ($change >= 0 ? '+' : '').number_format($change, 1).'% vs previous period';
        };
        $cards = [
            [
                'label' => 'Net SaaS revenue',
                'value' => '₱'.number_format($summary['net_revenue'], 2),
                'detail' => $changeText($summary['revenue_change']),
                'positive' => ($summary['revenue_change'] ?? 0) >= 0,
                'icon' => 'heroicon-o-banknotes',
                'url' => $this->reportUrl('revenue'),
            ],
            [
                'label' => 'Plan MRR',
                'value' => '₱'.number_format($recurring['mrr'], 2),
                'detail' => 'Billing cycles normalized monthly',
                'positive' => true,
                'icon' => 'heroicon-o-arrow-path-rounded-square',
                'url' => $this->reportUrl('subscriptions', ['subscriptionStatus' => 'active']),
            ],
            [
                'label' => 'ARR run rate',
                'value' => '₱'.number_format($recurring['arr'], 2),
                'detail' => 'MRR × 12',
                'positive' => true,
                'icon' => 'heroicon-o-chart-bar-square',
                'url' => $this->reportUrl('subscriptions', ['subscriptionStatus' => 'active']),
            ],
            [
                'label' => 'Active paid subscribers',
                'value' => number_format($recurring['active_paid_subscribers']),
                'detail' => number_format($recurring['expiring_soon']).' expiring within 30 days',
                'positive' => $recurring['expiring_soon'] === 0,
                'icon' => 'heroicon-o-building-office-2',
                'url' => $this->reportUrl('subscriptions', ['subscriptionStatus' => 'active']),
            ],
            [
                'label' => 'New paid subscriptions',
                'value' => number_format($summary['new_subscriptions']),
                'detail' => $changeText($summary['subscriber_change']),
                'positive' => ($summary['subscriber_change'] ?? 0) >= 0,
                'icon' => 'heroicon-o-user-plus',
                'url' => $this->reportUrl('subscriptions'),
            ],
            [
                'label' => 'Agent commission expense',
                'value' => '₱'.number_format($agent['commission_expense'], 2),
                'detail' => number_format($agent['conversions']).' attributed conversion(s)',
                'positive' => true,
                'icon' => 'heroicon-o-users',
                'url' => $this->reportUrl('commissions'),
            ],
        ];
    @endphp

    <div class="relative space-y-6">
        <div wire:loading.flex wire:target="period,customStart,customEnd" class="absolute inset-0 z-50 items-start justify-center rounded-3xl bg-white/60 pt-40 backdrop-blur-[1px] dark:bg-gray-950/60">
            <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-lg dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
                <x-filament::loading-indicator class="h-5 w-5" />
                Refreshing SaaS metrics…
            </div>
        </div>

        <header class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-6 dark:border-white/10 sm:px-7">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-400">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            KeyFleet business intelligence
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">SaaS performance dashboard</h1>
                        <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Subscription revenue, recurring growth, plan performance, referrals, commissions, and agent payouts.
                        </p>
                    </div>
                    <a href="{{ $this->reportUrl() }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-950 px-4 py-2.5 text-sm font-semibold text-white transition duration-200 hover:bg-gray-800 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200">
                        <x-filament::icon icon="heroicon-o-document-chart-bar" class="h-4 w-4" />
                        Open SaaS reports
                    </a>
                </div>
            </div>

            <div class="grid gap-4 bg-gray-50/70 px-5 py-5 dark:bg-white/[0.025] sm:grid-cols-2 lg:grid-cols-4 sm:px-7">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date range</span>
                    <select wire:model.live="period" class="w-full rounded-xl border-gray-300 bg-white text-sm text-gray-800 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
                        @foreach(\App\Services\AdminAnalyticsService::PERIODS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                @if($period === 'custom')
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</span>
                        <input type="date" wire:model.live="customStart" class="w-full rounded-xl border-gray-300 bg-white text-sm text-gray-800 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100" />
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</span>
                        <input type="date" wire:model.live="customEnd" class="w-full rounded-xl border-gray-300 bg-white text-sm text-gray-800 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100" />
                    </label>
                @else
                    <div class="flex items-end sm:col-span-1 lg:col-span-2">
                        <div class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 dark:border-white/10 dark:bg-gray-900">
                            <span class="block text-xs text-gray-400">Reporting period</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $data['range']['label'] }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach($cards as $card)
                <a href="{{ $card['url'] }}" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md dark:border-white/10 dark:bg-gray-900 dark:hover:border-amber-500/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <p class="mt-3 truncate text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $card['value'] }}</p>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 transition group-hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400">
                            <x-filament::icon :icon="$card['icon']" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="mt-4 text-xs {{ $card['positive'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $card['detail'] }}</p>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-12">
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 sm:p-6 xl:col-span-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">SaaS revenue performance</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gross subscription billings, net revenue after refunds, and earned agent commissions.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-indigo-500"></span>Gross</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>Net</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-amber-500"></span>Commission</span>
                    </div>
                </div>

                @if(count($trend['items']))
                    <div class="mt-7 overflow-x-auto pb-2">
                        <div class="flex h-72 min-w-[680px] items-end gap-2 border-b border-gray-200 px-2 dark:border-white/10">
                            @foreach($trend['items'] as $point)
                                @php
                                    $grossHeight = max($point['gross'] > 0 ? 4 : 0, ($point['gross'] / $trend['max']) * 100);
                                    $netHeight = max($point['net'] > 0 ? 4 : 0, ($point['net'] / $trend['max']) * 100);
                                    $commissionHeight = max($point['commissions'] > 0 ? 3 : 0, ($point['commissions'] / $trend['max']) * 100);
                                @endphp
                                <div class="group relative flex h-full min-w-[34px] flex-1 flex-col justify-end">
                                    <div class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 hidden w-56 -translate-x-1/2 rounded-xl border border-gray-200 bg-white p-3 text-xs shadow-xl group-hover:block dark:border-white/10 dark:bg-gray-800">
                                        <p class="font-semibold text-gray-950 dark:text-white">{{ $point['label'] }}</p>
                                        <div class="mt-2 space-y-1 text-gray-500 dark:text-gray-400">
                                            <div class="flex justify-between gap-4"><span>Gross billings</span><strong class="text-gray-900 dark:text-white">₱{{ number_format($point['gross'], 2) }}</strong></div>
                                            <div class="flex justify-between gap-4"><span>Net revenue</span><strong class="text-emerald-600 dark:text-emerald-400">₱{{ number_format($point['net'], 2) }}</strong></div>
                                            <div class="flex justify-between gap-4"><span>Commission</span><strong class="text-amber-600 dark:text-amber-400">₱{{ number_format($point['commissions'], 2) }}</strong></div>
                                            <div class="flex justify-between gap-4"><span>Transactions</span><strong class="text-gray-900 dark:text-white">{{ number_format($point['transactions']) }}</strong></div>
                                        </div>
                                    </div>
                                    <div class="flex h-[230px] items-end justify-center gap-1">
                                        <div class="w-2.5 rounded-t bg-indigo-500 transition duration-200 group-hover:bg-indigo-400" style="height: {{ $grossHeight }}%"></div>
                                        <div class="w-2.5 rounded-t bg-emerald-500 transition duration-200 group-hover:bg-emerald-400" style="height: {{ $netHeight }}%"></div>
                                        <div class="w-2.5 rounded-t bg-amber-500 transition duration-200 group-hover:bg-amber-400" style="height: {{ $commissionHeight }}%"></div>
                                    </div>
                                    <span class="mt-2 truncate text-center text-[10px] font-medium text-gray-400">{{ $point['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-8 flex min-h-64 flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 text-center dark:border-white/10 dark:bg-white/[0.025]">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="h-9 w-9 text-gray-300 dark:text-gray-600" />
                        <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">No subscription revenue recorded for this period</p>
                        <p class="mt-1 text-xs text-gray-400">Try another date range.</p>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-gray-200 bg-gray-950 p-6 text-white shadow-sm dark:border-white/10 xl:col-span-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400">Revenue quality</p>
                <h2 class="mt-2 text-lg font-semibold">Recorded subscription economics</h2>
                <div class="mt-6 divide-y divide-white/10">
                    <div class="flex items-center justify-between py-3"><span class="text-sm text-gray-400">Gross billings</span><strong>₱{{ number_format($summary['gross_billings'], 2) }}</strong></div>
                    <div class="flex items-center justify-between py-3"><span class="text-sm text-gray-400">Net SaaS revenue</span><strong class="text-emerald-400">₱{{ number_format($summary['net_revenue'], 2) }}</strong></div>
                    <div class="flex items-center justify-between py-3"><span class="text-sm text-gray-400">Refunds</span><strong class="text-rose-400">₱{{ number_format($summary['refunds'], 2) }}</strong></div>
                    <div class="flex items-center justify-between py-3"><span class="text-sm text-gray-400">Gateway fees</span><strong>₱{{ number_format($summary['gateway_fees'], 2) }}</strong></div>
                    <div class="flex items-center justify-between py-3"><span class="text-sm text-gray-400">Discounts granted</span><strong>₱{{ number_format($summary['discounts'], 2) }}</strong></div>
                    <div class="flex items-center justify-between py-3"><span class="text-sm text-gray-400">Average net / transaction</span><strong>₱{{ number_format($summary['average_revenue_per_transaction'], 2) }}</strong></div>
                </div>
                <p class="mt-4 text-xs leading-5 text-gray-400">Net revenue uses <code>net_amount − refund_amount</code>. It is not mixed with rental payments made to KeyFleet customers.</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">Subscriber movement</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Paid acquisition, renewal, expiry, and trial signals.</p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">ARPU ₱{{ number_format($recurring['arpu'], 2) }}</span>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach([
                        ['label' => 'New paid', 'value' => $summary['new_subscriptions'], 'class' => 'text-emerald-600 dark:text-emerald-400'],
                        ['label' => 'Renewals', 'value' => $summary['renewals'], 'class' => 'text-indigo-600 dark:text-indigo-400'],
                        ['label' => 'Churned', 'value' => $summary['churned'], 'class' => 'text-rose-600 dark:text-rose-400'],
                        ['label' => 'Active paid', 'value' => $recurring['active_paid_subscribers'], 'class' => 'text-gray-950 dark:text-white'],
                        ['label' => 'Active trials', 'value' => $recurring['active_trials'], 'class' => 'text-sky-600 dark:text-sky-400'],
                        ['label' => 'Expiring soon', 'value' => $recurring['expiring_soon'], 'class' => 'text-amber-600 dark:text-amber-400'],
                    ] as $item)
                        <div class="rounded-2xl bg-gray-50 p-4 dark:bg-white/[0.04]">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold {{ $item['class'] }}">{{ number_format($item['value']) }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs leading-5 text-gray-400">Churn counts subscriptions ending in the period with no later subscription for the same customer.</p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">Agent channel economics</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Subscription revenue attributed to the agent referral program.</p>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach([
                        ['label' => 'Active agents', 'value' => number_format($agent['active_agents']), 'money' => false],
                        ['label' => 'Referrals', 'value' => number_format($agent['referrals']), 'money' => false],
                        ['label' => 'Conversions', 'value' => number_format($agent['conversions']), 'money' => false],
                        ['label' => 'Attributed revenue', 'value' => number_format($agent['attributed_revenue'], 2), 'money' => true],
                        ['label' => 'Commission expense', 'value' => number_format($agent['commission_expense'], 2), 'money' => true],
                        ['label' => 'After commission', 'value' => number_format($agent['net_after_commissions'], 2), 'money' => true],
                    ] as $item)
                        <div class="rounded-2xl border border-gray-100 p-4 dark:border-white/10">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                            <p class="mt-2 text-xl font-bold text-gray-950 dark:text-white">{{ $item['money'] ? '₱' : '' }}{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center justify-between rounded-2xl bg-amber-50 px-4 py-3 dark:bg-amber-500/[0.08]">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Referral conversion rate</span>
                    <strong class="text-amber-700 dark:text-amber-400">{{ number_format($agent['conversion_rate'], 1) }}%</strong>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-12">
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-7">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-white/10">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">Revenue by plan</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Paid subscription transactions in the selected period.</p>
                    </div>
                    <a href="{{ $this->reportUrl('subscriptions') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-600 dark:text-amber-400">Full report →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="bg-gray-50/80 text-xs uppercase tracking-wide text-gray-400 dark:bg-white/[0.025]">
                            <tr><th class="px-6 py-3">Plan</th><th class="px-4 py-3 text-right">Transactions</th><th class="px-4 py-3 text-right">Customers</th><th class="px-4 py-3 text-right">Gross</th><th class="px-6 py-3 text-right">Net</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @forelse($data['plan_revenue'] as $plan)
                                <tr class="transition duration-150 hover:bg-amber-50/50 dark:hover:bg-white/[0.025]">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $plan['name'] }}</td>
                                    <td class="px-4 py-4 text-right text-gray-500 dark:text-gray-400">{{ number_format($plan['transactions']) }}</td>
                                    <td class="px-4 py-4 text-right text-gray-500 dark:text-gray-400">{{ number_format($plan['customers']) }}</td>
                                    <td class="px-4 py-4 text-right text-gray-900 dark:text-white">₱{{ number_format($plan['gross'], 2) }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($plan['net'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">No paid subscriptions in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-5">
                <h2 class="font-semibold text-gray-950 dark:text-white">Active plan mix</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current subscribers by plan, including free plans.</p>
                @php $planTotal = max(1, $data['plan_mix']->sum('subscribers')); @endphp
                <div class="mt-6 space-y-4">
                    @forelse($data['plan_mix'] as $plan)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $plan['name'] }}</span>
                                <span class="text-xs text-gray-400">{{ number_format($plan['subscribers']) }} subscriber(s)</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-full rounded-full bg-indigo-500" style="width: {{ ($plan['subscribers'] / $planTotal) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 px-5 py-12 text-center text-sm text-gray-400 dark:border-white/10">No active subscriptions.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-white/10">
                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">Top agent performance</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ranked by agent-attributed commissionable subscription revenue.</p>
                </div>
                <a href="{{ $this->reportUrl('agents') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-600 dark:text-amber-400">Full report →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-gray-50/80 text-xs uppercase tracking-wide text-gray-400 dark:bg-white/[0.025]">
                        <tr><th class="px-6 py-3">Agent</th><th class="px-4 py-3">Program</th><th class="px-4 py-3 text-right">Referrals</th><th class="px-4 py-3 text-right">Conversions</th><th class="px-4 py-3 text-right">Attributed revenue</th><th class="px-6 py-3 text-right">Commission</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse($data['top_agents'] as $row)
                            <tr class="transition duration-150 hover:bg-amber-50/50 dark:hover:bg-white/[0.025]">
                                <td class="px-6 py-4"><p class="font-semibold text-gray-900 dark:text-white">{{ $row['name'] }}</p><p class="mt-1 text-xs text-gray-400">{{ ucfirst($row['status']) }} · {{ number_format($row['conversion_rate'], 1) }}% conversion</p></td>
                                <td class="px-4 py-4 text-gray-500 dark:text-gray-400">{{ $row['program'] }}</td>
                                <td class="px-4 py-4 text-right">{{ number_format($row['referrals']) }}</td>
                                <td class="px-4 py-4 text-right">{{ number_format($row['conversions']) }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-gray-900 dark:text-white">₱{{ number_format($row['attributed_revenue'], 2) }}</td>
                                <td class="px-6 py-4 text-right text-amber-600 dark:text-amber-400">₱{{ number_format($row['commissions'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No agent-attributed revenue in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-4">
            @php
                $statusMeta = [
                    'pending' => ['label' => 'Holding', 'class' => 'text-sky-600 dark:text-sky-400', 'bg' => 'bg-sky-500'],
                    'payable' => ['label' => 'Payable', 'class' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-500'],
                    'paid' => ['label' => 'Paid', 'class' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-500'],
                    'reversed' => ['label' => 'Reversed', 'class' => 'text-rose-600 dark:text-rose-400', 'bg' => 'bg-rose-500'],
                ];
            @endphp
            @foreach($statusMeta as $status => $meta)
                @php $record = $data['commission_statuses']->get($status); @endphp
                <a href="{{ $this->reportUrl('commissions', ['commissionStatus' => $status]) }}" class="rounded-2xl border border-gray-200 bg-white p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-center justify-between"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $meta['label'] }} commissions</p><span class="h-2.5 w-2.5 rounded-full {{ $meta['bg'] }}"></span></div>
                    <p class="mt-3 text-2xl font-bold {{ $meta['class'] }}">₱{{ number_format($record?->amount ?? 0, 2) }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ number_format($record?->total ?? 0) }} record(s)</p>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-12">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-5">
                <h2 class="font-semibold text-gray-950 dark:text-white">Recent SaaS activity</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Subscription, commission, and payout events.</p>
                <div class="mt-5 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($data['recent_activity'] as $activity)
                        <div class="flex gap-3 py-3.5 first:pt-0">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $activity['type'] === 'subscription' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($activity['type'] === 'reversed' ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400') }}">
                                <x-filament::icon :icon="$activity['type'] === 'subscription' ? 'heroicon-o-credit-card' : ($activity['type'] === 'payout' ? 'heroicon-o-banknotes' : 'heroicon-o-user-group')" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between gap-3"><p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $activity['title'] }}</p><span class="shrink-0 text-xs font-semibold text-gray-700 dark:text-gray-200">₱{{ number_format($activity['amount'], 2) }}</span></div>
                                <p class="mt-1 truncate text-xs text-gray-400">{{ $activity['detail'] }} · {{ $activity['at']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-gray-400">No recent SaaS activity.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-3">
                <h2 class="font-semibold text-gray-950 dark:text-white">Needs attention</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revenue and payout signals backed by records.</p>
                <div class="mt-5 space-y-3">
                    @forelse($data['attention'] as $item)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-500/20 dark:bg-amber-500/[0.06]">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</p>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400"><span>{{ $item['detail'] }}</span>@if($item['amount'] !== null)<strong class="text-amber-700 dark:text-amber-400">₱{{ number_format($item['amount'], 2) }}</strong>@endif</div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-10 text-center dark:border-white/10"><x-filament::icon icon="heroicon-o-check-circle" class="mx-auto h-7 w-7 text-emerald-500" /><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No supported attention signals found.</p></div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-4">
                <h2 class="font-semibold text-gray-950 dark:text-white">Management insights</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Neutral summaries generated from recorded data.</p>
                <ol class="mt-5 space-y-4">
                    @foreach($data['insights'] as $insight)
                        <li class="flex gap-3 text-sm leading-6 text-gray-600 dark:text-gray-300"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span><span>{{ $insight }}</span></li>
                    @endforeach
                </ol>
                <div class="mt-6 rounded-2xl bg-gray-950 p-4 text-white dark:bg-white/[0.06]">
                    <div class="flex items-center justify-between"><span class="text-xs text-gray-400">Paid commissions this period</span><strong>₱{{ number_format($agent['paid_commissions'], 2) }}</strong></div>
                    <div class="mt-3 flex items-center justify-between"><span class="text-xs text-gray-400">Payout pipeline</span><strong class="text-amber-400">₱{{ number_format($agent['payout_pipeline'], 2) }}</strong></div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
