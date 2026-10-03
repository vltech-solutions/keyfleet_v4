<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Services\VehicleAvailabilityService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected bool $hasDuplicateSubmissionGuard = true;

    protected function handleRecordCreation(array $data): Model
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            throw new \RuntimeException('Unable to determine the current company.');
        }

        // Always assign company server-side.
        // Do not rely on a hidden form field.
        $data['company_id'] = $tenant->getKey();

        $availability = app(VehicleAvailabilityService::class);

        return $availability->withLockedVehicle(
            (int) $data['car_id'],
            function () use ($availability, $data): Booking {
                $availability->assertAvailable(
                    (int) $data['car_id'],
                    $data['start_datetime'],
                    $data['end_datetime'],
                );

                return Booking::create($data);
            }
        );
    }
}