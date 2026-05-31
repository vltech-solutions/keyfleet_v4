<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Car;
use Carbon\Carbon;

class TopBookedCarsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Cars by Total Revenue';
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
    protected static ?int $sort = 7;
    protected static ?string $maxHeight = '350px';

    protected function getData(): array
    {
        $cars = Car::with('bookings')->get();

        // Calculate total revenue per car
        $revenueData = $cars->mapWithKeys(function ($car) {
            $totalRevenue = $car->bookings->sum('total_due');

            $label = $car->name . ' (' . $car->model . ' ' . $car->year . ')';

            return [$label => $totalRevenue];
        })->sortDesc()->take(5);

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue (₱)',
                    'data' => array_values($revenueData->toArray()),
                ],
            ],
            'labels' => array_keys($revenueData->toArray()),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Still bar, with horizontal axis enabled below
    }

    protected function getOptions(): ?array
    {
        return [
            'indexAxis' => 'y', // makes it horizontal
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'grid' => ['display' => false],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            
        ];
    }
}
