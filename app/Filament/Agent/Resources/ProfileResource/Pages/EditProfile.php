<?php

namespace App\Filament\Agent\Resources\ProfileResource\Pages;

use App\Filament\Agent\Resources\ProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
