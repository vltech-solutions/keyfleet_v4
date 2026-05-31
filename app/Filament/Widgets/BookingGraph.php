<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;

class BookingGraph extends ChartWidget
{
    protected static ?string $heading = 'Yearly Booking Chart';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '500px';

    protected function getData(): array
    {

        $activeFilter = ($this->filter) ? $this->filter : now()->year;

        $bookings = Booking::query()
            ->whereYear('start_datetime', $activeFilter)
            ->selectRaw('MONTH(start_datetime) as month, COUNT(*) as bookings')
            ->groupByRaw('MONTH(start_datetime)')
            ->pluck('bookings', 'month');

        $bookingCount = [];

        for ($m = 1; $m <= 12; $m++) {
            $book = $bookings->get($m, 0);

            $bookingCount[] = $book;
            $labels[] = \Carbon\Carbon::create()->month($m)->format('M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $bookingCount,
                    // 'backgroundColor' => 'rgba(34, 197, 94, 0.4)',
                    // 'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true
                ]
            ],
            'labels' => $labels

        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'grid' => [
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
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return collect(range(now()->year, 2020))
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();

    }
}
