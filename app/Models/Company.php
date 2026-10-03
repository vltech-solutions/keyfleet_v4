<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Concerns\TracksUserAttribution;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Company extends Model implements HasAvatar
{
    use TracksUserAttribution;

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
        'offer_driver_service',
        'reservation_fee_amount', // Add this
        'is_reservation_fee_enabled', // Add this
    ];

    protected $casts = [
        'enabled_requirements' => 'array',
        'delivery_methods' => 'array',
        'reservation_fee_amount' => 'decimal:2',
        'is_reservation_fee_enabled' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
        return $this->activeSubscription()?->plan;
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->with(['plan', 'planPrice.plan'])
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->latest('starts_at')
            ->first();
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
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

    public function hasProOrMasterSubscription(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription || ! $subscription->planPrice || ! $subscription->plan) {
            return false;
        }

        $isNotExpired = now()->lte($subscription->ends_at);
        $isPaid = $subscription->planPrice->price > 0;

        // Check if plan is DrivePro (15 cars) or FleetMaster (35 cars)
        $planName = $subscription->plan->name ?? '';
        $isProOrMaster = in_array($planName, ['DrivePro', 'FleetMaster']);

        return $isNotExpired && $isPaid && $isProOrMaster;
    }

    public function carLimitReached()
    {
        return ! $this->canCreateMoreCars();
    }

    public function userLimit(): ?int
    {
        return $this->plan()?->user_limit;
    }

    public function activeUserCount(): int
    {
        return $this->users()->where('is_active', true)->count();
    }

    public function remainingUserSeats(): ?int
    {
        $limit = $this->userLimit();
        return $limit === null ? null : max($limit - $this->activeUserCount(), 0);
    }

    public function hasMultiUserAccess(): bool
    {
        if (! $this->activeSubscription()) {
            return false;
        }

        $limit = $this->userLimit();
        return $limit === null || $limit > 1;
    }

    public function canCreateMoreUsers(): bool
    {
        $limit = $this->userLimit();
        return $this->hasMultiUserAccess()
            && ($limit === null || $this->activeUserCount() < $limit);
    }

    public function carLimit(): ?int
    {
        return $this->plan()?->car_limit;
    }

    public function currentCarCount(): int
    {
        return $this->cars()->count();
    }

    public function remainingCarSlots(): ?int
    {
        $limit = $this->carLimit();
        return $limit === null || $limit <= 0 ? null : max($limit - $this->currentCarCount(), 0);
    }

    public function canCreateMoreCars(): bool
    {
        $limit = $this->carLimit();
        return $limit === null || $limit <= 0 || $this->currentCarCount() < $limit;
    }

    public function subscriptionDaysLeft(): ?int
    {
        $endsAt = $this->subscription?->ends_at;

        if (!$endsAt) {
            return null;
        }

        return now()->diffInDays($endsAt, false); // returns negative if already expired
    }

    /**
     * Get the reservation fee for a booking
     * 
     * @param float $totalAmount The total booking amount
     * @return float
     */
    public function getReservationFee($totalAmount = 0)
    {
        if (!$this->is_reservation_fee_enabled) {
            return 0;
        }

        return $this->reservation_fee_amount ?? 0;
    }

    /**
     * Check if reservation fee is enabled
     * 
     * @return bool
     */
    public function isReservationFeeEnabled()
    {
        return $this->is_reservation_fee_enabled && $this->reservation_fee_amount > 0;
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
            $code = session('ref');
            $agent = $code ? Agent::with('program')->where('referral_code', $code)->first() : null;

            if ($agent?->canReferAt()) {
                CompanyReferral::create([
                    'referrer_user_id' => $agent->user_id,
                    'agent_id' => $agent->id,
                    'agent_program_id' => $agent->agent_program_id,
                    'referred_company_id' => $company->id,
                    'referral_code' => $code,
                    'program_type' => CompanyReferral::PROGRAM_AGENT,
                    'referred_at' => now(),
                ]);
            } elseif ($company->referred_by_company_id) {
                CompanyReferral::create([
                    'referrer_company_id' => $company->referred_by_company_id,
                    'referred_company_id' => $company->id,
                    'referral_code' => $code,
                    'program_type' => CompanyReferral::PROGRAM_CUSTOMER,
                    'referred_at' => now(),
                ]);
            }

            session()->forget('ref');

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
