<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PremiumFeatureResource\Pages;
use App\Models\PremiumFeature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PremiumFeatureResource extends Resource
{
    protected static ?string $model = PremiumFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('feature')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('active')
                    ->required()
                    ->default(true)
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('pricing')
                    ->label('Pricing Plans')
                    ->schema([
                        Forms\Components\Select::make('duration')
                            ->options([
                                'monthly' => 'Monthly',
                                '3months' => '3 Months',
                                '6months' => '6 Months',
                                'annually' => 'Annually',
                            ])
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                    ])
                    ->defaultItems(4)
                    ->columnSpanFull()
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['duration'] ?? null)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('feature')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\IconColumn::make('active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('pricing')
                    ->label('Plans')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state)) return null;

                        return collect($state)
                            ->map(fn ($item) => ($item['duration'] ?? '') . ': ₱' . ($item['price'] ?? ''))
                            ->implode(', ');
                    })
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPremiumFeatures::route('/'),
            'create' => Pages\CreatePremiumFeature::route('/create'),
            'edit' => Pages\EditPremiumFeature::route('/{record}/edit'),
        ];
    }
}