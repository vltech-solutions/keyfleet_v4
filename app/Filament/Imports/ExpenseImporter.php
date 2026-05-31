<?php

namespace App\Filament\Imports;

use App\Models\Expense;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ExpenseImporter extends Importer
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('car')
                ->requiredMapping()
                ->relationship('car','name')
                ->rules(['required']),
            ImportColumn::make('expense_description')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('date')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }

                    try {
                        return Carbon::parse($state)->format('Y-m-d');
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages([
                            'date' => "Invalid date format: '{$state}'. Use format '".date('Y-m-d')."' .",
                        ]);
                    }
                }),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?Expense
    {
        try {
            return new Expense([
                'car_id' => $this->data['car'],
                'expense_description' => $this->data['expense_description'],
                'date' => $this->data['date'],
                'amount' => $this->data['amount'],
                'company_id' => $this->options['companyId'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Import failed for row', [
                'row' => $this->data,
                'message' => $e->getMessage(),
            ]);

            return new Expense();
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your expense import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
