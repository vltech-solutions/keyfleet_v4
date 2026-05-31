<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpenseExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with(['car', 'fundType']);
    }

    public function map($expense): array
    {
        return [
            $expense->car?->name ?? 'N/A',
            $expense->expense_description,
            Carbon::parse($expense->date)->format('d/m/Y'),
            $expense->amount,
            $expense->deduct_to_fund && $expense->fundType?->name
                ? $expense->fundType->name
                : '-', 
        ];
    }

    public function headings(): array
    {
        return [
            'Car',
            'Expense Description',
            'Date',
            'Amount',
            'Fund Type',
        ];
    }
}
