<?php

namespace App\Filament\Agent\Resources\ProfileResource\Pages;

use App\Filament\Agent\Resources\ProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListProfiles extends ListRecords
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
