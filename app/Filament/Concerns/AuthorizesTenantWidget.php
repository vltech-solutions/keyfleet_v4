<?php

namespace App\Filament\Concerns;

trait AuthorizesTenantWidget
{
    public static function canView(): bool
    {
        $permission = match (class_basename(static::class)) {
            'FinancialGraph', 'FinancialSummaryStats', 'FundTypes' => 'reports.financial',
            'CarAvailability' => 'cars.view',
            'FleetUtilizationStats', 'FleetUtilizationTrend', 'TopBookedCarsChart',
            'VehicleUtilizationRanking' => 'reports.view',
            default => 'dashboard.view',
        };

        return auth()->user()?->hasPermission($permission) ?? false;
    }
}
