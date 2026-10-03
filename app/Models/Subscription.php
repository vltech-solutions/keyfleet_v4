<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'auto_renew',
        'plan_price_id',
        'referral_bonus_days',
        'voucher_id',
        'voucher_code',
        'refund_amount',
        'discount_amount',
        'processing_fee',
        'subtotal',
        'total_due',
        'reminder_sent_after3d',
        'reminder_sent_0d',
        'reminder_sent_1d',
        'reminder_sent_3d',
        'reminder_sent_7d',
        'payment_source',
        'paymongo_fee',
        'paid_at',
        'net_amount',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function planPrice()
    {
        return $this->belongsTo(PlanPrice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function addonSubscriptions()
    {
        return $this->hasMany(AddonSubscription::class, 'subscription_id');
    }

    public function agentCommission()
    {
        return $this->hasOne(AgentCommission::class);
    }

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'paid_at' => 'datetime',
        'refund_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_due' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($subscription) {
            if ($subscription->plan_price_id) {
                $planPrice = PlanPrice::find($subscription->plan_price_id);
                $subscription->plan_id = $planPrice ? $planPrice->plan_id : null;
            }
        });

        static::updated(function (Subscription $subscription): void {
            if ($subscription->wasChanged('refund_amount') && (float) $subscription->refund_amount > 0) {
                app(\App\Services\AgentCommissionService::class)->reverseForSubscription(
                    $subscription,
                    'Source subscription payment was refunded or reversed.',
                );
            }
        });
    }
}
