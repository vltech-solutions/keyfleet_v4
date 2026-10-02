<?php

namespace App\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

abstract class TenantResource extends Resource
{
    protected static function permissionPrefix(): string
    {
        return match (class_basename(static::class)) {
            'BookingResource' => 'bookings',
            'QuotationResource' => 'quotations',
            'ReservationsResource' => 'reservations',
            'CarResource' => 'cars',
            'CarDocumentResource' => 'car_documents',
            'ChecklistItemResource' => 'checklist_items',
            'CustomerResource' => 'customers',
            'ExpenseResource' => 'expenses',
            'FundTypeResource' => 'fund_types',
            'PartnerResource' => 'partners',
            'SourceResource' => 'sources',
            default => throw new \LogicException('No permission prefix configured for '.static::class),
        };
    }

    protected static function allowed(string $action): bool
    {
        return auth()->user()?->hasPermission(static::permissionPrefix().'.'.$action) ?? false;
    }

    protected static function owns(Model $record): bool
    {
        if (! $record->getAttribute('company_id')) {
            return true;
        }

        return (int) $record->getAttribute('company_id') === (int) Filament::getTenant()?->getKey();
    }

    public static function canViewAny(): bool
    {
        return static::allowed('view');
    }

    public static function canView(Model $record): bool
    {
        return static::allowed('view') && static::owns($record);
    }

    public static function canCreate(): bool
    {
        return static::allowed('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::allowed('update') && static::owns($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::allowed('delete') && static::owns($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::allowed('delete');
    }
}
