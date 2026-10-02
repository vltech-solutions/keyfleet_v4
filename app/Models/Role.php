<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Role extends Model
{
    protected $fillable = ['company_id', 'name', 'description', 'is_protected', 'is_active'];

    protected function casts(): array
    {
        return ['is_protected' => 'boolean', 'is_active' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Role $role): void {
            if ($role->is_protected) {
                throw ValidationException::withMessages(['role' => 'The protected Owner role cannot be deleted.']);
            }

            if ($role->users()->exists()) {
                throw ValidationException::withMessages(['role' => 'Reassign all users before deleting this role.']);
            }
        });
    }
}
