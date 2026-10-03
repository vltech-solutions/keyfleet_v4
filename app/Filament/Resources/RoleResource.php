<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 91;

    public static function form(Form $form): Form
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('key')
            ->get()
            ->groupBy('module');

        return $form->schema([
            Forms\Components\Section::make('Role Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn (?Role $record): bool => $record?->is_protected ?? false)
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule) =>
                                $rule->where('company_id', Filament::getTenant()?->getKey()),
                        ),

                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->disabled(fn (?Role $record): bool => $record?->is_protected ?? false),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Permissions')
                ->description('Choose what users assigned to this role can access.')
                ->schema(
                    $permissions
                        ->map(function ($items, $module) {
                            $stateKey = 'permission_group_' . Str::slug($module, '_');

                            return Forms\Components\Section::make($module)
                                ->compact()
                                ->collapsible()
                                ->schema([
                                    Forms\Components\CheckboxList::make($stateKey)
                                        ->hiddenLabel()
                                        ->options(
                                            $items
                                                ->mapWithKeys(fn (Permission $permission) => [
                                                    $permission->id => $permission->description,
                                                ])
                                                ->all()
                                        )
                                        ->columns([
                                            'md' => 2,
                                            'xl' => 3,
                                        ])
                                        ->bulkToggleable()
                                        ->disabled(
                                            fn (?Role $record): bool =>
                                                $record?->is_protected ?? false
                                        )
                                        ->afterStateHydrated(function (
                                            Forms\Components\CheckboxList $component,
                                            ?Role $record
                                        ) use ($items): void {
                                            if (! $record) {
                                                $component->state([]);

                                                return;
                                            }

                                            $record->loadMissing('permissions');

                                            $ids = $record->permissions
                                                ->whereIn('id', $items->pluck('id'))
                                                ->pluck('id')
                                                ->all();

                                            $component->state($ids);
                                        }),
                                ]);
                        })
                        ->values()
                        ->all()
                )
                ->columnSpanFull(),
        ]);
    }

    public static function extractPermissionIds(array $data): array
    {
        return collect($data)
            ->filter(
                fn ($value, $key) => str_starts_with($key, 'permission_group_')
            )
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function removePermissionGroups(array $data): array
    {
        return collect($data)
            ->reject(
                fn ($value, $key) => str_starts_with($key, 'permission_group_')
            )
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(60),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Users'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\IconColumn::make('is_protected')->boolean()->label('Protected'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Role $record): bool => ! $record->is_protected && ! $record->users()->exists()),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', Filament::getTenant()?->getKey());
    }

    public static function canViewAny(): bool
    {
        return (auth()->user()?->hasPermission('roles.view') ?? false)
            && (static::tenantCompany()?->hasMultiUserAccess() ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    private static function tenantCompany(): ?Company
    {
        $tenant = Filament::getTenant();
        return $tenant instanceof Company ? $tenant : null;
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->hasPermission('roles.create') ?? false)
            && (static::tenantCompany()?->hasMultiUserAccess() ?? false);
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->hasPermission('roles.update') ?? false)
            && (int) $record->company_id === (int) Filament::getTenant()?->getKey()
            && (static::tenantCompany()?->hasMultiUserAccess() ?? false);
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->hasPermission('roles.delete') ?? false)
            && (int) $record->company_id === (int) Filament::getTenant()?->getKey()
            && (static::tenantCompany()?->hasMultiUserAccess() ?? false)
            && ! $record->is_protected
            && ! $record->users()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
