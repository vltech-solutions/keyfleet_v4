<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class BackfillBookingCommissionSettings extends Command
{
    protected $signature = 'bookings:backfill-commission-settings';
    protected $description = 'Backfill commission settings for existing bookings';

    public function handle()
    {
        $this->info('Starting to backfill commission settings...');

        $bookings = Booking::whereNull('commission_type')
            ->whereHas('car.partner')
            ->get();

        $bar = $this->output->createProgressBar($bookings->count());

        foreach ($bookings as $booking) {
            $partner = $booking->car?->partner;
            
            if ($partner) {
                $booking->commission_type = $partner->commission_type;
                $booking->commission_value = $partner->commission_value;
                $booking->commission_base = $partner->commission_base;
                
                if ($partner->commission_type === 'percentage') {
                    $booking->commission_rate_applied = $partner->commission_value;
                } else {
                    $booking->commission_rate_applied = $partner->commission_value;
                }
                
                $booking->save();
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Commission settings backfilled successfully!');
    }
}