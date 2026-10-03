<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AgentResource\Pages;
use App\Models\Agent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AgentResource extends Resource
{
    protected static ?string $model = Agent::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Agent Program';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'email')->searchable()->preload()->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('agent_program_id')->relationship('program', 'name')->required(),
            Forms\Components\TextInput::make('referral_code')->required()->unique(ignoreRecord: true)->default(fn () => 'KF-'.Str::upper(Str::random(8))),
            Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended', 'terminated' => 'Terminated'])->required(),
            Forms\Components\DateTimePicker::make('agreement_accepted_at'),
            Forms\Components\Select::make('payout_method')->options(['bank' => 'Bank Transfer', 'gcash' => 'GCash', 'other' => 'Other']),
            Forms\Components\TextInput::make('payout_details.account_name'),
            Forms\Components\TextInput::make('payout_details.account_number')->password()->revealable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->searchable(),
            Tables\Columns\TextColumn::make('user.email')->searchable(),
            Tables\Columns\TextColumn::make('referral_code'),
            Tables\Columns\TextColumn::make('program.name'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('activated_at')->dateTime()->placeholder('—'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAgents::route('/'), 'create' => Pages\CreateAgent::route('/create'), 'edit' => Pages\EditAgent::route('/{record}/edit')];
    }
}
