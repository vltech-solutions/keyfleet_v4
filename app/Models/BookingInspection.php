<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;

use Illuminate\Database\Eloquent\Model;

class BookingInspection extends Model
{
    use TracksUserAttribution;

    protected $fillable = [
        'booking_id',
        'type', // 'pre' or 'post'
        'odo', 
        'autosweep',
        'easytrip',
        'gas',
        'tires',
        'functions',
        'inspected_by',
        'customer_signature', // Add this
        'general_notes',      // Add this
        'signee_name',
    ];

    protected $casts = [
        'tires' => 'array',
        'functions' => 'array',
        'gas' => 'integer',
        'odo' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(InspectionItem::class, 'booking_inspection_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
