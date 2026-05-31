<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class BookingExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with(['car', 'source']);
    }

    public function map($booking): array
    {
        return [
            $booking->car?->brand.' '.$booking->car?->model.' '.$booking->car?->year,
            $booking->car?->name,
            $booking->source?->source ?? 'N/A',
            Carbon::parse($booking->start_datetime)->format('d/m/Y H:i'),
            Carbon::parse($booking->end_datetime)->format('d/m/Y H:i'),
            $booking->renter_name,
            $booking->contact_number,
            $booking->destination,
            $booking->renter_address,
            $booking->delivery_address ?? 'GARAGE',
            $booking->return_address ?? 'GARAGE',
            $booking->total_due,
            $booking->paid_amount,
            $booking->balance,
        ];
    }

    public function headings(): array
    {
        return [
            'Car',
            'Car name',
            'Source',
            'Start DateTime',
            'End DateTime',
            'Renter Name',
            'Contact Number',
            'Address',
            'Destination',
            'Delivery',
            'Return',
            'Total Due',
            'Paid Amount',
            'Balance',
        ];
    }
}
