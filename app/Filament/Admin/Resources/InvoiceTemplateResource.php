<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceTemplateResource\Pages;
use App\Filament\Admin\Resources\InvoiceTemplateResource\RelationManagers;
use App\Models\InvoiceTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceTemplateResource extends Resource
{
    protected static ?string $model = InvoiceTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'References';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('template')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('template')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListInvoiceTemplates::route('/'),
            'create' => Pages\CreateInvoiceTemplate::route('/create'),
            'edit' => Pages\EditInvoiceTemplate::route('/{record}/edit'),
        ];
    }
}
