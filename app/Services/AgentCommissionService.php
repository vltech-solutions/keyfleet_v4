<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\CompanyReferral;
use App\Models\Subscription;
use Carbon\CarbonInterface;
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

                $lockedSubscription->loadMissing(['planPrice', 'addonSubscriptions']);
                $actualBaseAmount = $this->actualBaseSubscriptionAmount($lockedSubscription);
                if ($this->decimalToMinor($actualBaseAmount) <= 0) {
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
                if (! $agent || $agent->status !== Agent::STATUS_ACTIVE || ! $program || ! $program->isAvailableAt($earnedAt)) {
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

                $coverage = $this->eligibleCoverage(
                    $lockedSubscription->starts_at,
                    $lockedSubscription->ends_at,
                    $referral->commission_started_at,
                    $referral->commission_ends_at,
                );

                if ($coverage === null) {
                    return null;
                }

                $commissionableAmount = $this->prorateAmount(
                    $actualBaseAmount,
                    $coverage['eligible_days'],
                    $coverage['total_days'],
                );
                if ($this->decimalToMinor($commissionableAmount) <= 0) {
                    return null;
                }

                $commissionAmount = $this->calculateCommission($commissionableAmount, (string) $program->commission_rate);

                return AgentCommission::create([
                    'agent_id' => $agent->id,
                    'company_id' => $lockedSubscription->company_id,
                    'company_referral_id' => $referral->id,
                    'subscription_id' => $lockedSubscription->id,
                    'agent_program_id' => $program->id,
                    'actual_base_subscription_amount' => $actualBaseAmount,
                    'payment_coverage_start' => $coverage['payment_start'],
                    'payment_coverage_end' => $coverage['payment_end'],
                    'eligible_coverage_start' => $coverage['eligible_start'],
                    'eligible_coverage_end' => $coverage['eligible_end'],
                    'total_coverage_days' => $coverage['total_days'],
                    'eligible_coverage_days' => $coverage['eligible_days'],
                    'commissionable_amount' => $commissionableAmount,
                    'commission_rate' => $program->commission_rate,
                    'commission_duration_months' => $program->commission_duration_months,
                    'commission_amount' => $commissionAmount,
                    'reversed_base_amount' => '0.00',
                    'reversed_amount' => '0.00',
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
            if (! $commission) {
                return null;
            }

            $subscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
            $refundedBaseMinor = min(
                $this->decimalToMinor((string) $subscription->refund_amount),
                $this->decimalToMinor((string) ($commission->actual_base_subscription_amount ?? $commission->commissionable_amount)),
            );
            $eligibleRefundMinor = $this->prorateMinor(
                $refundedBaseMinor,
                (int) ($commission->eligible_coverage_days ?? 1),
                (int) ($commission->total_coverage_days ?? 1),
            );
            $eligibleRefundMinor = min($eligibleRefundMinor, $this->decimalToMinor((string) $commission->commissionable_amount));
            $reversedAmount = $this->calculateCommission(
                $this->minorToDecimal($eligibleRefundMinor),
                (string) $commission->commission_rate,
            );

            if ($eligibleRefundMinor <= 0) {
                return $commission;
            }

            $fullyReversed = $eligibleRefundMinor >= $this->decimalToMinor((string) $commission->commissionable_amount);
            $commission->update([
                'status' => $fullyReversed ? AgentCommission::STATUS_REVERSED : AgentCommission::STATUS_PARTIALLY_REVERSED,
                'reversed_base_amount' => $this->minorToDecimal($eligibleRefundMinor),
                'reversed_amount' => $reversedAmount,
                'reversed_at' => now(),
                'reversal_reason' => $commission->paid_at
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

        return DB::transaction(function () use ($commission, $reason): AgentCommission {
            $commission = AgentCommission::query()->lockForUpdate()->findOrFail($commission->id);
            if ($commission->status === AgentCommission::STATUS_REVERSED) {
                return $commission;
            }

            $commission->update([
                'status' => AgentCommission::STATUS_REVERSED,
                'reversed_base_amount' => $commission->commissionable_amount,
                'reversed_amount' => $commission->commission_amount,
                'reversed_at' => now(),
                'reversal_reason' => $commission->paid_at
                    ? trim($reason).' The paid amount requires recovery; original payout attribution is retained.'
                    : trim($reason),
            ]);

            return $commission->fresh();
        }, 3);
    }

    private function calculateCommission(string $amount, string $rate): string
    {
        $amountMinor = $this->decimalToMinor($amount);
        $rateUnits = $this->decimalToScaledInteger($rate, 4);
        $commissionMinor = intdiv(($amountMinor * $rateUnits) + 500000, 1000000);

        return sprintf('%d.%02d', intdiv($commissionMinor, 100), $commissionMinor % 100);
    }

    private function actualBaseSubscriptionAmount(Subscription $subscription): string
    {
        $totalMinor = $this->decimalToMinor((string) $subscription->total_due);
        $addonMinor = $subscription->addonSubscriptions()
            ->pluck('total_paid')
            ->sum(fn ($amount): int => $this->decimalToMinor((string) $amount));
        $processingFeeMinor = $this->decimalToMinor((string) ($subscription->processing_fee ?? '0'));
        $baseMinor = max($totalMinor - $addonMinor - $processingFeeMinor, 0);

        return sprintf('%d.%02d', intdiv($baseMinor, 100), $baseMinor % 100);
    }

    private function eligibleCoverage(
        CarbonInterface $paymentStart,
        CarbonInterface $paymentEnd,
        CarbonInterface $eligibilityStart,
        CarbonInterface $eligibilityEnd,
    ): ?array {
        $paymentStart = $paymentStart->copy()->startOfDay();
        $paymentEnd = $paymentEnd->copy()->startOfDay();
        $eligibilityStart = $eligibilityStart->copy()->startOfDay();
        $eligibilityEnd = $eligibilityEnd->copy()->startOfDay();

        if (! $paymentEnd->gt($paymentStart)) {
            return null;
        }

        $eligibleStart = $paymentStart->greaterThan($eligibilityStart) ? $paymentStart->copy() : $eligibilityStart->copy();
        $eligibleEnd = $paymentEnd->lessThan($eligibilityEnd) ? $paymentEnd->copy() : $eligibilityEnd->copy();
        if (! $eligibleEnd->gt($eligibleStart)) {
            return null;
        }

        return [
            'payment_start' => $paymentStart->toDateString(),
            'payment_end' => $paymentEnd->toDateString(),
            'eligible_start' => $eligibleStart->toDateString(),
            'eligible_end' => $eligibleEnd->toDateString(),
            'total_days' => (int) $paymentStart->diffInDays($paymentEnd),
            'eligible_days' => (int) $eligibleStart->diffInDays($eligibleEnd),
        ];
    }

    private function prorateAmount(string $amount, int $eligibleDays, int $totalDays): string
    {
        return $this->minorToDecimal($this->prorateMinor($this->decimalToMinor($amount), $eligibleDays, $totalDays));
    }

    private function prorateMinor(int $amountMinor, int $eligibleDays, int $totalDays): int
    {
        if ($amountMinor <= 0 || $eligibleDays <= 0 || $totalDays <= 0) {
            return 0;
        }

        return intdiv(($amountMinor * min($eligibleDays, $totalDays)) + intdiv($totalDays, 2), $totalDays);
    }

    private function minorToDecimal(int $amountMinor): string
    {
        return sprintf('%d.%02d', intdiv($amountMinor, 100), $amountMinor % 100);
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
