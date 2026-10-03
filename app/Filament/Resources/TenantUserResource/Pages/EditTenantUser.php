<?php

namespace App\Filament\Resources\TenantUserResource\Pages;

use App\Filament\Resources\TenantUserResource;
use App\Services\TenantUserService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;

class EditTenantUser extends EditRecord
{
    protected static string $resource = TenantUserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! ($data['is_active'] ?? true)) {
            app(TenantUserService::class)->assertCanDeactivate($record);
        }
        return app(TenantUserService::class)->updateUser($record, $data);
    }
}
