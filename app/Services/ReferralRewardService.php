<?php

namespace App\Services;

use App\Models\CompanyReferral;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReferralRewardService
{
    public static function handleConversion(Subscription $subscription): void
    {
        $company = $subscription->company;

        $referral = CompanyReferral::where('referred_company_id', $company->id)
            ->where('is_converted', false)
            ->first();

        if (! $referral) return;

        DB::transaction(function () use ($referral) {
            $referrerCompany = $referral->referrer;
            $referrerSubscription = $referrerCompany->subscription;

            if (! $referrerSubscription) return;

            $plan = $referrerSubscription->plan;
            $rewardDays = $plan->referral_reward_days ?? 15;

            $currentEnd = Carbon::parse($referrerSubscription->ends_at);
            $referrerSubscription->update([
                'ends_at' => $currentEnd->addDays($rewardDays),
                'referral_bonus_days' => $rewardDays
            ]);

            $referral->update([
                'is_converted' => true,
                'reward_given' => true,
            ]);
        });
    }
}