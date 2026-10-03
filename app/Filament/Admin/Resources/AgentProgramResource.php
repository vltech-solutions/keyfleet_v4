<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AgentProgramResource\Pages;
use App\Models\AgentProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgentProgramResource extends Resource
{
    protected static ?string $model = AgentProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Agent Program';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('commission_rate')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
            Forms\Components\TextInput::make('commission_duration_months')->numeric()->integer()->minValue(1)->required(),
            Forms\Components\TextInput::make('holding_period_days')->numeric()->integer()->minValue(0)->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('ends_at')->after('starts_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('commission_rate')->suffix('%'),
            Tables\Columns\TextColumn::make('commission_duration_months')->suffix(' months'),
            Tables\Columns\TextColumn::make('holding_period_days')->suffix(' days'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAgentPrograms::route('/'), 'create' => Pages\CreateAgentProgram::route('/create'), 'edit' => Pages\EditAgentProgram::route('/{record}/edit')];
    }
}
