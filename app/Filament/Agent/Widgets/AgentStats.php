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
        $money = fn (string $status): string => '₱'.number_format((float) $agent->commissions()->where('status', $status)->sum('commission_amount'), 2);

        return [
            Stat::make('Referral Code', $agent->referral_code)->description(url('/register-company?ref='.$agent->referral_code)),
            Stat::make('Qualified Referrals', $agent->referrals()->whereNotNull('qualified_at')->count()),
            Stat::make('Active Referred Customers', $agent->referrals()->whereHas('referred', fn ($query) => $query->whereHas('subscriptions', fn ($q) => $q->whereDate('ends_at', '>=', today())))->count()),
            Stat::make('Pending Commission', $money(AgentCommission::STATUS_PENDING)),
            Stat::make('Payable Commission', $money(AgentCommission::STATUS_PAYABLE)),
            Stat::make('Total Paid', $money(AgentCommission::STATUS_PAID)),
        ];
    }
}
