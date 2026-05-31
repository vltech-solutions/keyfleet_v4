<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Booking;

class BookingStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.booking-summary-stats';
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
        'lg' => 2,
    ];

    public function getViewData(): array
    {
        $now = now();

        // Sum of receivables (balance) instead of total revenue
        $computeReceivables = fn($bookings) => $bookings->sum(function ($booking) {
            return floatval($booking->balance ?? 0);
        });

        // Fetch bookings per status
        $upcoming = Booking::with('car.partner')
            ->where('start_datetime', '>', $now)
            ->where('status','approved')
            ->get();

        $ongoing = Booking::with('car.partner')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now)
            ->where('status','approved')
            ->get();

        $finished = Booking::with('car.partner')
            ->where('end_datetime', '<', $now)
            ->where('status','approved')
            ->get();

        // Return data to the widget view
        return [
            'upcoming' => [
                'count' => $upcoming->count(),
                'total_receivables' => $computeReceivables($upcoming),
            ],
            'ongoing' => [
                'count' => $ongoing->count(),
                'total_receivables' => $computeReceivables($ongoing),
            ],
            'finished' => [
                'count' => $finished->count(),
                'total_receivables' => $computeReceivables($finished),
            ],
        ];
    }
}
