<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CarTypeResource\Pages;
use App\Filament\Admin\Resources\CarTypeResource\RelationManagers;
use App\Models\CarType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarTypeResource extends Resource
{
    protected static ?string $model = CarType::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'References';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('car_type')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('car_type')
                    ->label('Car Type')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('md'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarTypes::route('/'),
            // 'create' => Pages\CreateCarType::route('/create'),
            // 'edit' => Pages\EditCarType::route('/{record}/edit'),
        ];
    }
}
