<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages\ViewReservation;
use App\Filament\Resources\ReservationsResource\Pages;
use App\Models\Car;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Illuminate\Database\Eloquent\Builder;

class ReservationsResource extends TenantResource
{
    protected static ?string $model = Reservation::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()->orderBy('created_at', 'desc')
            )
            ->columns([
                Stack::make([
                    // Car Image & Name Header
                    Split::make([
                        ImageColumn::make('car.image')
                            ->label('Reserved Car')
                            ->grow(false),
                        
                        Stack::make([
                            TextColumn::make('car.name')
                                ->weight('bold')
                                ->searchable()
                                ->formatStateUsing(function ($state, $record) {
                                    if (!$record) return $state;
                                    return $record->car?->trashed() ? "{$state} (Deleted)" : $state;
                                })
                                ->color(fn ($record) => $record && $record->car?->trashed() ? 'danger' : 'primary'),
                                
                            TextColumn::make('reservation_number')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                    ]),

                    // Booking Period
                    TextColumn::make('booking_period')
                        ->html()
                        ->getStateUsing(function ($record) {
                            if (!$record) return 'N/A';
                            
                            $start = $record->start_date ? Carbon::parse($record->start_date)->format('M d, Y h:i A') : 'N/A';
                            $end = $record->end_date ? Carbon::parse($record->end_date)->format('M d, Y h:i A') : 'N/A';

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
                                if (!$record) return 'N/A';
                                return $record->status; // Uses the accessor
                            })
                            ->colors([
                                'success' => 'approved',
                                'warning' => 'pending',
                                'danger' => 'declined',
                                'gray' => 'cancelled',
                            ])
                            ->grow(false),
                    ]),

                    // Days/Hours Badge
                    TextColumn::make('hours')
                        ->badge()
                        ->color('gray')
                        ->getStateUsing(function ($record) {
                            if (!$record || !$record->start_date || !$record->end_date) return 'N/A';
                            $diff = Carbon::parse($record->start_date)->diffInHours(Carbon::parse($record->end_date));
                            return $diff > 24 ? round($diff / 24) . ' days' : $diff . ' hours';
                        }),
                        
                    // Cancellation Info (if cancelled)
                    TextColumn::make('cancellation_info')
                        ->html()
                        ->getStateUsing(function ($record) {
                            if (!$record) return null;
                            
                            if ($record->datetime_cancelled) {
                                $date = Carbon::parse($record->datetime_cancelled)->format('M d, Y h:i A');
                                $reason = $record->cancellation_reason ?? 'No reason provided';
                                return "<div class='text-xs text-red-500 mt-1'>Cancelled on: {$date}<br>Reason: {$reason}</div>";
                            }
                            return null;
                        })
                        ->visible(fn ($record) => $record && !is_null($record->datetime_cancelled)),
                        
                    // Decline Info (if declined)
                    TextColumn::make('decline_info')
                        ->html()
                        ->getStateUsing(function ($record) {
                            if (!$record) return null;
                            
                            if ($record->datetime_declined) {
                                $date = Carbon::parse($record->datetime_declined)->format('M d, Y h:i A');
                                $reason = $record->decline_reason ?? 'No reason provided';
                                return "<div class='text-xs text-red-500 mt-1'>Declined on: {$date}<br>Reason: {$reason}</div>";
                            }
                            return null;
                        })
                        ->visible(fn ($record) => $record && !is_null($record->datetime_declined)),
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
                        'cancelled' => 'Cancelled',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value']) || $data['value'] === '') {
                            return $query;
                        }

                        return match ($data['value']) {
                            'pending' => $query->whereNull('booking_id')->whereNull('datetime_declined')->whereNull('datetime_cancelled'),
                            'approved' => $query->whereNotNull('booking_id'),
                            'declined' => $query->whereNotNull('datetime_declined'),
                            'cancelled' => $query->whereNotNull('datetime_cancelled'),
                            default => $query,
                        };
                    })
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservations::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
        ];
    }
}