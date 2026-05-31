<?php

namespace App\Filament\Admin\Resources\PremiumFeatureResource\Pages;

use App\Filament\Admin\Resources\PremiumFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPremiumFeatures extends ListRecords
{
    protected static string $resource = PremiumFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
