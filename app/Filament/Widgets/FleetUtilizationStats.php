<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Car;
use App\Models\Booking;
use Carbon\Carbon;

class FleetUtilizationStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $vehicles = Car::count();

        $bookedDays = Booking::selectRaw('SUM(DATEDIFF(end_date,start_date)+1) as days')
            ->value('days');

        $availableDays = $vehicles * 30;

        $utilization = $availableDays > 0
            ? ($bookedDays / $availableDays) * 100
            : 0;

        $remark = match(true) {
            $utilization >= 90 => 'Excellent – Ready to add cars',
            $utilization >= 70 => 'Good – Optimize pricing',
            $utilization >= 50 => 'Moderate – Improve demand',
            default => 'Poor – Fix operations',
        };

        $color = match(true) {
            $utilization >= 90 => 'success',
            $utilization >= 70 => 'info',
            $utilization >= 50 => 'warning',
            default => 'danger',
        };

        return [

            Stat::make('Fleet Utilization Rate', round($utilization,2).'%')
                ->description($remark)
                ->color($color),

            Stat::make('Total Vehicles', $vehicles),

            Stat::make('Average Utilization Per Car',
                round($utilization / max($vehicles,1),2).'%' ),

        ];
    }
}