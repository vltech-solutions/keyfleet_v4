<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Addon extends Model
{
    protected $fillable = [
        'name',
        'slug',
        // 'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(AddonPrice::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AddonSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}