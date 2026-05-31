<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BookingDetailsForInspection extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public $booking)
    {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.booking-details-for-inspection');
    }
}
