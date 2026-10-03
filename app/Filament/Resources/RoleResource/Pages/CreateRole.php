<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $permissionIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Capture selected permission IDs before removing
        // the temporary permission_group_* form fields.
        $this->permissionIds = RoleResource::extractPermissionIds($data);

        // Remove permission_group_* because they are not
        // actual columns of the roles table.
        $data = RoleResource::removePermissionGroups($data);

        // Assign tenant/company server-side.
        $data['company_id'] = Filament::getTenant()?->getKey();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->permissions()->sync($this->permissionIds);
    }
}