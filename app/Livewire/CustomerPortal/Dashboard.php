<?php

namespace App\Livewire\CustomerPortal;

use Livewire\Component;
use App\Models\Customer;

class Dashboard extends Component
{
    public $customer;
    public $repeatToken;

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

    public function render()
    {
        // Get all reservations
        $reservations = $this->customer->reservations()->with('car')->get();
        
        // Get all bookings
        $bookings = $this->customer->bookings()->with('car')->get();
        
        // Calculate stats from reservations
        $stats = [
            'total' => $reservations->count() + $bookings->count(),
            'pending' => $reservations->whereNull('booking_id')
                ->whereNull('datetime_declined')
                ->whereNull('datetime_cancelled')
                ->count(),
            'approved' => $reservations->whereNotNull('booking_id')->count() + $bookings->where('status', 'approved')->count(),
            'completed' => $bookings->where('status', 'completed')->count() + 
                          $bookings->where('status', 'approved')->filter(function($b) {
                              return $b->end_datetime && $b->end_datetime->isPast();
                          })->count(),
            'declined' => $reservations->whereNotNull('datetime_declined')->count(),
            'cancelled' => $reservations->whereNotNull('datetime_cancelled')->count(),
        ];

        // Get recent bookings (mix of reservations and bookings)
        $recentReservations = $reservations->sortByDesc('created_at')->take(5);
        $recentBookings = $bookings->sortByDesc('created_at')->take(5);
        
        // Merge and sort recent items
        $recentItems = $recentReservations->merge($recentBookings)
            ->sortByDesc('created_at')
            ->take(5);

        return view('livewire.customer-portal.customer-dashboard', [
            'customer' => $this->customer,
            'stats' => $stats,
            'recentItems' => $recentItems,
            'repeatToken' => $this->repeatToken,
        ])->layout('livewire.customer-portal.customer-layout', [
            'customer' => $this->customer,
            'repeatToken' => $this->repeatToken,
        ]);
    }
}