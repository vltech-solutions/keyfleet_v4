<?php

namespace App\Filament\Resources\TenantUserResource\Pages;

use App\Filament\Resources\TenantUserResource;
use App\Services\TenantUserService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantUser extends CreateRecord
{
    protected static string $resource = TenantUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $company = Filament::getTenant();
        app(TenantUserService::class)->assertSeatAvailable($company);
        $data['company_id'] = $company->getKey();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->companies()->syncWithoutDetaching([$this->record->company_id]);
    }
}
