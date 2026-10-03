<?php

namespace App\Filament\Resources\TenantUserResource\Pages;

use App\Filament\Resources\TenantUserResource;
use App\Services\TenantUserService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenantUser extends CreateRecord
{
    protected static string $resource = TenantUserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $company = Filament::getTenant();
        $user = app(TenantUserService::class)->createUser($company, $data);
        $user->companies()->syncWithoutDetaching([$company->getKey()]);

        return $user;
    }
}
