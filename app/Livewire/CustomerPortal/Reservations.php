<?php

namespace App\Livewire\CustomerPortal;

use App\Models\Company;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Reservation;
use App\Services\SemaphoreService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Reservations extends Component
{
    public $customer;
    public $repeatToken;
    public $statusFilter = 'all';
    public $tenantId;
    public $company;
    
    // Cancellation modal properties
    public $showCancelModal = false;
    public $cancellingReservationId = null;
    public $cancellationReason = '';

    // Receipt modal properties
    public $showReceiptModal = false;
    public $receiptImageUrl = '';
    public $receiptReservationNumber = '';

    public function mount($repeatToken)
    {
        $tenantId = session('tenant_id');
        $this->tenantId = $tenantId;

        $companyInfo = Company::where('id', $tenantId)->first();
        $this->company = $companyInfo;
        
        if (!$tenantId) {
            abort(404, 'Company not found');
        }

        $this->customer = Customer::where('repeat_token', $repeatToken)
            ->where('company_id', $tenantId)
            ->firstOrFail();

        $this->repeatToken = $repeatToken;
    }

    public function openCancelModal($reservationId)
    {
        $this->cancellingReservationId = $reservationId;
        $this->cancellationReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->cancellingReservationId = null;
        $this->cancellationReason = '';
    }

    public function openReceiptModal($reservationId)
    {
        $reservation = Reservation::where('id', $reservationId)
            ->where('customer_id', $this->customer->id)
            ->firstOrFail();

        if ($reservation->reservation_fee_receipt) {
            $this->receiptImageUrl = Storage::disk('s3')->temporaryUrl(
                $reservation->reservation_fee_receipt, 
                now()->addMinutes(15)
            );
            $this->receiptReservationNumber = $reservation->reservation_number;
            $this->showReceiptModal = true;
        }
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->receiptImageUrl = '';
        $this->receiptReservationNumber = '';
    }

    public function confirmCancelReservation()
    {
        $this->validate([
            'cancellationReason' => 'required|min:5|max:500',
        ]);

        try {
            DB::beginTransaction();

            $reservation = Reservation::where('id', $this->cancellingReservationId)
                ->where('customer_id', $this->customer->id)
                ->whereNull('booking_id')
                ->whereNull('datetime_declined')
                ->whereNull('datetime_cancelled')
                ->firstOrFail();

            $reservation->update([
                'datetime_cancelled' => now(),
                'cancellation_reason' => $this->cancellationReason,
            ]);

            DB::commit();

            // Send SMS notification
            $message = "Reservation #".$reservation->reservation_number." cancelled. Reason: ".$this->cancellationReason;
            $notifNumber = $this->company?->notif_contact;

            if (env('SMS_ENABLED', false) && !empty($notifNumber)) {
                try {
                    $response = SemaphoreService::send($notifNumber, $message);
                } catch (\Throwable $e) {
                    \Log::warning('Company SMS sending failed', [
                        'number' => $notifNumber,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->closeCancelModal();

            Notification::make()
                ->title('Reservation Cancelled')
                ->body("Your reservation #{$reservation->reservation_number} has been cancelled.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Error!')
                ->body('There was a problem cancelling your reservation. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        $query = $this->customer->reservations()
            ->with(['car', 'car.carType', 'car.images']);

        if ($this->statusFilter !== 'all') {
            $query->when($this->statusFilter === 'pending', function ($q) {
                return $q->whereNull('booking_id')
                    ->whereNull('datetime_declined')
                    ->whereNull('datetime_cancelled');
            })->when($this->statusFilter === 'approved', function ($q) {
                return $q->whereNotNull('booking_id');
            })->when($this->statusFilter === 'declined', function ($q) {
                return $q->whereNotNull('datetime_declined');
            })->when($this->statusFilter === 'cancelled', function ($q) {
                return $q->whereNotNull('datetime_cancelled');
            });
        }

        $reservations = $query->orderBy('created_at', 'desc')->get();

        return view('livewire.customer-portal.customer-reservations', [
            'customer' => $this->customer,
            'reservations' => $reservations,
            'repeatToken' => $this->repeatToken,
        ])->layout('livewire.customer-portal.customer-layout', [
            'customer' => $this->customer,
            'repeatToken' => $this->repeatToken,
        ]);
    }
}