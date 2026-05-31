<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpenseTemplateExport implements WithHeadings
{
    public function headings(): array
    {
        return [
            'car',           
            'expense_description',      
            'date',  
            'amount'
        ];
    }
}