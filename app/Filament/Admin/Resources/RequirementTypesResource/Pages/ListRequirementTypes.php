<?php

namespace App\Filament\Admin\Resources\RequirementTypesResource\Pages;

use App\Filament\Admin\Resources\RequirementTypesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequirementTypes extends ListRecords
{
    protected static string $resource = RequirementTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New'),
        ];
    }
}
