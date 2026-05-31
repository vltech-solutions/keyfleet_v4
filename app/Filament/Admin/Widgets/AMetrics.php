<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use App\Models\Subscription;
use App\Models\PlanPrice;

class AMetrics extends BaseWidget
{

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {

        $today = Carbon::today();

        // Active paid subscribers
        $activeSubscribers = Company::whereHas('subscriptions', function ($q) use ($today) {
            $q->where('starts_at', '<=', $today)
            ->where('ends_at', '>=', $today)
            ->whereNotNull('plan_id')
            ->whereHas('plan.prices', function ($q2) {
                $q2->where('price', '>', 0);
            });
        })->count();

        // Free trials (active, but no paid plan)
        $freeTrials = Company::whereHas('subscriptions', function ($q) use ($today) {
            $q->where('starts_at', '<=', $today)
            ->where('ends_at', '>=', $today)
            ->where(function ($q2) {
                $q2->whereNull('plan_id')
                    ->orWhereHas('plan.prices', function ($q3) {
                        $q3->where('price', 0);
                    });
            });
        })->whereDoesntHave('subscriptions', function ($q) use ($today) {
            // exclude companies that already have active paid subs
            $q->where('starts_at', '<=', $today)
            ->where('ends_at', '>=', $today)
            ->whereNotNull('plan_id')
            ->whereHas('plan.prices', function ($q2) {
                $q2->where('price', '>', 0);
            });
        })->count();

        // Inactive (had subscriptions before, but no active paid or free trial now)
        $inactiveSubscribers = Company::whereHas('subscriptions')
            ->whereDoesntHave('subscriptions', function ($q) use ($today) {
                $q->where('starts_at', '<=', $today)
                ->where('ends_at', '>=', $today);
            })->count();

        $mrr = Subscription::with('planPrice')
          ->whereDate('starts_at', '<=', $today) // already started
          ->where(function ($q) use ($today) {
              $q->whereNull('ends_at') // not ended
                ->orWhere('ends_at', '>=', $today); // or still active
          })
          ->get()
          ->filter(fn ($sub) => $sub->planPrice && $sub->planPrice->billing_cycle === 'monthly')
          ->sum(fn ($sub) => $sub->planPrice->price);


        // ✅ Turnover (MRR) = sum of net_amount from subscriptions **starting this month**
        $turnover = Subscription::whereMonth('starts_at', $today->month)
            ->whereYear('starts_at', $today->year)
            ->sum('net_amount');

        // ✅ Retention rate = companies who had an active subscription last month AND this month
        $lastMonthStart = $today->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $today->copy()->subMonth()->endOfMonth();

        // Companies with subscriptions last month
        $lastMonthCompanies = Subscription::whereBetween('starts_at', [$lastMonthStart, $lastMonthEnd])
            ->pluck('company_id')
            ->unique();

        // Companies with subscriptions this month
        $thisMonthCompanies = Subscription::whereMonth('starts_at', $today->month)
            ->whereYear('starts_at', $today->year)
            ->pluck('company_id')
            ->unique();

        // Retained companies = intersection of last month and this month
        $retainedCompanies = $lastMonthCompanies->intersect($thisMonthCompanies);

        $retentionRate = $lastMonthCompanies->count() > 0
            ? round(($retainedCompanies->count() / $lastMonthCompanies->count()) * 100, 1)
            : 0;
        // Total companies
        $totalCompanies = Company::count();

        $churnedCompanies = $lastMonthCompanies->diff($thisMonthCompanies)->count();

        return [
            Stat::make('Active Subscribers', $activeSubscribers)
                ->description(number_format(($activeSubscribers / max($totalCompanies, 1)) * 100, 1) . '% of companies')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Inactive Subscribers', $inactiveSubscribers)
                ->description(number_format(($inactiveSubscribers / max($totalCompanies, 1)) * 100, 1) . '% of companies')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Free Trials', $freeTrials)
                ->description(number_format(($freeTrials / max($totalCompanies, 1)) * 100, 1) . '% of companies')
                ->icon('heroicon-o-gift')
                ->color('warning'),

            Stat::make('Total Companies', $totalCompanies)
                ->description('All registered companies')
                ->icon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('MRR', '₱' . number_format($mrr, 2))
                ->description('Monthly Recurring Revenue')
                ->icon('heroicon-o-banknotes')   // currency icon
                ->color('success'),

            Stat::make('Retention Rate', $retentionRate . '%')
                ->description('Returning companies from last month')
                ->icon('heroicon-o-arrow-path') // refresh icon for retention
                ->color($retentionRate >= 50 ? 'success' : 'warning'),

            Stat::make('Turnover', '₱' . number_format($turnover, 2))
                ->description('Total revenue from all subscriptions this month')
                ->icon('heroicon-o-arrow-trending-up') // trending up icon
                ->color('primary'),

            Stat::make('Churned Subscribers', $churnedCompanies)
                ->description('Companies who did not renew this month')
                ->icon('heroicon-o-arrow-down')
                ->color('danger'),
        ];
    }
}
