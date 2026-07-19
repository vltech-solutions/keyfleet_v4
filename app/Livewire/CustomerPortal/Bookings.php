<?php

namespace App\Livewire\CustomerPortal;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class Bookings extends Component
{
    public $customer;
    public $repeatToken;
    public $statusFilter = 'all';

    public function mount($repeatToken)
    {
        $tenantId = session('tenant_id');
        
        if (!$tenantId) {
            abort(404, 'Company not found');
        }

        $this->customer = Customer::where('repeat_token', $repeatToken)
            ->where('company_id', $tenantId)
            ->firstOrFail();

        $this->repeatToken = $repeatToken;
    }

    public function downloadInvoice($bookingId)
    {
        $booking = $this->customer->bookings()->with('car')->findOrFail($bookingId);
        $company = Company::findOrFail($booking->company_id);
        
        $invoiceBlade = explode('.', $company->invoice_template)[0] ?? 'default';
        
        $pdf = Pdf::loadView('invoice.' . $invoiceBlade, [
            'invoiceData' => ['company' => $company, 'booking' => $booking]
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "invoice-{$booking->id}.pdf");
    }

    public function render()
    {
        $query = $this->customer->bookings()->with(['car', 'car.images', 'reservation']);
        
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        $bookings = $query->orderBy('created_at', 'desc')->get();

        return view('livewire.customer-portal.customer-bookings', [
            'customer' => $this->customer,
            'bookings' => $bookings,
            'repeatToken' => $this->repeatToken,
        ])->layout('livewire.customer-portal.customer-layout', [
            'customer' => $this->customer,
            'repeatToken' => $this->repeatToken,
        ]);
    }
}