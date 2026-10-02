<?php

namespace App\Filament\Concerns;

trait AuthorizesTenantPage
{
    public static function canAccess(): bool
    {
        $permission = match (class_basename(static::class)) {
            'Calendar' => 'calendar.view',
            'FleetUtilizationReport' => 'reports.view',
            'VehicleRevenue', 'IncomeFlowReport', 'PartnerCommissionSummary' => 'reports.financial',
            'ContractBuilder' => 'contracts.manage',
            'CompanyProfile' => 'settings.update',
            'SubscriptionOverview', 'ReferralDashboard' => 'subscription.view',
            'BookingInspectionPage' => 'inspections.manage',
            'ViewInspectionPage' => 'inspections.view',
            default => null,
        };

        return $permission === null || (auth()->user()?->hasPermission($permission) ?? false);
    }
}
