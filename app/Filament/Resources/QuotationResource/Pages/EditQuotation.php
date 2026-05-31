<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('invoice')
                ->label('Generate Quotation')
                ->icon('heroicon-s-cloud-arrow-down')
                ->action(function (Booking $record) {
                    $company = Filament::getTenant();
                    $booking = $record->load('car');

                    $invoiceData = [
                        'company' => $company,
                        'booking' => $booking,
                        'type'  => 'quotation',
                    ];

                    $invoiceBlade = explode('.', $company->invoice_template)[0];

                    $pdf = Pdf::loadView('invoice.' . $invoiceBlade, ['invoiceData' => $invoiceData]);

                    $tempPath = storage_path('app/temp_invoice_' . now()->timestamp . '.pdf');
                    $pdf->save($tempPath);

                    return response()->download($tempPath, 'quotation.pdf')->deleteFileAfterSend();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
