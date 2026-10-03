<?php

namespace App\Filament\Admin\Resources\AgentReferralResource\Pages;

use App\Filament\Admin\Resources\AgentReferralResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentReferrals extends ListRecords
{
    protected static string $resource = AgentReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
