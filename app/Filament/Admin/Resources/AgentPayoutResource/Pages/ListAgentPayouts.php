<?php

namespace App\Filament\Admin\Resources\AgentPayoutResource\Pages;

use App\Filament\Admin\Resources\AgentPayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgentPayouts extends ListRecords
{
    protected static string $resource = AgentPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
