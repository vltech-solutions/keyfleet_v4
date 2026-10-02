<?php

namespace App\Filament\Resources\TenantUserResource\Pages;

use App\Filament\Resources\TenantUserResource;
use App\Services\TenantUserService;
use Filament\Resources\Pages\EditRecord;

class EditTenantUser extends EditRecord
{
    protected static string $resource = TenantUserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['is_active'] ?? false) && ! $this->record->is_active) {
            app(TenantUserService::class)->assertSeatAvailable($this->record->company, $this->record);
        }

        if (! ($data['is_active'] ?? true)) {
            app(TenantUserService::class)->assertCanDeactivate($this->record);
        }

        return $data;
    }
}
