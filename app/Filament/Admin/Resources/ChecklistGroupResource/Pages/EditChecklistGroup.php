<?php

namespace App\Filament\Admin\Resources\ChecklistGroupResource\Pages;

use App\Filament\Admin\Resources\ChecklistGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChecklistGroup extends EditRecord
{
    protected static string $resource = ChecklistGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
