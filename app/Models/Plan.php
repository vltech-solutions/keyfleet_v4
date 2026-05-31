<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany; 

class Plan extends Model
{
   protected $fillable = ['name', 'car_limit', 'price', 'billing_cycle', 'is_active','referral_reward_days'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function prices()
    {
        return $this->hasMany(PlanPrice::class);
    }

    
}
