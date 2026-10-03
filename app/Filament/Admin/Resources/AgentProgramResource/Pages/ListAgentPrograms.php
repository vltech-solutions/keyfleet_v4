<?php

namespace App\Filament\Admin\Resources\AgentProgramResource\Pages;

use App\Filament\Admin\Resources\AgentProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgentPrograms extends ListRecords
{
    protected static string $resource = AgentProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
