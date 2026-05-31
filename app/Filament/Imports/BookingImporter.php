<?php

namespace App\Filament\Imports;

use App\Models\Booking;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Throwable;

class BookingImporter extends Importer
{
    protected static ?string $model = Booking::class;
    public array $failures = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('car')
                ->requiredMapping()
                ->relationship('car','name')
                ->rules(['required']),
            ImportColumn::make('source')
                ->requiredMapping()
                ->relationship('source','source')
                ->rules(['required']),
            ImportColumn::make('start_datetime')
                ->requiredMapping()
                ->label('Start DateTime (Use "d/m/Y H:i" format to save the date and time)')
                ->rules(['required'])
                ->helperText('Use format d/m/Y H:i')
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        // Clean up extra spaces
                        $state = preg_replace('/\s+/', ' ', trim($state));

                        // Define the expected format
                        return Carbon::createFromFormat('d/m/Y H:i', $state)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages([
                            'start_datetime' => "Invalid date format: '{$state}'. Use formatssss '".date('d/m/Y H:i')."' .",
                        ]);
                    }
                }),

            ImportColumn::make('end_datetime')
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Use format d/m/Y H:i')
                ->label('Start DateTime (Use "d/m/Y H:i" format to save the date and time)')
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }

                    try {
                         // Clean up extra spaces
                        $state = preg_replace('/\s+/', ' ', trim($state));

                        // Define the expected format
                        return Carbon::createFromFormat('d/m/Y H:i', $state)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages([
                            'end_datetime' => "Invalid date format: '{$state}'. Use formatsss '".date('d/m/Y H:i')."' .",
                        ]);
                    }
                }),
            ImportColumn::make('renter_name')
                ->requiredMapping(),
            ImportColumn::make('contact_number'),
            ImportColumn::make('destination'),
            ImportColumn::make('total_due')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('paid_amount')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('balance')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    // protected function beforeSave(): void
    // {
    //     $this->data['company_id'] = $this->options['companyId'];
    // }

    public function resolveRecord(): ?Booking
    {
        try {

            //get the daily rate and how many days rented
            $rentalDays = round(Carbon::parse($this->data['start_datetime'])->diffInDays(Carbon::parse($this->data['end_datetime'])));
            $dailyRate = $this->data['total_due'] / $rentalDays;
            $this->data['total_rent_due'] = $dailyRate * $rentalDays;
            $this->data['daily_rate'] = $dailyRate;
            $this->data['days_rented'] = $rentalDays;
            return new Booking([
                'car_id' => $this->data['car'],
                'source_id' => $this->data['source'],
                'start_datetime' => $this->data['start_datetime'],
                'end_datetime' => $this->data['end_datetime'],
                'renter_name' => $this->data['renter_name'],
                'contact_number' => $this->data['contact_number'] ?? null,
                'destination' => $this->data['destination'] ?? null,
                'total_due' => $this->data['total_due'],
                'paid_amount' => $this->data['paid_amount'],
                'total_rent_due' => $this->data['total_rent_due'],
                'daily_rate' => $this->data['daily_rate'],
                'days_rented' => $this->data['days_rented'],
                'balance' => $this->data['balance'],
                'company_id' => $this->options['companyId'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Import failed for row', [
                'row' => $this->data,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
    

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your booking import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Import job failed.', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        Notification::make()
            ->title('Booking Import Failed')
            ->body('There was an error processing your import. Please review your file and try again.')
            ->danger()
            ->send();
    }
}
