<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Models\Expense;
use Filament\Widgets\Widget;

class ExpenseOverview extends Widget
{
    // Point this to your new blade file
    protected static string $view = 'filament.widgets.expense-overview-stats';

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
        'lg' => 2,
    ];

    public function getViewData(): array
    {
        return [
            'totalExpenses' => Expense::sum('amount'),
            'fleetExpenses' => Expense::whereNotNull('car_id')->sum('amount'),
            'generalExpenses' => Expense::whereNull('car_id')->sum('amount'),
        ];
    }
}