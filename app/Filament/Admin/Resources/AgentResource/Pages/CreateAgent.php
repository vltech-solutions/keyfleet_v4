<?php

namespace App\Filament\Admin\Resources\AgentResource\Pages;

use App\Filament\Admin\Resources\AgentResource;
use App\Models\Agent;
use Filament\Resources\Pages\CreateRecord;

class CreateAgent extends CreateRecord
{
    protected static string $resource = AgentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['status'] === Agent::STATUS_ACTIVE) {
            $data['activated_at'] = now();
        }

        return $data;
    }
}
