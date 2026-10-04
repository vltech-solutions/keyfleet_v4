<?php

namespace App\Filament\Agent\Widgets;

use App\Models\AgentCommission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $agent = auth()->user()->agentProfile;
        $sumNet = fn ($query): float => (float) $query
            ->selectRaw('COALESCE(SUM(commission_amount - reversed_amount), 0) total')
            ->value('total');
        $onHold = $sumNet($agent->commissions()->where(function ($query): void {
            $query->where('status', AgentCommission::STATUS_PENDING)
                ->orWhere(function ($query): void {
                    $query->where('status', AgentCommission::STATUS_PARTIALLY_REVERSED)
                        ->where('payable_at', '>', now());
                });
        }));
        $payable = $sumNet($agent->commissions()->where(function ($query): void {
            $query->where('status', AgentCommission::STATUS_PAYABLE)
                ->orWhere(function ($query): void {
                    $query->where('status', AgentCommission::STATUS_PARTIALLY_REVERSED)
                        ->where('payable_at', '<=', now());
                });
        }));
        $paid = $sumNet($agent->commissions()->where('status', AgentCommission::STATUS_PAID));
        $reversed = (float) $agent->commissions()->sum('reversed_amount');
        $lifetime = $sumNet($agent->commissions());

        return [
            Stat::make('Referral Code', $agent->referral_code)->description(url('/register-company?ref='.$agent->referral_code)),
            Stat::make('Total Referrals', $agent->referrals()->count()),
            Stat::make('Active Referred Customers', $agent->referrals()->whereHas('referred', fn ($query) => $query->whereHas('subscriptions', fn ($q) => $q->whereDate('ends_at', '>=', today())))->count()),
            Stat::make('Commission On Hold', 'PHP '.number_format($onHold, 2)),
            Stat::make('Payable Commission', 'PHP '.number_format($payable, 2)),
            Stat::make('Paid Commission', 'PHP '.number_format($paid, 2)),
            Stat::make('Reversed Commission', 'PHP '.number_format($reversed, 2)),
            Stat::make('Lifetime Commission', 'PHP '.number_format($lifetime, 2)),
        ];
    }
}
