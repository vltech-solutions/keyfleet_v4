<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $permissionIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissionIds = RoleResource::extractPermissionIds($data);

        return RoleResource::removePermissionGroups($data);
    }

    protected function afterSave(): void
    {
        $this->record->permissions()->sync($this->permissionIds);
    }
}