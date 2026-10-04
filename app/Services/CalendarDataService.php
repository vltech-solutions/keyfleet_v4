<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Company;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CalendarDataService
{
    public function events(Company $company, Carbon $from, Carbon $to, array $filters = []): Collection
    {
        return $this->bookings($company, $from, $to, $filters)
            ->flatMap(function (Booking $booking) use ($from, $to): array {
                $start = $booking->start_datetime->copy()->startOfDay()->max($from->copy()->startOfDay());
                $end = $booking->end_datetime->copy()->startOfDay()->min($to->copy()->startOfDay());
                $events = [];

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $events[] = $this->eventPayload($booking, $date);
                }

                return $events;
            })->values();
    }

    public function bookings(Company $company, Carbon $from, Carbon $to, array $filters = []): Collection
    {
        return Booking::query()
            ->where('company_id', $company->getKey())
            ->where('start_datetime', '<=', $to->copy()->endOfDay())
            ->where('end_datetime', '>=', $from->copy()->startOfDay())
            ->where('status', '!=', 'quotation')
            ->when(filled($filters['car_id'] ?? null), fn ($query) => $query->where('car_id', $filters['car_id']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['source_id'] ?? null), fn ($query) => $query->where('source_id', $filters['source_id']))
            ->with([
                'car' => fn ($query) => $query->withTrashed()->select([
                    'id', 'company_id', 'name', 'brand', 'model', 'plate_number', 'image',
                    'is_available', 'deleted_at',
                ]),
                'source:id,source',
            ])
            ->orderBy('start_datetime')
            ->orderBy('id')
            ->get();
    }

    public function statuses(Company $company): Collection
    {
        return Booking::query()->where('company_id', $company->getKey())
            ->where('status', '!=', 'quotation')->whereNotNull('status')
            ->distinct()->orderBy('status')->pluck('status');
    }

    private function eventPayload(Booking $booking, Carbon $date): array
    {
        $car = $booking->car;
        $image = $car?->image;
        $imageUrl = $image && Storage::disk('public')->exists($image)
            ? Storage::disk('public')->url($image)
            : asset('images/placeholder-car.png');

        return [
            'id' => $booking->getKey(),
            'booking_id' => $booking->booking_id ?: '#'.$booking->getKey(),
            'car_id' => $booking->car_id,
            'car_name' => $car?->name ?: 'Archived vehicle',
            'plate_number' => $car?->plate_number,
            'image_url' => $imageUrl,
            'renter_name' => $booking->renter_name ?: 'Customer not specified',
            'status' => $booking->status ?: 'unknown',
            'start' => $booking->start_datetime->toIso8601String(),
            'end' => $booking->end_datetime->toIso8601String(),
            'start_label' => $booking->start_datetime->format('M j, Y g:i A'),
            'end_label' => $booking->end_datetime->format('M j, Y g:i A'),
            'location' => $booking->delivery_address ?: $booking->destination,
            'return_location' => $booking->return_address,
            'source' => $booking->source?->source,
            'vehicle_available' => (bool) ($car?->is_available ?? false),
            'vehicle_archived' => (bool) ($car?->trashed() ?? true),
            'date' => $date->toDateString(),
        ];
    }
}
