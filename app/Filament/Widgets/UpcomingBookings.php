<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\AuthorizesTenantWidget;

use Filament\Widgets\Widget;
use App\Models\Booking;

class UpcomingBookings extends Widget
{
    use AuthorizesTenantWidget;

    protected static string $view = 'filament.widgets.upcoming-bookings';

    protected static ?int $sort = 4;

    public $upcomingBookings;

    public function mount() {
        $this->upcomingBookings = Booking::where('start_datetime', '>', now()) 
            ->where('status','approved')
            ->orderBy('start_datetime', 'asc') 
            ->with('car')
            ->take(3) 
            ->get();
    }
}
