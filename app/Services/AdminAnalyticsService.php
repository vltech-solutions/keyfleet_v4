<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\AgentPayout;
use App\Models\CompanyReferral;
use App\Models\Subscription;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    public const PERIODS = [
        'today' => 'Today',
        'last_7_days' => 'Last 7 Days',
        'last_30_days' => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_quarter' => 'This Quarter',
        'this_year' => 'This Year',
        'custom' => 'Custom Range',
    ];

    /**
     * SaaS revenue definitions:
     * - Gross billings: subscription.total_due for paid subscriptions.
     * - Net SaaS revenue: subscription.net_amount less recorded refund_amount.
     * - Plan MRR: active paid plan price normalized by billing-cycle months.
     * - Commission expense: non-reversed agent commissions earned in the period.
     * Tenant rental bookings/payments are intentionally excluded.
     */
    public function dashboard(string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $range = $this->resolveRange($period, $customStart, $customEnd);
        $previous = $this->previousRange($range['start'], $range['end'], $period);
        $current = $this->periodSummary($range['start'], $range['end']);
        $prior = $this->periodSummary($previous['start'], $previous['end']);
        $recurring = $this->recurringRevenue();
        $agent = $this->agentSummary($range['start'], $range['end']);

        $current['revenue_change'] = $this->percentageChange($current['net_revenue'], $prior['net_revenue']);
        $current['subscriber_change'] = $this->percentageChange($current['new_subscriptions'], $prior['new_subscriptions']);

        return [
            'range' => $range,
            'previous_range' => $previous,
            'summary' => $current,
            'previous' => $prior,
            'recurring' => $recurring,
            'agent' => $agent,
            'trend' => $this->revenueTrend($range['start'], $range['end']),
            'plan_mix' => $this->planMix(),
            'plan_revenue' => $this->planRevenue($range['start'], $range['end']),
            'top_agents' => $this->agentPerformance($range['start'], $range['end'], 6),
            'commission_statuses' => $this->commissionStatuses(),
            'recent_activity' => $this->recentActivity(),
            'attention' => $this->attentionItems($range['start'], $range['end']),
            'insights' => $this->managementInsights($current, $prior, $recurring, $agent),
        ];
    }

    public function resolveRange(string $period, ?string $customStart = null, ?string $customEnd = null): array
    {
        $today = now();
        [$start, $end] = match ($period) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'last_7_days' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            'last_30_days' => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_quarter' => [$today->copy()->startOfQuarter(), $today->copy()->endOfQuarter()],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'custom' => [
                $customStart ? Carbon::parse($customStart)->startOfDay() : $today->copy()->startOfMonth(),
                $customEnd ? Carbon::parse($customEnd)->endOfDay() : $today->copy()->endOfDay(),
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $start->isSameDay($end)
                ? $start->format('M j, Y')
                : $start->format('M j, Y').' – '.$end->format('M j, Y'),
        ];
    }

    public function previousRange(CarbonInterface $start, CarbonInterface $end, ?string $period = null): array
    {
        [$previousStart, $previousEnd] = match ($period) {
            'today' => [$start->copy()->subDay()->startOfDay(), $start->copy()->subDay()->endOfDay()],
            'this_month', 'last_month' => [
                $start->copy()->subMonthNoOverflow()->startOfMonth(),
                $start->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'this_quarter' => [
                $start->copy()->subQuarter()->startOfQuarter(),
                $start->copy()->subQuarter()->endOfQuarter(),
            ],
            'this_year' => [
                $start->copy()->subYear()->startOfYear(),
                $start->copy()->subYear()->endOfYear(),
            ],
            default => $this->precedingEqualRange($start, $end),
        };

        return [
            'start' => $previousStart,
            'end' => $previousEnd,
            'label' => $previousStart->format('M j, Y').' – '.$previousEnd->format('M j, Y'),
        ];
    }

    protected function precedingEqualRange(CarbonInterface $start, CarbonInterface $end): array
    {
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();

        return [$previousEnd->copy()->subDays($days - 1)->startOfDay(), $previousEnd];
    }

    public function subscriptionReport(CarbonInterface $start, CarbonInterface $end, ?int $planId = null, string $status = 'all'): array
    {
        $query = Subscription::query()
            ->with(['company:id,name', 'plan:id,name', 'planPrice:id,plan_id,billing_cycle,price'])
            ->whereBetween('paid_at', [$start, $end])
            ->when($planId, fn (Builder $builder) => $builder->where('plan_id', $planId))
            ->when($status === 'refunded', fn (Builder $builder) => $builder->where('refund_amount', '>', 0))
            ->when($status === 'active', fn (Builder $builder) => $builder->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today()))
            ->when($status === 'expired', fn (Builder $builder) => $builder->whereDate('ends_at', '<', today()));

        $totals = (clone $query)->selectRaw(
            'COUNT(*) transactions, COUNT(DISTINCT company_id) customers, '.
            'COALESCE(SUM(total_due), 0) gross, COALESCE(SUM(net_amount), 0) net_before_refunds, '.
            'COALESCE(SUM(refund_amount), 0) refunds, COALESCE(SUM(paymongo_fee), 0) gateway_fees, '.
            'COALESCE(SUM(discount_amount), 0) discounts'
        )->first();

        return [
            'rows' => $query->latest('paid_at')->limit(500)->get(),
            'totals' => [
                'transactions' => (int) ($totals->transactions ?? 0),
                'customers' => (int) ($totals->customers ?? 0),
                'gross' => (float) ($totals->gross ?? 0),
                'net_revenue' => (float) ($totals->net_before_refunds ?? 0) - (float) ($totals->refunds ?? 0),
                'refunds' => (float) ($totals->refunds ?? 0),
                'gateway_fees' => (float) ($totals->gateway_fees ?? 0),
                'discounts' => (float) ($totals->discounts ?? 0),
            ],
        ];
    }

    public function agentPerformance(CarbonInterface $start, CarbonInterface $end, ?int $limit = null): Collection
    {
        $commissionMetric = function ($query, string $select) use ($start, $end) {
            $query->from('agent_commissions')->selectRaw($select)
                ->whereColumn('agent_commissions.agent_id', 'agents.id')
                ->whereBetween('agent_commissions.earned_at', [$start, $end])
                ->where('agent_commissions.status', '!=', AgentCommission::STATUS_REVERSED);
        };
        $referralMetric = function ($query, string $select, string $dateColumn) use ($start, $end) {
            $query->from('company_referrals')->selectRaw($select)
                ->whereColumn('company_referrals.agent_id', 'agents.id')
                ->where('company_referrals.program_type', CompanyReferral::PROGRAM_AGENT)
                ->whereBetween($dateColumn, [$start, $end]);
        };

        $query = Agent::query()
            ->select(['agents.id', 'agents.user_id', 'agents.agent_program_id', 'agents.referral_code', 'agents.status'])
            ->with(['user:id,name,email', 'program:id,name'])
            ->selectSub(fn ($q) => $commissionMetric($q, 'COALESCE(SUM(commission_amount), 0)'), 'commission_total')
            ->selectSub(fn ($q) => $commissionMetric($q, 'COALESCE(SUM(commissionable_amount), 0)'), 'attributed_revenue')
            ->selectSub(fn ($q) => $commissionMetric($q, 'COUNT(*)'), 'commission_count')
            ->selectSub(fn ($q) => $referralMetric($q, 'COUNT(*)', 'company_referrals.referred_at'), 'referral_count')
            ->selectSub(fn ($q) => $referralMetric($q, 'COUNT(*)', 'company_referrals.qualified_at'), 'conversion_count')
            ->orderByDesc('attributed_revenue');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(fn (Agent $agent) => [
            'id' => $agent->id,
            'name' => $agent->user?->name ?? $agent->user?->email ?? 'Agent #'.$agent->id,
            'program' => $agent->program?->name ?? '—',
            'status' => $agent->status,
            'referrals' => (int) $agent->referral_count,
            'conversions' => (int) $agent->conversion_count,
            'attributed_revenue' => (float) $agent->attributed_revenue,
            'commissions' => (float) $agent->commission_total,
            'commission_count' => (int) $agent->commission_count,
            'conversion_rate' => (int) $agent->referral_count > 0
                ? ((int) $agent->conversion_count / (int) $agent->referral_count) * 100
                : 0,
        ]);
    }

    public function commissionReport(CarbonInterface $start, CarbonInterface $end, ?int $agentId = null, string $status = 'all'): array
    {
        $query = AgentCommission::query()
            ->with(['agent.user:id,name,email', 'company:id,name', 'subscription:id,company_id,total_due,net_amount,paid_at'])
            ->whereBetween('earned_at', [$start, $end])
            ->when($agentId, fn (Builder $builder) => $builder->where('agent_id', $agentId))
            ->when($status !== 'all', fn (Builder $builder) => $builder->where('status', $status));

        $totals = (clone $query)->selectRaw(
            'COUNT(*) commissions, COALESCE(SUM(commissionable_amount), 0) attributed_revenue, '.
            'COALESCE(SUM(CASE WHEN status != ? THEN commission_amount ELSE 0 END), 0) commission_total',
            [AgentCommission::STATUS_REVERSED],
        )->first();

        return [
            'rows' => $query->latest('earned_at')->limit(500)->get(),
            'totals' => [
                'commissions' => (int) ($totals->commissions ?? 0),
                'attributed_revenue' => (float) ($totals->attributed_revenue ?? 0),
                'commission_total' => (float) ($totals->commission_total ?? 0),
            ],
        ];
    }

    protected function periodSummary(CarbonInterface $start, CarbonInterface $end): array
    {
        $paid = Subscription::query()->whereNotNull('paid_at')->whereBetween('paid_at', [$start, $end]);
        $aggregate = (clone $paid)->selectRaw(
            'COUNT(*) transactions, COUNT(DISTINCT company_id) customers, '.
            'COALESCE(SUM(total_due), 0) gross, COALESCE(SUM(net_amount), 0) net_before_refunds, '.
            'COALESCE(SUM(refund_amount), 0) refunds, COALESCE(SUM(paymongo_fee), 0) gateway_fees, '.
            'COALESCE(SUM(discount_amount), 0) discounts, COALESCE(SUM(processing_fee), 0) processing_fees'
        )->first();

        $newSubscriptions = (clone $paid)->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('subscriptions as prior')
                ->whereColumn('prior.company_id', 'subscriptions.company_id')
                ->whereNotNull('prior.paid_at')
                ->whereColumn('prior.paid_at', '<', 'subscriptions.paid_at');
        })->count();

        $renewals = (clone $paid)->whereExists(function ($query) {
            $query->selectRaw('1')->from('subscriptions as prior')
                ->whereColumn('prior.company_id', 'subscriptions.company_id')
                ->whereNotNull('prior.paid_at')
                ->whereColumn('prior.paid_at', '<', 'subscriptions.paid_at');
        })->count();

        $churned = Subscription::query()
            ->whereBetween('ends_at', [$start, $end])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('subscriptions as later')
                    ->whereColumn('later.company_id', 'subscriptions.company_id')
                    ->whereColumn('later.starts_at', '>', 'subscriptions.starts_at');
            })
            ->distinct('company_id')->count('company_id');

        $netBeforeRefunds = (float) ($aggregate->net_before_refunds ?? 0);
        $refunds = (float) ($aggregate->refunds ?? 0);

        return [
            'gross_billings' => (float) ($aggregate->gross ?? 0),
            'net_revenue' => $netBeforeRefunds - $refunds,
            'refunds' => $refunds,
            'gateway_fees' => (float) ($aggregate->gateway_fees ?? 0),
            'processing_fees' => (float) ($aggregate->processing_fees ?? 0),
            'discounts' => (float) ($aggregate->discounts ?? 0),
            'transactions' => (int) ($aggregate->transactions ?? 0),
            'paying_customers' => (int) ($aggregate->customers ?? 0),
            'new_subscriptions' => $newSubscriptions,
            'renewals' => $renewals,
            'churned' => $churned,
            'average_revenue_per_transaction' => (int) ($aggregate->transactions ?? 0) > 0
                ? ($netBeforeRefunds - $refunds) / (int) $aggregate->transactions
                : 0,
        ];
    }

    protected function recurringRevenue(): array
    {
        $active = Subscription::query()
            ->join('plan_prices', 'subscriptions.plan_price_id', '=', 'plan_prices.id')
            ->whereDate('subscriptions.starts_at', '<=', today())
            ->whereDate('subscriptions.ends_at', '>=', today());

        $mrrExpression = "CASE plan_prices.billing_cycle
            WHEN 'monthly' THEN plan_prices.price
            WHEN '3months' THEN plan_prices.price / 3
            WHEN '3_months' THEN plan_prices.price / 3
            WHEN '6months' THEN plan_prices.price / 6
            WHEN '6_months' THEN plan_prices.price / 6
            WHEN 'annually' THEN plan_prices.price / 12
            WHEN 'annual' THEN plan_prices.price / 12
            WHEN '2years' THEN plan_prices.price / 24
            ELSE 0 END";

        $mrr = (float) (clone $active)->where('plan_prices.price', '>', 0)->sum(DB::raw($mrrExpression));
        $paidCustomers = (clone $active)->where('plan_prices.price', '>', 0)->distinct('subscriptions.company_id')->count('subscriptions.company_id');
        $trials = (clone $active)->where('plan_prices.price', '<=', 0)->distinct('subscriptions.company_id')->count('subscriptions.company_id');
        $expiring = (clone $active)->where('plan_prices.price', '>', 0)
            ->whereBetween('subscriptions.ends_at', [today(), today()->addDays(30)])
            ->distinct('subscriptions.company_id')->count('subscriptions.company_id');

        return [
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'active_paid_subscribers' => $paidCustomers,
            'active_trials' => $trials,
            'expiring_soon' => $expiring,
            'arpu' => $paidCustomers > 0 ? $mrr / $paidCustomers : 0,
        ];
    }

    protected function revenueTrend(CarbonInterface $start, CarbonInterface $end): array
    {
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $grain = $days <= 1 ? 'hour' : ($days > 100 ? 'month' : 'day');
        $subscriptionBucket = $this->dateBucketExpression('paid_at', $grain);
        $commissionBucket = $this->dateBucketExpression('earned_at', $grain);

        $revenue = Subscription::query()->whereNotNull('paid_at')->whereBetween('paid_at', [$start, $end])
            ->selectRaw("{$subscriptionBucket} bucket, SUM(total_due) gross, SUM(net_amount - refund_amount) net, COUNT(*) transactions")
            ->groupBy('bucket')->orderBy('bucket')->get()->keyBy('bucket');
        $commissions = AgentCommission::query()
            ->where('status', '!=', AgentCommission::STATUS_REVERSED)
            ->whereBetween('earned_at', [$start, $end])
            ->selectRaw("{$commissionBucket} bucket, SUM(commission_amount) total")
            ->groupBy('bucket')->orderBy('bucket')->pluck('total', 'bucket');

        $buckets = $revenue->keys()->merge($commissions->keys())->unique()->sort()->values();
        $items = $buckets->map(function ($bucket) use ($revenue, $commissions, $grain) {
            $date = match ($grain) {
                'hour' => Carbon::createFromFormat('Y-m-d H', $bucket),
                'month' => Carbon::createFromFormat('Y-m', $bucket)->startOfMonth(),
                default => Carbon::parse($bucket),
            };

            return [
                'bucket' => $bucket,
                'label' => match ($grain) {
                    'hour' => $date->format('g A'),
                    'month' => $date->format('M'),
                    default => $date->format('M j'),
                },
                'gross' => (float) ($revenue->get($bucket)?->gross ?? 0),
                'net' => (float) ($revenue->get($bucket)?->net ?? 0),
                'commissions' => (float) ($commissions->get($bucket) ?? 0),
                'transactions' => (int) ($revenue->get($bucket)?->transactions ?? 0),
            ];
        })->all();

        return [
            'grain' => $grain,
            'items' => $items,
            'max' => max(1, (float) collect($items)->max(fn ($item) => max($item['gross'], $item['net']))),
        ];
    }

    protected function planMix(): Collection
    {
        return Subscription::query()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->join('plan_prices', 'subscriptions.plan_price_id', '=', 'plan_prices.id')
            ->whereDate('subscriptions.starts_at', '<=', today())
            ->whereDate('subscriptions.ends_at', '>=', today())
            ->selectRaw('plans.id, plans.name, COUNT(DISTINCT subscriptions.company_id) subscribers, SUM(plan_prices.price > 0) paid_subscriptions')
            ->groupBy('plans.id', 'plans.name')->orderByDesc('subscribers')->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'subscribers' => (int) $row->subscribers,
                'paid_subscriptions' => (int) $row->paid_subscriptions,
            ]);
    }

    protected function planRevenue(CarbonInterface $start, CarbonInterface $end): Collection
    {
        return Subscription::query()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereNotNull('subscriptions.paid_at')
            ->whereBetween('subscriptions.paid_at', [$start, $end])
            ->selectRaw('plans.id, plans.name, COUNT(*) transactions, COUNT(DISTINCT subscriptions.company_id) customers, SUM(subscriptions.total_due) gross, SUM(subscriptions.net_amount - subscriptions.refund_amount) net')
            ->groupBy('plans.id', 'plans.name')->orderByDesc('net')->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'transactions' => (int) $row->transactions,
                'customers' => (int) $row->customers,
                'gross' => (float) $row->gross,
                'net' => (float) $row->net,
            ]);
    }

    protected function agentSummary(CarbonInterface $start, CarbonInterface $end): array
    {
        $earned = AgentCommission::query()
            ->whereBetween('earned_at', [$start, $end])
            ->where('status', '!=', AgentCommission::STATUS_REVERSED);
        $earnedAmount = (float) (clone $earned)->sum('commission_amount');
        $attributedRevenue = (float) (clone $earned)->sum('commissionable_amount');
        $referrals = CompanyReferral::query()->where('program_type', CompanyReferral::PROGRAM_AGENT)
            ->whereBetween('referred_at', [$start, $end])->count();
        $conversions = CompanyReferral::query()->where('program_type', CompanyReferral::PROGRAM_AGENT)
            ->whereBetween('qualified_at', [$start, $end])->count();

        return [
            'active_agents' => Agent::query()->active()->count(),
            'referrals' => $referrals,
            'conversions' => $conversions,
            'conversion_rate' => $referrals > 0 ? ($conversions / $referrals) * 100 : 0,
            'attributed_revenue' => $attributedRevenue,
            'commission_expense' => $earnedAmount,
            'net_after_commissions' => $attributedRevenue - $earnedAmount,
            'paid_commissions' => (float) AgentCommission::query()->where('status', AgentCommission::STATUS_PAID)
                ->whereBetween('paid_at', [$start, $end])->sum('commission_amount'),
            'reversed_commissions' => (float) AgentCommission::query()->where('status', AgentCommission::STATUS_REVERSED)
                ->whereBetween('reversed_at', [$start, $end])->sum('commission_amount'),
            'payouts_paid' => (float) AgentPayout::query()->where('status', AgentPayout::STATUS_PAID)
                ->whereBetween('paid_at', [$start, $end])->sum('amount'),
            'payout_pipeline' => (float) AgentPayout::query()
                ->whereIn('status', [AgentPayout::STATUS_DRAFT, AgentPayout::STATUS_PROCESSING])->sum('amount'),
        ];
    }

    protected function commissionStatuses(): Collection
    {
        return AgentCommission::query()
            ->selectRaw('status, COUNT(*) total, COALESCE(SUM(commission_amount), 0) amount')
            ->groupBy('status')->get()->keyBy('status');
    }

    protected function recentActivity(): Collection
    {
        $subscriptions = Subscription::query()->with(['company:id,name', 'plan:id,name'])
            ->whereNotNull('paid_at')->latest('paid_at')->limit(5)->get()
            ->map(fn (Subscription $subscription) => [
                'type' => 'subscription',
                'title' => 'Subscription payment received',
                'detail' => ($subscription->company?->name ?? 'Customer').' · '.($subscription->plan?->name ?? 'Plan'),
                'amount' => (float) $subscription->net_amount - (float) $subscription->refund_amount,
                'at' => $subscription->paid_at,
            ]);
        $commissions = AgentCommission::query()->with('agent.user:id,name,email')
            ->latest('earned_at')->limit(5)->get()
            ->map(fn (AgentCommission $commission) => [
                'type' => $commission->status === AgentCommission::STATUS_REVERSED ? 'reversed' : 'commission',
                'title' => $commission->status === AgentCommission::STATUS_REVERSED ? 'Commission reversed' : 'Agent commission earned',
                'detail' => $commission->agent?->user?->name ?? $commission->agent?->user?->email ?? 'Agent',
                'amount' => (float) $commission->commission_amount,
                'at' => $commission->reversed_at ?? $commission->earned_at,
            ]);
        $payouts = AgentPayout::query()->with('agent.user:id,name,email')
            ->where('status', AgentPayout::STATUS_PAID)->latest('paid_at')->limit(5)->get()
            ->map(fn (AgentPayout $payout) => [
                'type' => 'payout',
                'title' => 'Agent payout completed',
                'detail' => $payout->agent?->user?->name ?? $payout->reference_number,
                'amount' => (float) $payout->amount,
                'at' => $payout->paid_at,
            ]);

        return $subscriptions->merge($commissions)->merge($payouts)->filter(fn ($item) => $item['at'])
            ->sortByDesc('at')->take(10)->values();
    }

    protected function attentionItems(CarbonInterface $start, CarbonInterface $end): Collection
    {
        $items = collect();
        $expiring = Subscription::query()->with(['company:id,name', 'plan:id,name'])
            ->whereDate('starts_at', '<=', today())->whereBetween('ends_at', [today(), today()->addDays(30)])
            ->whereHas('planPrice', fn (Builder $query) => $query->where('price', '>', 0))
            ->orderBy('ends_at')->limit(4)->get();
        foreach ($expiring as $subscription) {
            $items->push([
                'type' => 'expiry',
                'title' => ($subscription->company?->name ?? 'Subscriber').' expires soon',
                'detail' => ($subscription->plan?->name ?? 'Plan').' · '.$subscription->ends_at->format('M j, Y'),
                'amount' => null,
            ]);
        }

        $payable = AgentCommission::query()->where('status', AgentCommission::STATUS_PAYABLE)
            ->whereDoesntHave('payoutItem')
            ->selectRaw('COUNT(*) total, COALESCE(SUM(commission_amount), 0) amount')->first();
        if ((int) ($payable->total ?? 0) > 0) {
            $items->push([
                'type' => 'payout',
                'title' => number_format($payable->total).' commission(s) ready for payout',
                'detail' => 'Not yet assigned to a payout',
                'amount' => (float) $payable->amount,
            ]);
        }

        $pastDue = AgentCommission::query()->where('status', AgentCommission::STATUS_PENDING)
            ->where('payable_at', '<=', now())->count();
        if ($pastDue > 0) {
            $items->push([
                'type' => 'commission',
                'title' => number_format($pastDue).' commission(s) passed their holding period',
                'detail' => 'Run payable reconciliation',
                'amount' => null,
            ]);
        }

        $refunds = (float) Subscription::query()->whereBetween('paid_at', [$start, $end])->sum('refund_amount');
        if ($refunds > 0) {
            $items->push([
                'type' => 'refund',
                'title' => 'Refunds recorded in this period',
                'detail' => 'Review related subscription and commission reversals',
                'amount' => $refunds,
            ]);
        }

        return $items->take(6)->values();
    }

    protected function managementInsights(array $current, array $previous, array $recurring, array $agent): array
    {
        $insights = [];
        $change = $this->percentageChange($current['net_revenue'], $previous['net_revenue']);
        if ($change !== null) {
            $insights[] = sprintf(
                'Net SaaS revenue %s %.1f%% compared with the previous equivalent period.',
                $change >= 0 ? 'increased' : 'decreased',
                abs($change),
            );
        }
        $insights[] = 'Plan MRR is ₱'.number_format($recurring['mrr'], 2).' across '.number_format($recurring['active_paid_subscribers']).' active paid subscriber(s).';
        $insights[] = number_format($current['renewals']).' renewal transaction(s) and '.number_format($current['new_subscriptions']).' new paid subscription(s) were recorded in the period.';
        $insights[] = 'Agent-attributed subscription revenue was ₱'.number_format($agent['attributed_revenue'], 2).', with ₱'.number_format($agent['commission_expense'], 2).' in earned commission expense.';
        if ($current['refunds'] > 0) {
            $insights[] = 'Recorded refunds reduced period net revenue by ₱'.number_format($current['refunds'], 2).'.';
        }
        if ($recurring['expiring_soon'] > 0) {
            $insights[] = number_format($recurring['expiring_soon']).' active paid subscriber(s) expire within 30 days.';
        }

        return $insights;
    }

    protected function dateBucketExpression(string $column, string $grain): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => match ($grain) {
                'hour' => "strftime('%Y-%m-%d %H', {$column})",
                'month' => "strftime('%Y-%m', {$column})",
                default => "strftime('%Y-%m-%d', {$column})",
            },
            'pgsql' => match ($grain) {
                'hour' => "to_char({$column}, 'YYYY-MM-DD HH24')",
                'month' => "to_char({$column}, 'YYYY-MM')",
                default => "to_char({$column}, 'YYYY-MM-DD')",
            },
            default => match ($grain) {
                'hour' => "DATE_FORMAT({$column}, '%Y-%m-%d %H')",
                'month' => "DATE_FORMAT({$column}, '%Y-%m')",
                default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
            },
        };
    }

    protected function percentageChange(float|int $current, float|int $previous): ?float
    {
        if ((float) $previous === 0.0) {
            return (float) $current === 0.0 ? 0.0 : null;
        }

        return (($current - $previous) / abs($previous)) * 100;
    }
}
