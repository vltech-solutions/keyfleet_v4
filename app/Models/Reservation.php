<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
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
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'datetime_declined' => 'datetime',
        'with_driver' => 'boolean',
        'reservation_fee' => 'decimal:2',
    ];

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
}