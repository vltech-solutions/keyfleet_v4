<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VoucherResource\Pages;
use App\Filament\Admin\Resources\VoucherResource\RelationManagers;
use App\Models\Company;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Voucher Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Voucher Code')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('type')
                            ->options([
                                'fixed' => 'Fixed (₱)',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->default('fixed')
                            ->required(),

                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->label('Discount Value'),

                        DatePicker::make('valid_until')
                            ->label('Valid Until')
                            ->required(),

                        TextInput::make('usage_limit')
                            ->numeric()
                            ->label('Total Usage Limit'),

                        TextInput::make('per_company_limit')
                            ->numeric()
                            ->label('Per Company Usage Limit'),

                        Toggle::make('active')
                            ->label('Is Active')
                            ->default(true),

                        Select::make('company_ids')
                            ->label('Allowed Companies')
                            ->options(
                                Company::all()->pluck('name', 'id')
                            )
                            ->multiple()
                            ->searchable()
                            ->helperText('Leave blank to allow all companies')
                            ->preload()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           ->columns([
                TextColumn::make('code')->sortable()->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('amount')->label('Value'),
                TextColumn::make('usage_limit')->label('Global Limit'),
                TextColumn::make('per_company_limit')->label('Per Co. Limit'),
                TextColumn::make('valid_until')->label('Expires'),
                BooleanColumn::make('active'),
            ])
            ->defaultSort('valid_until', 'desc')
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
            'index' => Pages\ListVouchers::route('/'),
            // 'create' => Pages\CreateVoucher::route('/create'),
            // 'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
