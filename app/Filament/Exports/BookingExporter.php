<?php

namespace App\Filament\Exports;

use App\Models\Booking;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BookingExporter extends Exporter
{
    protected static ?string $model = Booking::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('car.name'),
            ExportColumn::make('start_datetime'),
            ExportColumn::make('end_datetime'),
            ExportColumn::make('renter_name'),
            ExportColumn::make('contact_number'),
            ExportColumn::make('destination'),
            ExportColumn::make('daily_rate'),
            ExportColumn::make('days_rented'),
            ExportColumn::make('total_rent_due'),
            ExportColumn::make('delivery_fee'),
            ExportColumn::make('total_due'),
            ExportColumn::make('paid_amount'),
            ExportColumn::make('balance'),
            ExportColumn::make('source.source'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your booking export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
