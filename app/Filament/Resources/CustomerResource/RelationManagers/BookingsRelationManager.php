<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Filament\Resources\BookingResource;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings'; // relation in Customer model

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Renter Details')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Full Name')
                            ->disabled(),
                        Forms\Components\TextInput::make('address')
                            ->disabled(),
                        Forms\Components\TextInput::make('contact_number')
                            ->label('Contact Number')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('car.image')
                    ->label('Rented Car'),
                TextColumn::make('car.name')->label('Car')->badge(),
                TextColumn::make('start_datetime')->dateTime('M d, Y h:i A'),
                TextColumn::make('end_datetime')->dateTime('M d, Y h:i A'),
                TextColumn::make('total_due')->money('PHP'),
                TextColumn::make('balance')->money('PHP')
                    ->color(fn($record) => $record->balance > 0 ? 'danger' : 'black'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Action::make('editBooking')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => BookingResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(false),
            ])
            ->defaultSort('start_datetime', 'desc');
    }
}
    