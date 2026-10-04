<?php

namespace Tests\Feature;

use App\Filament\Agent\Resources\CommissionResource;
use App\Filament\Agent\Resources\PayoutResource;
use App\Filament\Agent\Resources\ReferralResource;
use App\Models\Addon;
use App\Models\AddonPrice;
use App\Models\AddonSubscription;
use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\AgentProgram;
use App\Models\Company;
use App\Models\CompanyReferral;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AgentCommissionService;
use App\Services\AgentPayoutService;
use App\Services\ReferralRewardService;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AgentProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_referrals_remain_separate_and_receive_free_days(): void
    {
        [$referrer, $referrerSubscription] = $this->companyWithSubscription(100, now(), 9);
        [$referred, $payment] = $this->companyWithSubscription(500, now());
        CompanyReferral::create([
            'referrer_company_id' => $referrer->id,
            'referred_company_id' => $referred->id,
            'program_type' => CompanyReferral::PROGRAM_CUSTOMER,
            'referred_at' => now()->subDay(),
        ]);

        ReferralRewardService::handleConversion($payment);

        $this->assertTrue(CompanyReferral::where('referred_company_id', $referred->id)->value('reward_given'));
        $this->assertSame(9, $referrerSubscription->fresh()->referral_bonus_days);
        $this->assertDatabaseCount('agent_commissions', 0);
    }

    public function test_configured_terms_drive_snapshotted_commission_and_first_payment_window(): void
    {
        [$agent, $company, $referral] = $this->agentReferral(rate: '12.5000', months: 5, holdingDays: 11);
        [, $payment] = $this->companyWithSubscription(800, now(), 0, $company);

        $commission = app(AgentCommissionService::class)->createForSubscription($payment);

        $this->assertSame('800.00', $commission->commissionable_amount);
        $this->assertSame('12.5000', $commission->commission_rate);
        $this->assertSame('100.00', $commission->commission_amount);
        $this->assertSame(11, $commission->holding_period_days);
        $this->assertTrue($commission->payable_at->equalTo($payment->paid_at->copy()->addDays(11)));
        $this->assertSame(5, $referral->fresh()->commission_duration_months);
        $this->assertTrue($referral->fresh()->commission_ends_at->equalTo($payment->paid_at->copy()->addMonthsNoOverflow(5)));
        $this->assertSame($agent->id, $commission->agent_id);

        ReferralRewardService::handleConversion($payment);
        $this->assertFalse($referral->fresh()->reward_given);
    }

    public function test_customer_becoming_agent_does_not_reclassify_history_and_new_links_use_agent_program(): void
    {
        [$customerCompany] = $this->companyWithSubscription(100, now(), 10);
        $historicalReferred = Company::create(['name' => 'Historical '.uniqid(), 'slug' => 'historical-'.uniqid(), 'invoice_template' => 'invoice1.png']);
        $historical = CompanyReferral::create(['referrer_company_id' => $customerCompany->id, 'referred_company_id' => $historicalReferred->id, 'program_type' => CompanyReferral::PROGRAM_CUSTOMER, 'referred_at' => now()->subMonth()]);
        $program = AgentProgram::create(['name' => 'New Agent Program', 'commission_rate' => 8, 'commission_duration_months' => 4, 'holding_period_days' => 2, 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $customerCompany->id, 'is_active' => true]);
        $agent = Agent::create(['user_id' => $user->id, 'agent_program_id' => $program->id, 'referral_code' => 'KF-CUSTOMER', 'status' => Agent::STATUS_ACTIVE, 'activated_at' => now()]);

        session(['ref' => $agent->referral_code]);
        $newCompany = Company::create(['name' => 'New Referral '.uniqid(), 'slug' => 'new-referral-'.uniqid(), 'invoice_template' => 'invoice1.png']);

        $this->assertSame(CompanyReferral::PROGRAM_CUSTOMER, $historical->fresh()->program_type);
        $this->assertSame(CompanyReferral::PROGRAM_AGENT, CompanyReferral::where('referred_company_id', $newCompany->id)->value('program_type'));
        $this->assertSame($agent->id, CompanyReferral::where('referred_company_id', $newCompany->id)->value('agent_id'));
    }

    public function test_inactive_agent_code_does_not_create_an_agent_referral(): void
    {
        [$agent] = $this->agentReferral();
        $agent->update(['status' => Agent::STATUS_SUSPENDED]);
        session(['ref' => $agent->referral_code]);
        $company = Company::create(['name' => 'No Attribution '.uniqid(), 'slug' => 'no-attribution-'.uniqid(), 'invoice_template' => 'invoice1.png']);
        $this->assertDatabaseMissing('company_referrals', ['referred_company_id' => $company->id, 'program_type' => CompanyReferral::PROGRAM_AGENT]);
    }

    public function test_failed_free_addon_and_duplicate_payments_do_not_create_extra_commission(): void
    {
        [, $company] = $this->agentReferral();
        [, $failed] = $this->companyWithSubscription(500, null, 0, $company);
        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($failed));

        [, $trial] = $this->companyWithSubscription(0, now(), 0, $company);
        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($trial));

        [, $paid] = $this->companyWithSubscription(500, now(), 0, $company);
        $first = app(AgentCommissionService::class)->createForSubscription($paid);
        $second = app(AgentCommissionService::class)->createForSubscription($paid);
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('agent_commissions', 1);
    }

    public function test_actual_discounted_base_amount_is_used_and_addons_are_excluded(): void
    {
        [, $company] = $this->agentReferral(rate: '10.0000');
        [, $payment] = $this->companyWithSubscription(750, now(), 0, $company, ['subtotal' => 1000, 'discount_amount' => 250]);
        $commission = app(AgentCommissionService::class)->createForSubscription($payment);

        $this->assertSame('750.00', $commission->commissionable_amount);
        $this->assertSame('75.00', $commission->commission_amount);
    }

    public function test_addons_and_processing_fees_are_removed_from_commissionable_revenue(): void
    {
        [, $company] = $this->agentReferral(rate: '10.0000');
        [, $payment] = $this->companyWithSubscription(1300, now(), 0, $company, ['subtotal' => 1300, 'processing_fee' => 100]);
        $addon = Addon::create(['name' => 'Premium Feature', 'slug' => 'premium-'.uniqid(), 'is_active' => true]);
        $addonPrice = AddonPrice::create(['addon_id' => $addon->id, 'billing_cycle' => 'monthly', 'price' => 200, 'discount_percentage' => 0]);
        AddonSubscription::create([
            'company_id' => $company->id, 'subscription_id' => $payment->id,
            'addon_id' => $addon->id, 'addon_price_id' => $addonPrice->id,
            'starts_at' => now(), 'ends_at' => now()->addMonth(), 'status' => 'active', 'total_paid' => 200,
        ]);

        $commission = app(AgentCommissionService::class)->createForSubscription($payment);
        $this->assertSame('1000.00', $commission->commissionable_amount);
        $this->assertSame('100.00', $commission->commission_amount);
    }

    public function test_upgrade_does_not_restart_window_and_outside_payment_is_rejected(): void
    {
        [, $company, $referral] = $this->agentReferral(months: 2);
        [, $first] = $this->companyWithSubscription(500, now()->subMonth(), 0, $company);
        app(AgentCommissionService::class)->createForSubscription($first);
        $started = $referral->fresh()->commission_started_at;

        [, $upgrade] = $this->companyWithSubscription(900, now(), 0, $company);
        app(AgentCommissionService::class)->createForSubscription($upgrade);
        $this->assertTrue($referral->fresh()->commission_started_at->equalTo($started));

        [, $outside] = $this->companyWithSubscription(900, $started->copy()->addMonths(2), 0, $company, [
            'starts_at' => $started->copy()->addMonths(2),
            'ends_at' => $started->copy()->addMonths(3),
        ]);
        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($outside));
    }

    public function test_annual_payment_fully_inside_window_uses_the_full_actual_base_amount(): void
    {
        [, $company] = $this->agentReferral(rate: '8.5000', months: 12);
        $start = now()->startOfDay();
        [, $payment] = $this->companyWithSubscription(9600, $start, 0, $company, [
            'starts_at' => $start,
            'ends_at' => $start->copy()->addYear(),
        ]);

        $commission = app(AgentCommissionService::class)->createForSubscription($payment);

        $this->assertSame('9600.00', $commission->actual_base_subscription_amount);
        $this->assertSame('9600.00', $commission->commissionable_amount);
        $this->assertSame('816.00', $commission->commission_amount);
        $this->assertSame($commission->total_coverage_days, $commission->eligible_coverage_days);
    }

    public function test_three_and_six_month_payments_fully_inside_window_use_the_full_base_amount(): void
    {
        foreach ([3, 6] as $months) {
            [, $company] = $this->agentReferral(
                rate: '10.0000',
                months: 12,
                code: 'KF-'.$months.'MONTHS',
            );
            $start = now()->startOfDay();
            $amount = $months * 1000;
            [, $payment] = $this->companyWithSubscription($amount, $start, 0, $company, [
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMonths($months),
            ]);

            $commission = app(AgentCommissionService::class)->createForSubscription($payment);

            $this->assertSame(number_format($amount, 2, '.', ''), $commission->commissionable_amount);
            $this->assertSame(number_format($amount * 0.10, 2, '.', ''), $commission->commission_amount);
        }
    }

    public function test_long_term_payment_crossing_window_is_prorated_by_exact_coverage_days(): void
    {
        [, $company, $referral] = $this->agentReferral(rate: '10.0000', months: 12);
        $windowStart = now()->startOfDay()->subMonths(6);
        $referral->update(['referred_at' => $windowStart->copy()->subDay()]);
        [, $firstPayment] = $this->companyWithSubscription(100, $windowStart, 0, $company, [
            'starts_at' => $windowStart,
            'ends_at' => $windowStart->copy()->addMonth(),
        ]);
        app(AgentCommissionService::class)->createForSubscription($firstPayment);

        $coverageStart = $windowStart->copy()->addMonths(6);
        $coverageEnd = $coverageStart->copy()->addYear();
        [, $annualPayment] = $this->companyWithSubscription(12000, $coverageStart, 0, $company, [
            'starts_at' => $coverageStart,
            'ends_at' => $coverageEnd,
        ]);

        $commission = app(AgentCommissionService::class)->createForSubscription($annualPayment);
        $eligibilityEnd = $referral->fresh()->commission_ends_at->startOfDay();
        $totalDays = (int) $coverageStart->diffInDays($coverageEnd);
        $eligibleDays = (int) $coverageStart->diffInDays($eligibilityEnd);
        $expectedBaseCents = intdiv((1200000 * $eligibleDays) + intdiv($totalDays, 2), $totalDays);
        $this->assertSame($totalDays, $commission->total_coverage_days);
        $this->assertSame($eligibleDays, $commission->eligible_coverage_days);
        $this->assertSame(sprintf('%d.%02d', intdiv($expectedBaseCents, 100), $expectedBaseCents % 100), $commission->commissionable_amount);
        $this->assertSame($eligibilityEnd->toDateString(), $commission->eligible_coverage_end->toDateString());
        $this->assertLessThan((float) $commission->actual_base_subscription_amount, (float) $commission->commissionable_amount);
    }

    public function test_partial_refund_records_a_proportional_reversal_without_deleting_original_commission(): void
    {
        [, $company] = $this->agentReferral(rate: '10.0000');
        [, $payment] = $this->companyWithSubscription(1000, now(), 0, $company);
        $commission = app(AgentCommissionService::class)->createForSubscription($payment);

        $payment->update(['refund_amount' => 500]);
        $commission->refresh();

        $this->assertSame(AgentCommission::STATUS_PARTIALLY_REVERSED, $commission->status);
        $this->assertSame('1000.00', $commission->commissionable_amount);
        $this->assertSame('100.00', $commission->commission_amount);
        $this->assertSame('500.00', $commission->reversed_base_amount);
        $this->assertSame('50.00', $commission->reversed_amount);
        $this->assertSame('50.00', $commission->netCommissionAmount());
        $this->assertDatabaseHas('agent_commissions', ['id' => $commission->id]);
    }

    public function test_program_must_be_available_when_a_new_commission_is_generated(): void
    {
        [$agent, $company] = $this->agentReferral();
        $agent->program->update(['is_active' => false]);
        [, $payment] = $this->companyWithSubscription(500, now(), 0, $company);

        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($payment));
        $this->assertDatabaseCount('agent_commissions', 0);
    }

    public function test_program_start_and_end_dates_are_respected_for_new_commissions(): void
    {
        [$futureAgent, $futureCompany] = $this->agentReferral(code: 'KF-FUTURE');
        $futureAgent->program->update(['starts_at' => now()->addDay()]);
        [, $futurePayment] = $this->companyWithSubscription(500, now(), 0, $futureCompany);
        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($futurePayment));

        [$expiredAgent, $expiredCompany] = $this->agentReferral(code: 'KF-EXPIRED');
        $expiredAgent->program->update(['ends_at' => now()->subDay()]);
        [, $expiredPayment] = $this->companyWithSubscription(500, now(), 0, $expiredCompany);
        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($expiredPayment));

        [$validAgent, $validCompany] = $this->agentReferral(code: 'KF-VALID');
        $validAgent->program->update(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        [, $validPayment] = $this->companyWithSubscription(500, now(), 0, $validCompany);
        $this->assertNotNull(app(AgentCommissionService::class)->createForSubscription($validPayment));
    }

    public function test_program_changes_do_not_mutate_historical_snapshots(): void
    {
        [$agent, $company] = $this->agentReferral(rate: '15.0000');
        [, $payment] = $this->companyWithSubscription(1000, now(), 0, $company);
        $commission = app(AgentCommissionService::class)->createForSubscription($payment);
        $agent->program->update(['commission_rate' => '7.0000']);

        $this->assertSame('15.0000', $commission->fresh()->commission_rate);
        $this->assertSame('150.00', $commission->fresh()->commission_amount);

        $newProgram = AgentProgram::create(['name' => 'Replacement', 'commission_rate' => 50, 'commission_duration_months' => 1, 'holding_period_days' => 0, 'is_active' => true]);
        $agent->update(['agent_program_id' => $newProgram->id]);
        [, $laterPayment] = $this->companyWithSubscription(1000, now()->addDay(), 0, $company);
        $laterCommission = app(AgentCommissionService::class)->createForSubscription($laterPayment);
        $this->assertSame('7.0000', $laterCommission->commission_rate);
        $this->assertSame($commission->agent_program_id, $laterCommission->agent_program_id);
    }

    public function test_refund_reverses_unpaid_commission_and_holding_period_promotes_it(): void
    {
        [, $company] = $this->agentReferral(holdingDays: 3);
        [, $payment] = $this->companyWithSubscription(500, now()->subDays(4), 0, $company);
        $commission = app(AgentCommissionService::class)->createForSubscription($payment);

        $this->assertSame(1, app(AgentCommissionService::class)->promotePayable());
        $this->assertSame(AgentCommission::STATUS_PAYABLE, $commission->fresh()->status);

        $payment->update(['refund_amount' => 500]);
        $this->assertSame(AgentCommission::STATUS_REVERSED, $commission->fresh()->status);
        $this->assertNotNull($commission->fresh()->reversal_reason);
    }

    public function test_only_payable_commissions_enter_one_payout_and_are_marked_paid(): void
    {
        [$agent, $company] = $this->agentReferral(holdingDays: 0);
        [, $payment] = $this->companyWithSubscription(500, now(), 0, $company);
        $commission = app(AgentCommissionService::class)->createForSubscription($payment);

        $this->expectException(ValidationException::class);
        app(AgentPayoutService::class)->create($agent, [$commission->id]);
    }

    public function test_payable_commission_cannot_enter_two_payouts(): void
    {
        [$agent, $company] = $this->agentReferral(holdingDays: 0);
        [, $payment] = $this->companyWithSubscription(500, now()->subMinute(), 0, $company);
        $commission = app(AgentCommissionService::class)->createForSubscription($payment);
        app(AgentCommissionService::class)->promotePayable();
        $payout = app(AgentPayoutService::class)->create($agent, [$commission->id]);
        $this->assertSame($commission->commission_amount, $payout->amount);

        try {
            app(AgentPayoutService::class)->create($agent, [$commission->id]);
            $this->fail('Duplicate payout inclusion was accepted.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('agent_payout_items', 1);
        }

        app(AgentPayoutService::class)->markPaid($payout);
        $this->assertSame(AgentCommission::STATUS_PAID, $commission->fresh()->status);
    }

    public function test_agent_panel_access_and_resource_queries_are_owner_scoped(): void
    {
        [$agent, $company] = $this->agentReferral();
        [$otherAgent, $otherCompany] = $this->agentReferral(code: 'KF-OTHER');
        [, $payment] = $this->companyWithSubscription(500, now(), 0, $company);
        [, $otherPayment] = $this->companyWithSubscription(500, now(), 0, $otherCompany);
        app(AgentCommissionService::class)->createForSubscription($payment);
        app(AgentCommissionService::class)->createForSubscription($otherPayment);
        $this->actingAs($agent->user);

        $this->assertTrue($agent->user->canAccessPanel(Panel::make()->id('agent')));
        $this->assertSame(1, ReferralResource::getEloquentQuery()->count());
        $this->assertSame(1, CommissionResource::getEloquentQuery()->count());
        $this->assertSame(0, PayoutResource::getEloquentQuery()->count());
        $this->assertFalse(User::factory()->create(['is_active' => true])->canAccessPanel(Panel::make()->id('agent')));
        $this->assertNotSame($agent->id, $otherAgent->id);
    }

    public function test_suspended_agent_cannot_generate_commission(): void
    {
        [$agent, $company] = $this->agentReferral();
        $agent->update(['status' => Agent::STATUS_SUSPENDED]);
        [, $payment] = $this->companyWithSubscription(500, now(), 0, $company);
        $this->assertNull(app(AgentCommissionService::class)->createForSubscription($payment));
    }

    private function agentReferral(string $rate = '15.0000', int $months = 12, int $holdingDays = 7, string $code = 'KF-AGENT'): array
    {
        $program = AgentProgram::create(['name' => 'Program '.uniqid(), 'commission_rate' => $rate, 'commission_duration_months' => $months, 'holding_period_days' => $holdingDays, 'is_active' => true]);
        $user = User::factory()->create(['is_active' => true]);
        $agent = Agent::create(['user_id' => $user->id, 'agent_program_id' => $program->id, 'referral_code' => $code, 'status' => Agent::STATUS_ACTIVE, 'activated_at' => now()->subYear()]);
        $company = Company::create(['name' => 'Referred '.uniqid(), 'slug' => 'referred-'.uniqid(), 'invoice_template' => 'invoice1.png']);
        $referral = CompanyReferral::create(['referrer_user_id' => $user->id, 'agent_id' => $agent->id, 'agent_program_id' => $program->id, 'referred_company_id' => $company->id, 'referral_code' => $code, 'program_type' => CompanyReferral::PROGRAM_AGENT, 'referred_at' => now()->subMonths(6)]);

        return [$agent, $company, $referral];
    }

    private function companyWithSubscription(float $amount, $paidAt, int $rewardDays = 0, ?Company $company = null, array $overrides = []): array
    {
        $company ??= Company::create(['name' => 'Company '.uniqid(), 'slug' => 'company-'.uniqid(), 'invoice_template' => 'invoice1.png']);
        $plan = Plan::create(['name' => 'Plan '.uniqid(), 'car_limit' => 10, 'is_active' => true, 'referral_reward_days' => $rewardDays]);
        $price = PlanPrice::create(['plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'price' => $amount]);
        $subscription = Subscription::create(array_merge([
            'company_id' => $company->id, 'plan_price_id' => $price->id,
            'starts_at' => now(), 'ends_at' => now()->addMonth(), 'subtotal' => $amount,
            'total_due' => $amount, 'paid_at' => $paidAt, 'refund_amount' => 0,
        ], $overrides));

        return [$company, $subscription];
    }
}
