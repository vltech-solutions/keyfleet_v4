<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use TracksUserAttribution;

    protected $fillable = [
        'source',
        'company_id'
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
