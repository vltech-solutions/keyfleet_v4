<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentProgram extends Model
{
    use TracksUserAttribution;

    protected $fillable = ['name', 'commission_rate', 'commission_duration_months', 'holding_period_days', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4', 'commission_duration_months' => 'integer',
            'holding_period_days' => 'integer', 'is_active' => 'boolean',
            'starts_at' => 'datetime', 'ends_at' => 'datetime',
        ];
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function isAvailableAt($date = null): bool
    {
        $date ??= now();

        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte($date))
            && (! $this->ends_at || $this->ends_at->gte($date));
    }
}
