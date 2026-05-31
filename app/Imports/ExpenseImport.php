<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExpenseImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

  
    public function model(array $row)
    {
        $failures = [];

        $car = Car::where('name', trim($row['car']))->first();
        if (! $car) {
            $this->onFailure(new Failure(
                0, // Row number is unknown here; will be set internally
                'car',
                ["Car not found: {$row['car']}"],
                $row
            ));
            return null;
        }

        $date = $this->parseDate($row['date']);

        return new Expense([
            'car_id' => $car->id,
            'expense_description' => $row['expense_description'],
            'date' => $date,
            'amount' => $row['amount'],
            'company_id' => auth()->user()?->companies->first()?->id,
        ]);
    }

    private function parseDate($value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value));
        }

        return Carbon::createFromFormat('d/m/Y H:i', trim($value));
    }
}
