<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use Carbon\Carbon;

class FleetUtilizationTrend extends ChartWidget
{
    protected static ?string $heading = 'Fleet Utilization Trend';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {

        $labels = [];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {

            $month = Carbon::create()->month($i);

            $booked = Booking::whereMonth('start_date',$i)
                ->selectRaw('SUM(DATEDIFF(end_date,start_date)+1) as days')
                ->value('days');

            $available = 30;

            $util = $available > 0
                ? ($booked/$available)*100
                : 0;

            $labels[] = $month->format('M');
            $data[] = round($util,2);
        }

        return [

            'datasets' => [
                [
                    'label' => 'Utilization %',
                    'data' => $data,
                ],
            ],

            'labels' => $labels,
        ];
    }
}