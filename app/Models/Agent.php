<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use TracksUserAttribution;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = ['user_id', 'agent_program_id', 'referral_code', 'status', 'activated_at', 'agreement_accepted_at', 'payout_method', 'payout_details'];

    protected $hidden = ['payout_details'];

    protected function casts(): array
    {
        return ['activated_at' => 'datetime', 'agreement_accepted_at' => 'datetime', 'payout_details' => 'encrypted:array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(AgentProgram::class, 'agent_program_id');
    }

    public function referrals()
    {
        return $this->hasMany(CompanyReferral::class);
    }

    public function commissions()
    {
        return $this->hasMany(AgentCommission::class);
    }

    public function payouts()
    {
        return $this->hasMany(AgentPayout::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function canReferAt($date = null): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->activated_at !== null
            && $this->program?->isAvailableAt($date);
    }
}
