<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\CompanyReferral;
use App\Models\Subscription;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgentCommissionService
{
    public function createForSubscription(Subscription $subscription): ?AgentCommission
    {
        if ($subscription->agentCommission()->exists()) {
            return $subscription->agentCommission;
        }

        $subscription->loadMissing('planPrice');
        $earnedAt = $subscription->paid_at;

        if (! $earnedAt || ! $subscription->planPrice || (float) $subscription->planPrice->price <= 0) {
            return null;
        }

        if ($this->decimalToMinor((string) $subscription->total_due) <= 0) {
            return null;
        }

        try {
            return DB::transaction(function () use ($subscription, $earnedAt): ?AgentCommission {
                $lockedSubscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
                $existing = AgentCommission::query()->where('subscription_id', $subscription->id)->first();
                if ($existing) {
                    return $existing;
                }

                $amount = $this->commissionableAmount($lockedSubscription);
                if ($this->decimalToMinor($amount) <= 0) {
                    return null;
                }

                $referral = CompanyReferral::query()
                    ->where('referred_company_id', $lockedSubscription->company_id)
                    ->where('program_type', CompanyReferral::PROGRAM_AGENT)
                    ->lockForUpdate()
                    ->first();

                if (! $referral || $referral->referred_at?->gt($earnedAt)) {
                    return null;
                }

                $agent = Agent::find($referral->agent_id);
                $program = $referral->agentProgram;
                if (! $agent || $agent->status !== Agent::STATUS_ACTIVE || ! $program) {
                    return null;
                }

                if (! $referral->commission_started_at) {
                    $referral->forceFill([
                        'qualified_at' => $earnedAt,
                        'is_converted' => true,
                        'commission_started_at' => $earnedAt,
                        'commission_ends_at' => $earnedAt->copy()->addMonthsNoOverflow($program->commission_duration_months),
                        'commission_duration_months' => $program->commission_duration_months,
                    ])->save();
                }

                if ($earnedAt->lt($referral->commission_started_at) || ! $earnedAt->lt($referral->commission_ends_at)) {
                    return null;
                }

                $commissionAmount = $this->calculateCommission($amount, (string) $program->commission_rate);

                return AgentCommission::create([
                    'agent_id' => $agent->id,
                    'company_id' => $lockedSubscription->company_id,
                    'company_referral_id' => $referral->id,
                    'subscription_id' => $lockedSubscription->id,
                    'agent_program_id' => $program->id,
                    'commissionable_amount' => $amount,
                    'commission_rate' => $program->commission_rate,
                    'commission_amount' => $commissionAmount,
                    'holding_period_days' => $program->holding_period_days,
                    'status' => AgentCommission::STATUS_PENDING,
                    'earned_at' => $earnedAt,
                    'payable_at' => $earnedAt->copy()->addDays($program->holding_period_days),
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException) {
            return AgentCommission::query()->where('subscription_id', $subscription->id)->first();
        }
    }

    public function promotePayable(): int
    {
        return AgentCommission::query()
            ->where('status', AgentCommission::STATUS_PENDING)
            ->where('payable_at', '<=', now())
            ->update(['status' => AgentCommission::STATUS_PAYABLE, 'updated_at' => now()]);
    }

    public function reverseForSubscription(Subscription $subscription, string $reason): ?AgentCommission
    {
        return DB::transaction(function () use ($subscription, $reason): ?AgentCommission {
            $commission = AgentCommission::query()->where('subscription_id', $subscription->id)->lockForUpdate()->first();
            if (! $commission || $commission->status === AgentCommission::STATUS_REVERSED) {
                return $commission;
            }

            $commission->update([
                'status' => AgentCommission::STATUS_REVERSED,
                'reversed_at' => now(),
                'reversal_reason' => $commission->status === AgentCommission::STATUS_PAID
                    ? $reason.' The paid amount requires recovery; original payout attribution is retained.'
                    : $reason,
            ]);

            return $commission;
        }, 3);
    }

    public function reverse(AgentCommission $commission, string $reason): AgentCommission
    {
        if (blank(trim($reason))) {
            throw ValidationException::withMessages(['reversal_reason' => 'A reversal reason is required.']);
        }

        return $this->reverseForSubscription($commission->subscription, trim($reason));
    }

    private function calculateCommission(string $amount, string $rate): string
    {
        $amountMinor = $this->decimalToMinor($amount);
        $rateUnits = $this->decimalToScaledInteger($rate, 4);
        $commissionMinor = intdiv(($amountMinor * $rateUnits) + 500000, 1000000);

        return sprintf('%d.%02d', intdiv($commissionMinor, 100), $commissionMinor % 100);
    }

    private function commissionableAmount(Subscription $subscription): string
    {
        $totalMinor = $this->decimalToMinor((string) $subscription->total_due);
        $addonMinor = $subscription->addonSubscriptions()
            ->pluck('total_paid')
            ->sum(fn ($amount): int => $this->decimalToMinor((string) $amount));
        $processingFeeMinor = $this->decimalToMinor((string) ($subscription->processing_fee ?? '0'));
        $baseMinor = max($totalMinor - $addonMinor - $processingFeeMinor, 0);

        return sprintf('%d.%02d', intdiv($baseMinor, 100), $baseMinor % 100);
    }

    private function decimalToMinor(string $value): int
    {
        return $this->decimalToScaledInteger($value, 2);
    }

    private function decimalToScaledInteger(string $value, int $scale): int
    {
        $normalized = trim($value);
        $negative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '+-');
        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $fraction = substr(str_pad($fraction, $scale, '0'), 0, $scale);
        $result = ((int) ($whole ?: 0) * (10 ** $scale)) + (int) $fraction;

        return $negative ? -$result : $result;
    }
}
