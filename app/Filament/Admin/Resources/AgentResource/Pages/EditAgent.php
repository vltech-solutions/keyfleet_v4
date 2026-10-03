<?php

namespace App\Filament\Admin\Resources\AgentResource\Pages;

use App\Filament\Admin\Resources\AgentResource;
use App\Models\Agent;
use Filament\Resources\Pages\EditRecord;

class EditAgent extends EditRecord
{
    protected static string $resource = AgentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === Agent::STATUS_ACTIVE && ! $this->record->activated_at) {
            $data['activated_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
