<?php

namespace App\Filament\Resources\CarResource\Pages;

use App\Filament\Resources\CarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCars extends ListRecords
{
    protected static string $resource = CarResource::class;

    protected function getHeaderActions(): array
    {
        if(auth()->user()->hasActiveSubscription() && !auth()->user()->carLimitReached()){
            return [
                Actions\CreateAction::make()
                    ->label('Add New'),
            ];
        }
        
        return [];
    }
}
