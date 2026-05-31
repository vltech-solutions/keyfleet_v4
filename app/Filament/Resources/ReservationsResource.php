<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages\ViewReservation;
use App\Filament\Resources\ReservationsResource\Pages;
use App\Filament\Resources\ReservationsResource\RelationManagers;
use App\Models\Car;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;

class ReservationsResource extends Resource
{
    protected static ?string $model = Reservation::class;

    // protected static ?string $navigationParentItem = 'Bookings';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }
    

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false;
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Stack::make([
                // Car Image & Name Header
                Split::make([
                    ImageColumn::make('car.image')
                        ->label('Reserved Car')
                        // ->circular()
                        ->grow(false),
                    
                    Stack::make([
                        TextColumn::make('car.name')
                            ->weight('bold')
                            ->searchable()
                            ->formatStateUsing(function ($state, $record) {
                                return $record->car?->trashed() ? "{$state} (Deleted)" : $state;
                            })
                            ->color(fn ($record) => $record->car?->trashed() ? 'danger' : 'primary'),
                            
                        TextColumn::make('reservation_number')
                            ->size('xs')
                            ->color('gray')
                            ->searchable(),
                    ]),
                ]),

                // Booking Period (Rich HTML)
               TextColumn::make('booking_period')
                ->html()
                ->getStateUsing(function ($record) {
                  $start = $record->start_date ? Carbon::parse($record->start_date)->format('M d, h:i A') : 'N/A';
                  $end = $record->end_date ? Carbon::parse($record->end_date)->format('M d, h:i A') : 'N/A';

                  // Ginamitan natin ng inline style at mas maliit na size (16px) para hindi sira ang alignment
                  $icon = "<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' style='width:14px; height:14px; display:inline; margin-bottom:2px;'>
                                    <path stroke-linecap='round' stroke-linejoin='round' d='M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5' />
                                 </svg>";

                  return "
                            <div class='flex flex-col space-y-1 text-xs py-2'>
                                <div class='flex justify-between items-center'><span class='text-gray-500 flex items-center gap-1'>Pickup {$icon}:</span> <span class='font-medium'>{$start}</span></div>
                                <div class='flex justify-between items-center'><span class='text-gray-500 flex items-center gap-1'>Return {$icon}:</span> <span class='font-medium'>{$end}</span></div>
                            </div>
                        ";
                }),

                // Status & Renter Row
                Split::make([
                    TextColumn::make('customer.customer_name')
                        ->icon('heroicon-m-user')
                        ->size('sm')
                        ->color('gray'),

                    TextColumn::make('status')
                        ->badge()
                        ->getStateUsing(function ($record) {
                            $start = $record->start_date;
                            $end = $record->end_date;
                            $carId = $record->selected_car_id;

                            if ($start && $carId && $record->status === 'pending') {
                                if (!Car::isAvailableAt($carId, $start, $end, $record->id)) {
                                    return 'Dates Not Available';
                                }
                            }

                            if ($end && $end->isPast()) return 'Past Reservation';
                            
                            return ucfirst($record->status);
                        })
                        ->colors([
                            'success' => 'Approved',
                            'danger' => fn ($state) => in_array($state, ['Declined', 'Dates Not Available']),
                            'warning' => 'Pending',
                        ])
                        ->grow(false),
                ]),

                // Days/Hours Badge (Small Footer)
                TextColumn::make('hours')
                    ->badge()
                    ->color('gray')
                    ->getStateUsing(function ($record) {
                        if (!$record->start_date || !$record->end_date) return 'N/A';
                        $diff = Carbon::parse($record->start_date)->diffInHours(Carbon::parse($record->end_date));
                        return $diff > 24 ? round($diff / 24) . ' days' : $diff . ' hours';
                    }),
            ])->space(3),
        ])
        ->contentGrid([
            'md' => 2,
            'xl' => 3,
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'declined' => 'Declined',
                ])
                ->default('pending'),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservations::route('/create'),
            // 'edit' => Pages\EditReservations::route('/{record}/edit'),
            'view' => Pages\ViewReservation::route('/{record}'),
        ];
    }
}
