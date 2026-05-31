<?php

namespace App\Filament\Admin\Resources\CarTypeResource\Pages;

use App\Filament\Admin\Resources\CarTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarType extends EditRecord
{
    protected static string $resource = CarTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
