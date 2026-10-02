<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantUserService
{
    public function ownerRole(Company $company): Role
    {
        return DB::transaction(function () use ($company): Role {
            $role = Role::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'Owner'],
                [
                    'description' => 'Protected tenant owner with full access.',
                    'is_protected' => true,
                    'is_active' => true,
                ],
            );

            $role->permissions()->sync(Permission::query()->pluck('id'));

            return $role;
        });
    }

    public function assertSeatAvailable(Company $company, ?User $excluding = null): void
    {
        $limit = $company->userLimit();
        if ($limit === null) {
            return;
        }

        $activeUsers = $company->users()
            ->where('is_active', true)
            ->when($excluding, fn ($query) => $query->whereKeyNot($excluding->getKey()))
            ->count();

        if ($activeUsers >= $limit) {
            throw ValidationException::withMessages([
                'email' => "This subscription allows {$limit} active user".($limit === 1 ? '' : 's').'. Deactivate a user or upgrade the plan first.',
            ]);
        }
    }

    public function assertCanDeactivate(User $user): void
    {
        $roleId = $user->isDirty('role_id')
            ? $user->getOriginal('role_id')
            : $user->role_id;
        $wasOwner = Role::query()->whereKey($roleId)->where('is_protected', true)->exists();

        if (! $wasOwner) {
            return;
        }

        $otherOwners = User::query()
            ->where('company_id', $user->company_id)
            ->where('is_active', true)
            ->whereKeyNot($user->getKey())
            ->whereHas('role', fn ($query) => $query->where('is_protected', true))
            ->exists();

        if (! $otherOwners) {
            throw ValidationException::withMessages([
                'is_active' => 'The last active Owner cannot be deactivated or reassigned.',
            ]);
        }
    }
}
