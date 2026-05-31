<?php

namespace App\Filament\Resources\SourceResource\Pages;

use App\Filament\Resources\SourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSources extends ListRecords
{
    protected static string $resource = SourceResource::class;

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
