<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Widgets\AgentStats;
use App\Filament\Agent\Widgets\RecentCommissions;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [AgentStats::class, RecentCommissions::class];
    }
}
