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
        'source_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'datetime_declined' => 'datetime',
        'with_driver' => 'boolean',
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

    
}
