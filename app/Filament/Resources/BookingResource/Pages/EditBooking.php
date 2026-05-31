<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Pages\ViewInspectionPage;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Contract;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('invoice')
                ->label('Generate Invoice')
                ->icon('heroicon-s-cloud-arrow-down')
                ->action(function (Booking $record) {
                    $company = Filament::getTenant();
                    $booking = $record->load('car');

                    $invoiceData = [
                        'company' => $company,
                        'booking' => $booking
                    ];

                    $invoiceBlade = explode('.', $company->invoice_template)[0];

                    $pdf = Pdf::loadView('invoice.' . $invoiceBlade, ['invoiceData' => $invoiceData]);

                    $tempPath = storage_path('app/temp_invoice_' . now()->timestamp . '.pdf');
                    $pdf->save($tempPath);

                    return response()->download($tempPath, 'invoice.pdf')->deleteFileAfterSend();
                // ->action(function (Booking $record) {
                    
                //     $company = Filament::getTenant();
                //     $booking = $record->load('car');
                    
                //     $invoiceData = [
                //         'company' => $company,
                //         'booking' => $booking
                //     ];

                //     $invoiceBlade = explode('.', $company->invoice_template)[0];

                //     $pdf = Pdf::loadView('invoice.'.$invoiceBlade, ['invoiceData' => $invoiceData]);
                //     return response()->streamDownload(function () use ($pdf) { echo $pdf->stream(); }, 'invoice.pdf');
                }),
            
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        if (auth()->user()->hasActiveSubscription()) {
            return parent::getFormActions(); // normal Save/Cancel buttons
        }

        // Hide Save Changes if no active subscription
        return [
            $this->getCancelFormAction(),
            // OR you could disable Save instead of hiding:
            // $this->getSaveFormAction()->disabled(),
        ];
    }
}
