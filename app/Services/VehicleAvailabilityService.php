<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleAvailabilityService
{
    public function isAvailable(
        int $carId,
        CarbonInterface|string $start,
        CarbonInterface|string|null $end = null,
        ?int $excludeBookingId = null,
        ?int $excludeReservationId = null,
        bool $includeReservations = true,
    ): bool {
        $startAt = Carbon::parse($start);
        $endAt = $end ? Carbon::parse($end) : $startAt->copy()->addMinutes(30);
        if ($endAt->lessThanOrEqualTo($startAt)) {
            return false;
        }
        $bookingOverlap = Booking::query()
            ->where('car_id', $carId)
            ->where('status', 'approved')
            ->when($excludeBookingId, fn ($query) => $query->whereKeyNot($excludeBookingId))
            ->where('start_datetime', '<', $endAt)
            ->where('end_datetime', '>', $startAt)
            ->exists();
        if ($bookingOverlap || ! $includeReservations) {
            return ! $bookingOverlap;
        }
        return ! Reservation::query()
            ->where('selected_car_id', $carId)
            ->whereNull('booking_id')
            ->whereNull('datetime_declined')
            ->whereNull('datetime_cancelled')
            ->when($excludeReservationId, fn ($query) => $query->whereKeyNot($excludeReservationId))
            ->where('start_date', '<', $endAt)
            ->where('end_date', '>', $startAt)
            ->exists();
    }

    public function assertAvailable(
        int $carId,
        CarbonInterface|string $start,
        CarbonInterface|string $end,
        ?int $excludeBookingId = null,
        ?int $excludeReservationId = null,
        bool $includeReservations = true,
    ): void {
        if (! $this->isAvailable($carId, $start, $end, $excludeBookingId, $excludeReservationId, $includeReservations)) {
            throw ValidationException::withMessages([
                'car_id' => 'This vehicle is no longer available for the selected schedule. Please select another vehicle or change the rental dates.',
            ]);
        }
    }

    public function createReservation(array $attributes): Reservation
    {
        $carId = (int) $attributes['selected_car_id'];
        return $this->withLockedVehicle($carId, function () use ($attributes, $carId): Reservation {
            $this->assertAvailable($carId, $attributes['start_date'], $attributes['end_date']);
            return Reservation::create($attributes);
        });
    }

    public function approveReservation(Reservation $reservation, array $bookingAttributes): Booking
    {
        $carId = (int) $reservation->selected_car_id;
        return $this->withLockedVehicle($carId, function () use ($reservation, $bookingAttributes, $carId): Booking {
            $lockedReservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->getKey());
            if ($lockedReservation->booking_id || $lockedReservation->datetime_declined || $lockedReservation->datetime_cancelled) {
                throw ValidationException::withMessages([
                    'car_id' => 'This reservation is no longer pending approval.',
                ]);
            }
            $this->assertAvailable(
                $carId,
                $lockedReservation->start_date,
                $lockedReservation->end_date,
                null,
                (int) $lockedReservation->getKey(),
            );
            $booking = Booking::create($bookingAttributes);
            $lockedReservation->update([
                'booking_id' => $booking->getKey(),
                'status' => 'approved',
            ]);
            $reservation->setRelation('booking', $booking);
            $reservation->forceFill(['booking_id' => $booking->getKey()]);
            return $booking;
        });
    }

    public function withLockedVehicle(int $carId, Closure $callback): mixed
    {
        return DB::transaction(function () use ($carId, $callback): mixed {
            Car::query()->whereKey($carId)->lockForUpdate()->firstOrFail();
            return $callback();
        }, 3);
    }
}
