<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Company extends Model implements HasAvatar
{
    protected $fillable = [
        'name',
        'slug',
        'avatar_url',
        'address',
        'contacts',
        'primary_color',
        'invoice_template',
        'advance_booking_form',
        'notif_contact',
        'enabled_requirements',
        'booking_form_dark_mode',
        'requirements_expiry_months',
        'delivery_methods',
        'offer_driver_service'
    ];

    protected $casts = [
        'enabled_requirements' => 'array',
        'delivery_methods' => 'array'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    public function carDocuments(): HasMany
    {
        return $this->hasMany(CarDocument::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function fundTypes(): HasMany
    {
        return $this->hasMany(FundType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partners::class);
    }

    public function checklistItems():  HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return ($this->avatar_url) ? Storage::url($this->avatar_url) : Storage::url('default-logo.jpg');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function plan()
    {
        return $this->subscription?->planPrice?->plan;
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->subscription;

        if (!$subscription) {
            return false;
        }

        $today = Carbon::today();
       
        return !($today > $subscription->ends_at);
    }

    public function hasActivePaidSubscription(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription || ! $subscription->planPrice) {
            return false;
        }

        $isNotExpired = now()->lte($subscription->ends_at);
        $isPaid = $subscription->planPrice->price > 0;

        return $isNotExpired && $isPaid;
    }

    public function hasActiveFreeSubscription(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription || ! $subscription->planPrice) {
            return false;
        }

        $isNotExpired = now()->lte($subscription->ends_at);
        $isFree = $subscription->planPrice->price == 0;
        
        return $isNotExpired && $isFree;
    }

    public function hasNonBasicPaidSubscription(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription || ! $subscription->planPrice) {
            return false;
        }

        $isNotExpired = now()->lte($subscription->ends_at);
        $isPaid = $subscription->planPrice->price > 0;

        $isBasicPlan = $subscription->plan->car_limit > 3;

        return $isNotExpired && $isPaid && $isBasicPlan;
    }

    public function carLimitReached()
    {
        $carLimit = $this->plan()?->car_limit ?? 0;
        return $carLimit > 0 && $this->cars()->count() >= $carLimit;
    }

    public function subscriptionDaysLeft(): ?int
    {
        $endsAt = $this->subscription?->ends_at;

        if (!$endsAt) {
            return null;
        }

        return now()->diffInDays($endsAt, false); // returns negative if already expired
    }

    protected static function booted(): void
    {
        static::creating(function ($company) {
            $company->referral_code = Str::slug($company->name);

            if (session()->has('ref')) {
                $referrer = Company::where('referral_code', session('ref'))->first();
                if ($referrer) {
                    $company->referred_by_company_id = $referrer->id;
                }
            }
        });

        static::created(function ($company) {
            if ($company->referred_by_company_id) {
                CompanyReferral::create([
                    'referrer_company_id' => $company->referred_by_company_id,
                    'referred_company_id' => $company->id,
                ]);
            }

            // add the default funds
            FundType::create([
                'name' => "Partner's Fund",
                'balance' => 0,
                'company_id' => $company->id
            ]);

            FundType::create([
                'name' => "Income",
                'balance' => 0,
                'company_id' => $company->id
            ]);
            
        });
    }

    public function referralsMade()
    {
        return $this->hasMany(CompanyReferral::class, 'referrer_company_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(Company::class, 'referred_by_company_id');
    }

    public function website()
    {
        return $this->hasOne(CompanyWebsite::class);
    }

    // add ons
    public function activeAddons()
    {
        return $this->hasMany(AddonSubscription::class)
                    ->where('ends_at', '>=', now())
                    ->where('status', 'active');
    }

    public function hasAddon($slug)
    {
        return $this->activeAddons()->whereHas('addon', function($q) use ($slug) {
            $q->where('slug', $slug);
        })->exists();
    }
}
