<?php

namespace App\Filament\Agent\Resources\PayoutResource\Pages;

use App\Filament\Agent\Resources\PayoutResource;
use Filament\Resources\Pages\ListRecords;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
