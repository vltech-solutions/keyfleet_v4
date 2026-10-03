<?php

namespace App\Filament\Admin\Resources\AgentProgramResource\Pages;

use App\Filament\Admin\Resources\AgentProgramResource;
use Filament\Resources\Pages\EditRecord;

class EditAgentProgram extends EditRecord
{
    protected static string $resource = AgentProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
