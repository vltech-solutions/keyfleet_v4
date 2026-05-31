<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reservation;
use App\Models\Booking;
use App\Models\Company;
use Filament\Facades\Filament;

class TraceBooking extends Component
{
    public $reservation_number;
    public $showInvoiceModal = false;
    public $invoiceData = [];

    protected $rules = [
        'reservation_number' => 'required|numeric|exists:reservations,reservation_number',
    ];

    public function submit()
    {
        // $this->validate();
        // dd($this->reservation_number);

        $reservation = Reservation::where('reservation_number', $this->reservation_number)->first();

        if (! $reservation) {
            session()->flash('danger', 'Reservation not found.');
            return;
        }

        if ($reservation->status !== 'approved') {
            if ($reservation->status === 'pending') {
                session()->flash('warning', 'This reservation is still pending approval.');
            } elseif ($reservation->status === 'declined') {
                $declinedAt = $reservation->updated_at
                    ? $reservation->updated_at->format('M d, Y h:i A')
                    : 'Unknown date';

                $reason = $reservation->decline_reason ?? 'No reason provided';

                session()->flash('danger', "This reservation was declined on {$declinedAt}. Reason: {$reason}");
            } else {
                session()->flash('error', 'This reservation is not approved. Current status: ' . ucfirst($reservation->status));
            }
            return;
        }

        if (! $reservation->booking_id) {
            session()->flash('error', 'This reservation has no booking attached.');
            return;
        }

        $booking = Booking::with('car')->find($reservation->booking_id);

        $company = Company::find($booking->company_id);
        // dd($company);

        $invoiceBlade = explode('.', $company->invoice_template)[0];
        $this->invoiceData = [
            'company' => $company,
            'booking' => $booking,
            'invoiceBlade' => $invoiceBlade,
            'fromTrace' => true
        ];

        $this->showInvoiceModal = true;
        // dd($this->showInvoiceModal);
    }

    public function render()
    {
        return view('livewire.trace-booking');
    }
}
