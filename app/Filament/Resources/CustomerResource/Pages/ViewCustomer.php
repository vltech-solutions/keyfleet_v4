<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Booking;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Renter Details')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Full Name')
                            ->disabled(),
                        TextInput::make('contact_number')
                            ->label('Contact Number')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('Email')
                            ->disabled(),
                        TextInput::make('facebook_name')
                            ->label('Facebook')
                            ->disabled(),
                        Textarea::make('address')
                            ->columnSpanFull()
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
