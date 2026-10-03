<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ProfileResource\Pages;
use App\Models\Agent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProfileResource extends Resource
{
    protected static ?string $model = Agent::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Profile';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereKey(auth()->user()->agentProfile->id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('referral_code')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('program.name')->label('Agent Program')->disabled()->dehydrated(false),
            Forms\Components\Select::make('payout_method')->options(['bank' => 'Bank Transfer', 'gcash' => 'GCash', 'other' => 'Other']),
            Forms\Components\TextInput::make('payout_details.account_name')->label('Account Name')->maxLength(255),
            Forms\Components\TextInput::make('payout_details.account_number')->label('Account / Wallet Number')->password()->revealable()->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('referral_code'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('program.name'),
            Tables\Columns\TextColumn::make('payout_method'),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProfiles::route('/'), 'edit' => Pages\EditProfile::route('/{record}/edit')];
    }
}
