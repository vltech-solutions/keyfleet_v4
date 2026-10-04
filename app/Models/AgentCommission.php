<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    use TracksUserAttribution;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAYABLE = 'payable';

    public const STATUS_PAID = 'paid';

    public const STATUS_PARTIALLY_REVERSED = 'partially_reversed';

    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'agent_id', 'company_id', 'company_referral_id', 'subscription_id', 'agent_program_id',
        'actual_base_subscription_amount', 'payment_coverage_start', 'payment_coverage_end',
        'eligible_coverage_start', 'eligible_coverage_end', 'total_coverage_days', 'eligible_coverage_days',
        'commissionable_amount', 'commission_rate', 'commission_duration_months', 'commission_amount',
        'reversed_base_amount', 'reversed_amount', 'holding_period_days',
        'status', 'earned_at', 'payable_at', 'paid_at', 'reversed_at', 'reversal_reason',
    ];

    protected function casts(): array
    {
        return [
            'actual_base_subscription_amount' => 'decimal:2',
            'payment_coverage_start' => 'date', 'payment_coverage_end' => 'date',
            'eligible_coverage_start' => 'date', 'eligible_coverage_end' => 'date',
            'total_coverage_days' => 'integer', 'eligible_coverage_days' => 'integer',
            'commissionable_amount' => 'decimal:2', 'commission_rate' => 'decimal:4',
            'commission_duration_months' => 'integer', 'commission_amount' => 'decimal:2',
            'reversed_base_amount' => 'decimal:2', 'reversed_amount' => 'decimal:2',
            'holding_period_days' => 'integer', 'earned_at' => 'datetime', 'payable_at' => 'datetime',
            'paid_at' => 'datetime', 'reversed_at' => 'datetime',
        ];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function referral()
    {
        return $this->belongsTo(CompanyReferral::class, 'company_referral_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function program()
    {
        return $this->belongsTo(AgentProgram::class, 'agent_program_id');
    }

    public function payoutItem()
    {
        return $this->hasOne(AgentPayoutItem::class);
    }

    public function netCommissionAmount(): string
    {
        $net = max($this->decimalToMinor((string) $this->commission_amount) - $this->decimalToMinor((string) $this->reversed_amount), 0);

        return sprintf('%d.%02d', intdiv($net, 100), $net % 100);
    }

    private function decimalToMinor(string $value): int
    {
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole * 100) + (int) substr(str_pad($fraction, 2, '0'), 0, 2);
    }
}
