<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddonSubscription extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id', 
        'addon_id',
        'addon_price_id',
        'starts_at',
        'ends_at',
        'status',
        'total_paid'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'total_paid' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function addonPrice(): BelongsTo
    {
        return $this->belongsTo(AddonPrice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('ends_at', '>=', now()->toDateString());
    }

    protected static function booted()
    {
        static::creating(function ($addonSub) {
            if (!$addonSub->ends_at && $addonSub->addon_price_id) {
                $price = AddonPrice::find($addonSub->addon_price_id);
                
                $months = match ($price->billing_cycle) {
                    'monthly' => 1,
                    '3_months' => 3,
                    '6_months' => 6,
                    'annual' => 12,
                    default => 1,
                };

                $addonSub->starts_at = $addonSub->starts_at ?? now();
                $addonSub->ends_at = \Carbon\Carbon::parse($addonSub->starts_at)->addMonths($months);
            }
        });
    }
}