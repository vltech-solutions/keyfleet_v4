<?php

namespace App\Filament\Agent\Resources\ReferralResource\Pages;

use App\Filament\Agent\Resources\ReferralResource;
use Filament\Resources\Pages\ListRecords;

class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
