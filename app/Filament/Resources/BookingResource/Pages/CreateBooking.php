<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;

use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected bool $hasDuplicateSubmissionGuard = true;

    // protected function getFormActions(): array
    // {
    //     return [
    //         Actions\Action::make('save')
    //             ->label('Save Booking')
    //             ->submit('save') 
    //             ->color('primary'),
    //         $this->getCancelFormAction(),
    //     ];
    // }
}
