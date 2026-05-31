<?php

namespace App\Filament\Resources\ChecklistItemResource\Pages;

use App\Filament\Resources\ChecklistItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListChecklistItems extends ListRecords
{
    protected static string $resource = ChecklistItemResource::class;

    protected function getHeaderActions(): array
    {

        if(auth()->user()->hasActiveSubscription()){
            return [
                Actions\CreateAction::make()
                    ->label('Add New')
                    ->modalWidth(MaxWidth::Medium),
            ];
        }

        return [];
    }
}
