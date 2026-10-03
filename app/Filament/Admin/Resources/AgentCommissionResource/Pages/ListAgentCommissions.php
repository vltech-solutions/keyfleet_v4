<?php

namespace App\Filament\Admin\Resources\AgentCommissionResource\Pages;

use App\Filament\Admin\Resources\AgentCommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentCommissions extends ListRecords
{
    protected static string $resource = AgentCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
