<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Subscription;
use Filament\Widgets\ChartWidget;

class SubscriptionRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Subscription Net Revenue (Last 6 Months)';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = collect(range(0, 5))
            ->map(fn ($i) => now()->subMonths($i)->startOfMonth())
            ->reverse();

        $labels = $months->map(fn ($date) => $date->format('M Y'));

        $revenue = $months->map(function ($date) {
            return Subscription::whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('net_amount'); // ✅ directly sum column
        });

        return [
            'datasets' => [
                [
                    'label' => 'Net Revenue',
                    'data' => $revenue->values(),
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels->values(),
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
