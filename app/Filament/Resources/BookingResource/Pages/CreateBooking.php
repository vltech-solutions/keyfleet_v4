<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Services\VehicleAvailabilityService;
use Filament\Actions;

use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected bool $hasDuplicateSubmissionGuard = true;

    protected function handleRecordCreation(array $data): Model
    {
        $availability = app(VehicleAvailabilityService::class);
        return $availability->withLockedVehicle((int) $data['car_id'], function () use ($availability, $data): Booking {
            $availability->assertAvailable((int) $data['car_id'], $data['start_datetime'], $data['end_datetime']);
            return Booking::create($data);
        });
    }

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
