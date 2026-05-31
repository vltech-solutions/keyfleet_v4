<?php

namespace App\Filament\Admin\Resources\InvoiceTemplateResource\Pages;

use App\Filament\Admin\Resources\InvoiceTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceTemplate extends EditRecord
{
    protected static string $resource = InvoiceTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
