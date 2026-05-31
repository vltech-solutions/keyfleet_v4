<?php

namespace App\Filament\Admin\Resources\PremiumFeatureResource\Pages;

use App\Filament\Admin\Resources\PremiumFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPremiumFeature extends EditRecord
{
    protected static string $resource = PremiumFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
