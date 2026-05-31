<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Plans & Subscriptions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('car_limit')
                    ->numeric()
                    ->required(),
                Toggle::make('is_active'),

                TextInput::make('referral_reward_days')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Repeater::make('prices')
                    ->relationship('prices') // links to hasMany PlanPrice
                    ->schema([
                        Select::make('billing_cycle')
                            ->options([
                                '14days' => '14 days',
                                'monthly' => 'Monthly',
                                '3months' => '3 Months',
                                '6months' => '6 Months',
                                'annually' => 'Annually',
                                '2years' => '2 Years',
                            ])
                            ->required(),

                        TextInput::make('price')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),
                    ])
                    ->defaultItems(1)
                    ->label('Billing Options')
                    ->columnSpan('full')
                    ->collapsible()
                    ->addActionLabel('Add Billing Option')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Plan')
                    ->searchable(),
                TextColumn::make('car_limit')
                    ->label('Car Limit'),
                TextColumn::make('prices')
                    ->label('Billing Cycles')
                    ->formatStateUsing(function ($record) {
                        return $record->prices->map(fn ($price) =>
                            strtoupper($price->billing_cycle) . ': ₱' . number_format($price->price, 2)
                        )->join(', ');
                    }),
                ToggleColumn::make('is_active'),
                TextColumn::make('referral_reward_days')
                    ->label('Referral Reward Days'),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
