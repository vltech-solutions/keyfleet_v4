<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\AgentPayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentPayoutService
{
    public function create(Agent $agent, array $commissionIds, array $attributes = []): AgentPayout
    {
        return DB::transaction(function () use ($agent, $commissionIds, $attributes): AgentPayout {
            $commissions = AgentCommission::query()
                ->where('agent_id', $agent->id)
                ->whereIn('id', array_unique($commissionIds))
                ->lockForUpdate()
                ->get();

            if ($commissions->count() !== count(array_unique($commissionIds)) || $commissions->isEmpty()) {
                throw ValidationException::withMessages(['commissions' => 'Select valid commissions owned by this agent.']);
            }

            if ($commissions->contains(fn (AgentCommission $item) => ! $this->isPayable($item) || $item->payoutItem()->exists())) {
                throw ValidationException::withMessages(['commissions' => 'Only unassigned payable commissions may enter a payout.']);
            }

            $payout = AgentPayout::create([
                'agent_id' => $agent->id,
                'reference_number' => $attributes['reference_number'] ?? 'KFP-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'amount' => $this->sum($commissions),
                'status' => AgentPayout::STATUS_DRAFT,
                'period_start' => $attributes['period_start'] ?? $commissions->min('earned_at')?->toDateString(),
                'period_end' => $attributes['period_end'] ?? $commissions->max('earned_at')?->toDateString(),
                'notes' => $attributes['notes'] ?? null,
            ]);

            $payout->commissions()->attach($commissions->modelKeys());

            return $payout->load('commissions');
        }, 3);
    }

    public function markPaid(AgentPayout $payout, $paidAt = null): AgentPayout
    {
        return DB::transaction(function () use ($payout, $paidAt): AgentPayout {
            $payout = AgentPayout::query()->lockForUpdate()->findOrFail($payout->id);
            if (! in_array($payout->status, [AgentPayout::STATUS_DRAFT, AgentPayout::STATUS_PROCESSING], true)) {
                throw ValidationException::withMessages(['status' => 'Only draft or processing payouts may be paid.']);
            }

            $commissions = $payout->commissions()->lockForUpdate()->get();
            if ($commissions->isEmpty() || $commissions->contains(fn ($item) => ! $this->isPayable($item))) {
                throw ValidationException::withMessages(['commissions' => 'Every payout item must still be payable.']);
            }

            $paidAt ??= now();
            $payout->update(['amount' => $this->sum($commissions), 'status' => AgentPayout::STATUS_PAID, 'paid_at' => $paidAt]);
            $payout->commissions()->update(['status' => AgentCommission::STATUS_PAID, 'paid_at' => $paidAt]);

            return $payout->fresh('commissions');
        }, 3);
    }

    private function sum(Collection $commissions): string
    {
        $cents = $commissions->sum(function ($item): int {
            [$whole, $fraction] = array_pad(explode('.', $item->netCommissionAmount(), 2), 2, '');

            return ((int) $whole * 100) + (int) substr(str_pad($fraction, 2, '0'), 0, 2);
        });

        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    private function isPayable(AgentCommission $commission): bool
    {
        return $commission->status === AgentCommission::STATUS_PAYABLE
            || ($commission->status === AgentCommission::STATUS_PARTIALLY_REVERSED && $commission->payable_at->isPast());
    }
}
