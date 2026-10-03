<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPayoutItem extends Model
{
    protected $fillable = ['agent_payout_id', 'agent_commission_id'];

    public function payout()
    {
        return $this->belongsTo(AgentPayout::class, 'agent_payout_id');
    }

    public function commission()
    {
        return $this->belongsTo(AgentCommission::class, 'agent_commission_id');
    }
}
