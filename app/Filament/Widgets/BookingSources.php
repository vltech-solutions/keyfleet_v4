<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use Illuminate\Support\Str;

class BookingSources extends ChartWidget
{
    protected static ?string $heading = 'Booking Source Analysis';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '270px';

    protected static bool $isLazy = true;

    protected function getData(): array
    {

        $bookings = Booking::query()
            ->join('sources', 'bookings.source_id', '=', 'sources.id')
            ->select('sources.source')
            ->selectRaw('COUNT(*) as bookings')
            ->groupBy('sources.source')
            ->pluck('bookings', 'sources.source'); 

        $filamentColors = [
            '#3b82f6', // blue-500
            '#22c55e', // green-500
            '#ef4444', // red-500
            '#f97316', // orange-500
            '#10b981', // emerald-500
            '#f59e0b', // amber-500
            '#6366f1', // indigo-500
            '#ec4899', // pink-500
            '#14b8a6', // teal-500
            '#8b5cf6', // violet-500
            '#0ea5e9', // sky-500
        ];
        $labels = array_keys($bookings->toArray());
        $colors = array_slice($filamentColors, 0, count($labels));

        return [
            'datasets' => [
                [
                    'label' => 'Bookings by Source',
                    'data' => array_values($bookings->toArray()),
                    'backgroundColor' => $colors,
                    'borderColor' => $colors, 
                ],
            ],
            'labels' => array_keys($bookings->toArray()),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'datalabels' => [
                    'color' => '#fff',
                    'anchor' => 'center',
                    'align' => 'center',
                    'formatter' => 'function(value) { return value; }',
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'display' => false, 
                    ],
                    'grid' => [
                        'drawTicks' => false,
                        'display' => false, 
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false, 
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false, 
            
        ];
    }


    protected function getType(): string
    {
        return 'doughnut';
    }
}
