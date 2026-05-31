<?php

namespace App\Actions;

use App\Models\Booking;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateInvoice
{
    use AsAction;

    public function handle(Booking $booking)
    {
        $pdf = Pdf::loadView('invoice', ['booking' => $booking]);
        $pdf->download('invoice.pdf'); 
    }
}