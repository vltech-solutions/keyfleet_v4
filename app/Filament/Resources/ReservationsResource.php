<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationsResource\Pages;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
                Reservation::query()
                    ->with(['car', 'customer'])
                    ->orderByDesc('created_at')
            )
            ->recordUrl(
                fn ($record) => Pages\ViewReservation::getUrl([
                    'record' => $record->id,
                ])
            )
            ->columns([
                /*
                |--------------------------------------------------------------------------
                | Mobile
                |--------------------------------------------------------------------------
                */

                ViewColumn::make('mobile_reservation')
                    ->label('')
                    ->view('filament.tables.columns.reservation-mobile-card')
                    ->hiddenFrom('md')
                    ->grow()
                    ->extraAttributes([
                        'class' => '!w-full !max-w-none !whitespace-normal !p-0',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Desktop
                |--------------------------------------------------------------------------
                */

                ImageColumn::make('car.image')
                    ->label('')
                    // ->width(60)
                    // ->height(48)
                    // ->extraImgAttributes([
                    //     'class' => 'object-contain',
                    // ])
                    ->visibleFrom('md'),

                TextColumn::make('car.name')
                    ->label('Vehicle')
                    ->searchable()
                    ->weight('semibold')
                    ->description(function ($record) {
                        $vehicle = trim(
                            ($record->car?->brand ?? '')
                            . ' '
                            . ($record->car?->model ?? '')
                        );

                        return collect([
                            $vehicle,
                            $record->car?->plate_number,
                        ])
                            ->filter()
                            ->implode(' · ');
                    })
                    ->formatStateUsing(
                        fn ($state, $record) => $record->car?->trashed()
                            ? "{$state} (Deleted)"
                            : ($state ?: 'Vehicle unavailable')
                    )
                    ->color(
                        fn ($record) => $record->car?->trashed()
                            ? 'danger'
                            : null
                    )
                    ->visibleFrom('md'),

                TextColumn::make('customer.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->weight('medium')
                    ->description(
                        fn ($record) => $record->customer?->contact_number ?: null
                    )
                    ->visibleFrom('md'),

                TextColumn::make('reservation_period')
                    ->label('Reservation Period')
                    ->html()
                    ->getStateUsing(function ($record) {
                        if (! $record->start_date || ! $record->end_date) {
                            return "
                                <span class='text-sm text-gray-400'>
                                    Schedule unavailable
                                </span>
                            ";
                        }

                        $start = Carbon::parse($record->start_date);
                        $end = Carbon::parse($record->end_date);

                        $startLabel = $start->format('M d, Y · h:i A');
                        $endLabel = $end->format('M d, Y · h:i A');

                        $hours = (int) floor(
                            $start->diffInMinutes($end) / 60
                        );

                        if ($hours < 24) {
                            $duration = $hours . ' '
                                . ($hours === 1 ? 'hour' : 'hours');
                        } else {
                            $days = intdiv($hours, 24);
                            $remaining = $hours % 24;

                            $duration = $days . ' '
                                . ($days === 1 ? 'day' : 'days');

                            if ($remaining > 0) {
                                $duration .= ' · ' . $remaining . ' hr';
                            }
                        }

                        return "
                            <div class='min-w-[210px] space-y-1'>
                                <div class='text-sm font-medium text-gray-800 dark:text-gray-200'>
                                    {$startLabel}
                                </div>

                                <div class='text-xs text-gray-400'>
                                    to {$endLabel}
                                </div>

                                <div class='pt-1 text-[11px] font-medium text-gray-500 dark:text-gray-400'>
                                    {$duration}
                                </div>
                            </div>
                        ";
                    })
                    ->visibleFrom('md'),

                TextColumn::make('reservation_fee')
                    ->label('Reservation Fee')
                    ->money('PHP')
                    ->alignRight()
                    ->color(
                        fn ($record) => ($record->reservation_fee ?? 0) > 0
                            ? null
                            : 'gray'
                    )
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => ucfirst($state ?: 'pending')
                    )
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'declined' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M d, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'declined' => 'Declined',
                        'cancelled' => 'Cancelled',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            'pending' => $query
                                ->whereNull('booking_id')
                                ->whereNull('datetime_declined')
                                ->whereNull('datetime_cancelled'),

                            'approved' => $query
                                ->whereNotNull('booking_id'),

                            'declined' => $query
                                ->whereNotNull('datetime_declined'),

                            'cancelled' => $query
                                ->whereNotNull('datetime_cancelled'),

                            default => $query,
                        };
                    }),

                SelectFilter::make('selected_car_id')
                    ->label('Vehicle')
                    ->relationship('car', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('reservation_period')
                    ->label('Reservation Period')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('From'),

                                DatePicker::make('until')
                                    ->label('To'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date) =>
                                    $query->whereDate(
                                        'start_date',
                                        '>=',
                                        $date
                                    )
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date) =>
                                    $query->whereDate(
                                        'start_date',
                                        '<=',
                                        $date
                                    )
                            );
                    }),
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormWidth('md')
            ->paginated([10, 25, 50, 100])
            ->bulkActions([])
            ->actions([]);
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