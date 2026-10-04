<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IcalendarService
{
    public function calendar(Collection $bookings, string $name = 'KeyFleet Calendar'): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//KeyFleet//Booking Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape($name),
            'X-WR-TIMEZONE:'.config('app.timezone'),
        ];

        foreach ($bookings as $booking) {
            array_push($lines, ...$this->event($booking));
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map([$this, 'fold'], $lines))."\r\n";
    }

    public function event(Booking $booking): array
    {
        $booking->loadMissing('car');
        $vehicle = $booking->car?->name ?: 'Archived vehicle';
        $plate = $booking->car?->plate_number;
        $reference = $booking->booking_id ?: '#'.$booking->getKey();
        $summary = $vehicle.($booking->renter_name ? ' - '.$booking->renter_name : '');
        $description = array_filter([
            'Booking Reference: '.$reference,
            'Vehicle: '.$vehicle.($plate ? ' ('.$plate.')' : ''),
            $booking->renter_name ? 'Customer: '.$booking->renter_name : null,
            $booking->status ? 'Status: '.ucfirst($booking->status) : null,
        ]);

        return array_values(array_filter([
            'BEGIN:VEVENT',
            'UID:booking-'.$booking->getKey().'@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'keyfleet.local'),
            'DTSTAMP:'.$this->utc(now()),
            'DTSTART:'.$this->utc($booking->start_datetime),
            'DTEND:'.$this->utc($booking->end_datetime),
            'SUMMARY:'.$this->escape($summary),
            'DESCRIPTION:'.$this->escape(implode("\n", $description)),
            ($booking->delivery_address ?: $booking->destination)
                ? 'LOCATION:'.$this->escape($booking->delivery_address ?: $booking->destination)
                : null,
            'STATUS:'.($booking->status === 'cancelled' ? 'CANCELLED' : 'CONFIRMED'),
            'END:VEVENT',
        ], fn ($line) => $line !== null));
    }

    private function utc(Carbon|string $date): string
    {
        return Carbon::parse($date, config('app.timezone'))->utc()->format('Ymd\THis\Z');
    }

    private function escape(string $value): string
    {
        return str_replace(["\\", "\r\n", "\n", ',', ';'], ["\\\\", '\\n', '\\n', '\\,', '\\;'], $value);
    }

    private function fold(string $line): string
    {
        $result = '';

        while (strlen($line) > 75) {
            $cut = 75;
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--;
            }
            $result .= substr($line, 0, $cut)."\r\n ";
            $line = substr($line, $cut);
        }

        return $result.$line;
    }
}
