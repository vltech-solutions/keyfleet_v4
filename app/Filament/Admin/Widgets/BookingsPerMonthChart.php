<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingsPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Bookings Per Month';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = collect(range(0, 5))
            ->map(fn ($i) => now()->subMonths($i)->startOfMonth())
            ->reverse();

        $labels = $months->map(fn ($date) => $date->format('M Y'));

        $data = $months->map(function ($date) {
            return Booking::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status','approved')
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $data->values(), // make sure it's a clean array
                    'backgroundColor' => '#6366F1',
                    'borderColor' => '#4F46E5',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels->values(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full'; // This makes the chart span full width
    }
}
