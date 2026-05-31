<?php

namespace App\Filament\Resources\CarDocumentResource\Pages;

use App\Filament\Resources\CarDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarDocument extends EditRecord
{
    protected static string $resource = CarDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
