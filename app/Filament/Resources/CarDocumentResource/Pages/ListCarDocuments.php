<?php

namespace App\Filament\Resources\CarDocumentResource\Pages;

use App\Filament\Resources\CarDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarDocuments extends ListRecords
{
    protected static string $resource = CarDocumentResource::class;

    protected function getHeaderActions(): array
    {
        if(auth()->user()->hasActiveSubscription()){
            return [
                Actions\CreateAction::make()
                    ->label('Add New')
                    ->modalWidth('md'),
            ];
        }

        return [];
    }

    
}
