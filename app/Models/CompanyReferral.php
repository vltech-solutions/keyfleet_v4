<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;
use Illuminate\Database\Eloquent\Model;

class CompanyReferral extends Model
{
    use TracksUserAttribution;

    public const PROGRAM_CUSTOMER = 'customer_referral';

    public const PROGRAM_AGENT = 'agent';

    protected $fillable = [
        'referrer_company_id',
        'referrer_user_id',
        'agent_id',
        'agent_program_id',
        'referred_company_id',
        'referral_code',
        'program_type',
        'referred_at',
        'qualified_at',
        'commission_started_at',
        'commission_ends_at',
        'commission_duration_months',
        'is_converted',
        'reward_given',
    ];

    protected function casts(): array
    {
        return [
            'is_converted' => 'boolean', 'reward_given' => 'boolean',
            'referred_at' => 'datetime', 'qualified_at' => 'datetime',
            'commission_started_at' => 'datetime', 'commission_ends_at' => 'datetime',
            'commission_duration_months' => 'integer',
        ];
    }

    public function referrer()
    {
        return $this->belongsTo(Company::class, 'referrer_company_id');
    }

    public function referred()
    {
        return $this->belongsTo(Company::class, 'referred_company_id');
    }

    public function referrerUser()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function agentProgram()
    {
        return $this->belongsTo(AgentProgram::class);
    }

    public function commissions()
    {
        return $this->hasMany(AgentCommission::class);
    }
}
