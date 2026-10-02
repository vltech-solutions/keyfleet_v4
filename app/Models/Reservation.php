<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use TracksUserAttribution;

    use HasFactory;

    protected $fillable = [
        'customer_id',
        'start_date',
        'end_date',
        'destination',
        'pickup_option',
        'pickup_address',
        'return_address',
        'with_driver',
        'other_drivers',
        'datetime_declined',
        'decline_reason',
        'selected_car_id',
        'status',
        'reservation_number',
        'booking_id',
        'company_id',
        'source_id',
        'fund_type_id',
        'reservation_fee',
        'reservation_fee_receipt',
        'datetime_cancelled',
        'cancellation_reason'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'datetime_declined' => 'datetime',
        'datetime_cancelled' => 'datetime',
        'with_driver' => 'boolean',
        'reservation_fee' => 'decimal:2',
    ];

    /**
     * Get the current status of the reservation
     * - booking_id exists = approved
     * - datetime_declined exists = declined
     * - datetime_cancelled exists = cancelled
     * - default = pending
     */
    public function getStatusAttribute()
    {
        if ($this->datetime_cancelled) {
            return 'cancelled';
        }
        
        if ($this->datetime_declined) {
            return 'declined';
        }
        
        if ($this->booking_id) {
            return 'approved';
        }
        
        return 'pending';
    }

    /**
     * Get the status label with badge class
     */
    public function getStatusBadgeAttribute()
    {
        $status = $this->status;
        
        $classes = [
            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'declined' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        ];
        
        return $classes[$status] ?? 'bg-gray-100 text-gray-700';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'selected_car_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the fund type associated with this reservation
     */
    public function fundType(): BelongsTo
    {
        return $this->belongsTo(FundType::class, 'fund_type_id');
    }

    /**
     * Get the reservation fee for this booking
     */
    public function getReservationFee()
    {
        if (!$this->company || !$this->company->isReservationFeeEnabled()) {
            return 0;
        }

        return $this->company->reservation_fee_amount;
    }

    /**
     * Calculate total with reservation fee
     */
    public function getTotalWithReservationFee()
    {
        $total = $this->total_due ?? 0;
        $reservationFee = $this->getReservationFee();
        
        return $total + $reservationFee;
    }

    /**
     * Check if reservation has a receipt uploaded
     */
    public function hasReceipt()
    {
        return !empty($this->reservation_fee_receipt);
    }

    /**
     * Get the receipt URL
     */
    public function getReceiptUrl()
    {
        if (!$this->reservation_fee_receipt) {
            return null;
        }

        return $this->reservation_fee_receipt;
    }
    
    /**
     * Check if reservation is completed (past end date)
     */
    public function getIsCompletedAttribute()
    {
        if (!$this->end_date) {
            return false;
        }
        
        return $this->end_date->isPast() && $this->status === 'approved';
    }
}