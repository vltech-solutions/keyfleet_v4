<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ChecklistGroupResource\Pages;
use App\Filament\Admin\Resources\ChecklistGroupResource\RelationManagers;
use App\Models\ChecklistGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\MaxWidth;

class ChecklistGroupResource extends Resource
{
    protected static ?string $model = ChecklistGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
         return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->columnSpanFull(),
            ])
            ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->badge(),
                TextColumn::make('order')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Medium),
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
            'index' => Pages\ListChecklistGroups::route('/'),
            // 'create' => Pages\CreateChecklistGroup::route('/create'),
            // 'edit' => Pages\EditChecklistGroup::route('/{record}/edit'),
        ];
    }
}
