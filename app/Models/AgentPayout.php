<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;
use Illuminate\Database\Eloquent\Model;

class AgentPayout extends Model
{
    use TracksUserAttribution;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['agent_id', 'reference_number', 'amount', 'status', 'period_start', 'period_end', 'paid_at', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'period_start' => 'date', 'period_end' => 'date', 'paid_at' => 'datetime'];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function items()
    {
        return $this->hasMany(AgentPayoutItem::class);
    }

    public function commissions()
    {
        return $this->belongsToMany(AgentCommission::class, 'agent_payout_items');
    }
}
