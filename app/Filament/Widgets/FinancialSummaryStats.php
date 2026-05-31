<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

use App\Models\Expense;
use App\Models\Booking;
use App\Models\BookingPayments;

class FinancialSummaryStats extends Widget
{
    protected static string $view = 'filament.widgets.financial-summary-stats';

    protected static ?int $sort = 1;

    public $filter = 'all';

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public $totalExpenses = 0;
    public $totalBookings = 0;
    public $totalRevenue = 0;
    public $totalProfit = 0;  

    public function getTotalBookingsProperty()
    {
        return match ($this->filter) {
            'all' => Booking::where('status', 'approved')->count(),
            'current_year' => Booking::whereYear('start_datetime', now()->year)
                                    ->where('status', 'approved')
                                    ->count(),
            'current_month' => Booking::whereYear('start_datetime', now()->year)
                                    ->whereMonth('start_datetime', now()->month)
                                    ->where('status', 'approved')
                                    ->count(),
            default => 0,
        };
    }

    public function getTotalExpensesProperty()
    {
        return match ($this->filter) {
            'all' => Expense::sum('amount'),
            'current_year' => Expense::whereYear('date', now()->year)->sum('amount'),
            'current_month' => Expense::whereMonth('date', now()->month)->sum('amount'),
            default => 0,
        };
    }

    public function getTotalRevenueProperty()
    {
        $payments = match ($this->filter) {
            'all' => BookingPayments::whereHas('fundType', function ($q) {
                            $q->where('name', '!=', "Partner's Fund");
                        })->get(),

            'current_year' => BookingPayments::whereHas('fundType', function ($q) {
                            $q->where('name', '!=', "Partner's Fund");
                        })->whereYear('payment_date', now()->year)
                        ->get(),

            'current_month' => BookingPayments::whereHas('fundType', function ($q) {
                            $q->where('name', '!=', "Partner's Fund");
                        })->whereMonth('payment_date', now()->month)
                        ->get(),

            default => collect(),
        };
        
        return $payments->sum('amount');
    }

    public function getTotalProfitProperty()
    {
        return $this->getTotalRevenueProperty() - $this->getTotalExpensesProperty();
    }
}
