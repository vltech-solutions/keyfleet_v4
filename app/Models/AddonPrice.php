<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AddonPrice extends Model
{
    protected $fillable = [
        'addon_id',
        'billing_cycle',
        'price',
        'discount_percentage'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'integer'
    ];

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function addonSubscriptions(): HasMany
    {
        return $this->hasMany(AddonSubscription::class);
    }

    public function getCalculatedPriceAttribute(): float
    {
        $months = match ($this->billing_cycle) {
            'monthly' => 1,
            '3months' => 3,
            '6months' => 6,
            'annually' => 12,
            default => 1,
        };

        $totalBase = $this->price * $months;
        $discount = $totalBase * ($this->discount_percentage / 100);

        return (float) ($totalBase - $discount);
    }
}