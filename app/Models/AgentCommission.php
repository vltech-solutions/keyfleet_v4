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

    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'agent_id', 'company_id', 'company_referral_id', 'subscription_id', 'agent_program_id',
        'commissionable_amount', 'commission_rate', 'commission_amount', 'holding_period_days',
        'status', 'earned_at', 'payable_at', 'paid_at', 'reversed_at', 'reversal_reason',
    ];

    protected function casts(): array
    {
        return [
            'commissionable_amount' => 'decimal:2', 'commission_rate' => 'decimal:4', 'commission_amount' => 'decimal:2',
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
}
