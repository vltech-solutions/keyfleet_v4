<?php

namespace App\Filament\Agent\Resources\CommissionResource\Pages;

use App\Filament\Agent\Resources\CommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissions extends ListRecords
{
    protected static string $resource = CommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
