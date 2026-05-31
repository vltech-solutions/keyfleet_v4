<?php

namespace App\Filament\Resources\FundTypeResource\Pages;

use App\Filament\Resources\FundTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundType extends EditRecord
{
    protected static string $resource = FundTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
