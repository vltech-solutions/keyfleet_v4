<?php

namespace App\Filament\Admin\Resources\AgentPayoutResource\Pages;

use App\Filament\Admin\Resources\AgentPayoutResource;
use App\Models\Agent;
use App\Services\AgentPayoutService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAgentPayout extends CreateRecord
{
    protected static string $resource = AgentPayoutResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $agentId = $data['agent_id'];
        $commissionIds = $data['commission_ids'];
        unset($data['commission_ids'], $data['agent_id']);

        return app(AgentPayoutService::class)->create(Agent::findOrFail($agentId), $commissionIds, array_filter($data, fn ($value) => filled($value)));
    }
}
