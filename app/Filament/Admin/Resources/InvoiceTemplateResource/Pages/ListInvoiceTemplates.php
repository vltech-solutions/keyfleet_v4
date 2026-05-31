<?php

namespace App\Filament\Admin\Resources\InvoiceTemplateResource\Pages;

use App\Filament\Admin\Resources\InvoiceTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceTemplates extends ListRecords
{
    protected static string $resource = InvoiceTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
