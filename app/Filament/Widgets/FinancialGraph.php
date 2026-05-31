<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use App\Models\BookingPayments;
use App\Models\Expense; 

class FinancialGraph extends ChartWidget
{
    protected static ?string $heading = 'Yearly Financial Chart';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $activeFilter = $this->filter ?? now()->year;

        // Get payments grouped by month, excluding 'Partner's Fund'
        $revenues = BookingPayments::whereHas('fundType', function ($q) {
                $q->where('name', '!=', "Partner's Fund");
            })
            ->whereYear('payment_date', $activeFilter)
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as revenue')
            ->groupByRaw('MONTH(payment_date)')
            ->pluck('revenue', 'month');

        // Get expenses grouped by month
        $expenses = Expense::whereYear('date', $activeFilter)
            ->selectRaw('MONTH(date) as month, SUM(amount) as expense')
            ->groupByRaw('MONTH(date)')
            ->pluck('expense', 'month');

        $revenue = [];
        $expense = [];
        $profit = [];
        $labels = [];

        for ($m = 1; $m <= 12; $m++) {
            $rev = $revenues->get($m, 0);
            $exp = $expenses->get($m, 0);
            $pro = $rev - $exp;

            $revenue[] = $rev;
            $expense[] = $exp;
            $profit[] = $pro;
            $labels[] = \Carbon\Carbon::create()->month($m)->format('M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Expense',
                    'data' => $expense,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Profit',
                    'data' => $profit,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
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
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return collect(range(now()->year, 2020))
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();

    }
}
