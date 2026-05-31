<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    protected $fillable = [
        'customer_name',
        'address',
        'contact_number',
        'email',
        'facebook_name',
        'repeat_token',
        'company_id'
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)
                    ->where('status', 'approved');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requirements(): HasMany
    {
        return $this->HasMany(CustomerRequirement::class);
    }

    protected static function booted()
    {
        static::creating(function ($customer) {
            if (Filament::getTenant()) {
                $customer->company_id = Filament::getTenant()->id;
            }

            if (empty($customer->repeat_token)) {
                $customer->repeat_token = Str::random(60);
            }
        });

        // Cascade delete requirements + files
        static::deleting(function ($customer) {
            foreach ($customer->requirements as $requirement) {
                if ($requirement->path && Storage::disk('s3')->exists($requirement->path)) {
                    Storage::disk('s3')->delete($requirement->path);
                }
                $requirement->delete();
            }
        });

    }
}
