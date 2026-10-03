<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_token',
        'company_id',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function agentProfile()
    {
        return $this->hasOne(Agent::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->company ? collect([$this->company]) : collect();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $tenant instanceof Company && (int) $this->company_id === (int) $tenant->getKey();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->is_admin && $this->is_active;
        }

        if ($panel->getId() === 'agent') {
            return $this->is_active && ($this->agentProfile?->status === Agent::STATUS_ACTIVE);
        }

        return $this->is_active && $this->company_id !== null && $this->role_id !== null;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->is_active
            && $this->role?->is_active
            && $this->role->permissions()->where('key', $permission)->exists();
    }

    public function isOwner(): bool
    {
        return (bool) $this->role?->is_protected;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->company?->hasActiveSubscription() ?? false;
    }

    public function carLimitReached(): bool
    {
        return $this->company?->carLimitReached() ?? false;
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->role_id) {
                $roleCompanyId = Role::query()->whereKey($user->role_id)->value('company_id');
                if ((int) $roleCompanyId !== (int) $user->company_id) {
                    throw ValidationException::withMessages(['role_id' => 'The selected role belongs to another tenant.']);
                }
            }

            if ($user->exists && (
                ($user->isDirty('is_active') && ! $user->is_active)
                || $user->isDirty('role_id')
                || $user->isDirty('company_id')
            )) {
                app(\App\Services\TenantUserService::class)->assertCanDeactivate($user);
            }
        });

        static::deleting(function (User $user): void {
            if ($user->isOwner()) {
                app(\App\Services\TenantUserService::class)->assertCanDeactivate($user);
            }
        });
    }
}
