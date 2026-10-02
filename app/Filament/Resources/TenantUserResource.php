<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantUserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(8),
            Forms\Components\Select::make('role_id')
                ->label('Role')
                ->options(fn (): array => Role::query()
                    ->where('company_id', Filament::getTenant()?->getKey())
                    ->where('is_active', true)
                    ->pluck('name', 'id')
                    ->all())
                ->required()
                ->disabled(fn (?User $record): bool => $record?->isOwner() ?? false)
                ->dehydrated(fn (?User $record): bool => ! ($record?->isOwner() ?? false)),
            Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->disabled(fn (?User $record): bool => $record?->isOwner() ?? false)
                ->dehydrated(fn (?User $record): bool => ! ($record?->isOwner() ?? false)),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role.name')->label('Role')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('last_login_at')->dateTime()->placeholder('Never'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->emptyStateHeading('No staff users yet');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', Filament::getTenant()?->getKey());
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('users.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('users.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->hasPermission('users.update') ?? false)
            && (int) $record->company_id === (int) Filament::getTenant()?->getKey();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantUsers::route('/'),
            'create' => Pages\CreateTenantUser::route('/create'),
            'edit' => Pages\EditTenantUser::route('/{record}/edit'),
        ];
    }
}
