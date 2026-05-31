<?php

namespace App\Filament\Admin\Resources\ChecklistGroupResource\Pages;

use App\Filament\Admin\Resources\ChecklistGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListChecklistGroups extends ListRecords
{
    protected static string $resource = ChecklistGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New')
                ->modalWidth(MaxWidth::Medium)
        ];
    }
}
