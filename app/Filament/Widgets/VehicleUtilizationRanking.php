<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Vehicle;
use App\Models\Booking;

class VehicleUtilizationRanking extends ChartWidget
{
    protected static ?string $heading = 'Vehicle Utilization Ranking';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {

        $vehicles = Vehicle::with('bookings')
            ->get()
            ->map(function ($v) {

                $booked = $v->bookings
                    ->sum(fn($b) =>
                        $b->start_date->diffInDays($b->end_date) + 1
                    );

                $util = ($booked / 30) * 100;

                return [
                    'name'=>$v->name,
                    'util'=>$util
                ];
            })
            ->sortByDesc('util')
            ->take(10);

        return [

            'datasets'=>[
                [
                    'label'=>'Utilization %',
                    'data'=>$vehicles->pluck('util')
                ]
            ],

            'labels'=>$vehicles->pluck('name')
        ];
    }
}