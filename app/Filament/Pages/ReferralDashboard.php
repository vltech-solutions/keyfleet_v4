<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Company;
use App\Models\Plan;
use Filament\Facades\Filament;

class ReferralDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static string $view = 'filament.pages.referral-dashboard';
    protected static ?string $title = 'My Referral Program';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getCompany(): Company
    {
        // return auth()->user()->company;
        return Company::find(Filament::getTenant()->id);
    }

    public function getPlansProperty()
    {
        return Plan::where('is_active', true)->where('referral_reward_days', '>', 0)->orderBy('referral_reward_days')->get();
    }

    public function getStats(): array
    {
        $company = $this->getCompany();

        $referrals = $company->referralsMade;

        $company = Company::find(Filament::getTenant()?->id);
        $hasActivePaidSubscription = $company->hasActivePaidSubscription();
        // dd($hasActivePaidSubscription);
        return [
            'hasActivePaidSubscription' => $hasActivePaidSubscription,
            'referral_code' => $company->referral_code,
            'plans' => $this->getPlansProperty(),
            'total_referrals' => $referrals->count(),
            'converted' => $referrals->where('is_converted', true)->count(),
            'total_reward_days' => $referrals->where('reward_given', true)->sum(function ($r) {
                return optional($r->referrer->subscription->plan)->referral_reward_days ?? 0;
            }),
        ];
    }
}
