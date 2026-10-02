<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
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

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 91;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->disabled(fn (?Role $record): bool => $record?->is_protected ?? false)
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('company_id', Filament::getTenant()?->getKey()),
                ),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->disabled(fn (?Role $record): bool => $record?->is_protected ?? false),
            Forms\Components\CheckboxList::make('permissions')
                ->relationship('permissions', 'description')
                ->options(fn (): array => Permission::query()
                    ->orderBy('module')
                    ->orderBy('key')
                    ->get()
                    ->mapWithKeys(fn (Permission $permission) => [
                        $permission->id => "{$permission->module}: {$permission->description}",
                    ])
                    ->all())
                ->columns(2)
                ->bulkToggleable()
                ->searchable()
                ->disabled(fn (?Role $record): bool => $record?->is_protected ?? false)
                ->columnSpanFull(),
        ])->columns(2);
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
        return auth()->user()?->hasPermission('roles.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('roles.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->hasPermission('roles.update') ?? false)
            && (int) $record->company_id === (int) Filament::getTenant()?->getKey();
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->hasPermission('roles.delete') ?? false)
            && (int) $record->company_id === (int) Filament::getTenant()?->getKey()
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
