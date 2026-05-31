<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use Carbon\Carbon;

class BookingIdSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = Booking::orderBy('created_at')
            ->orderBy('company_id')
            ->get()
            ->groupBy(function ($booking) {
                return Carbon::parse($booking->created_at)->format('Y-m-d')
                    . '-' . $booking->company_id;
            });

        foreach ($bookings as $group) {
            $sequence = 1;

            foreach ($group as $booking) {
                if ($booking->booking_id) {
                    continue; // skip if already generated
                }

                $date = Carbon::parse($booking->created_at);

                $booking->booking_id = sprintf(
                    '%s%s%s-%s-%05d',
                    $date->year,
                    str_pad($date->month, 2, '0', STR_PAD_LEFT),
                    str_pad($date->day, 2, '0', STR_PAD_LEFT),
                    $booking->company_id,
                    $sequence
                );

                $booking->save();
                $sequence++;
            }
        }
    }
}
