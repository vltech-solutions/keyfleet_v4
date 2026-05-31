<?php

namespace App\Filament\Admin\Resources\RequirementTypesResource\Pages;

use App\Filament\Admin\Resources\RequirementTypesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequirementTypes extends EditRecord
{
    protected static string $resource = RequirementTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
