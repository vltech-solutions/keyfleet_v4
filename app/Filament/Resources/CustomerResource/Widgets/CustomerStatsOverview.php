<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsOverview extends BaseWidget
{
    protected static string $view = 'filament.widgets.customer-summary-stats';

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
        'lg' => 2,
    ];

    public function getViewData(): array
    {
        $totalCount = Customer::count();

        $topCustomer = Customer::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->first();

        $totalReceivable = Customer::with('bookings')->get()
            ->sum(fn($customer) => $customer->bookings->sum('balance'));

        return [
            'totalCount' => $totalCount,
            'topCustomer' => $topCustomer,
            'totalReceivable' => $totalReceivable,
        ];
    }
}
