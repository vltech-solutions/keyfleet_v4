<?php

namespace App\Filament\Resources\FundTypeResource\Pages;

use App\Filament\Resources\FundTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundTypes extends ListRecords
{
    protected static string $resource = FundTypeResource::class;

    protected function getHeaderActions(): array
    {   

       if(auth()->user()->hasActiveSubscription() ){
            return [
                Actions\CreateAction::make()
                    ->label('Add New')
                    ->modalWidth('md'),
            ];
       }
        
        return [];
    }
}
