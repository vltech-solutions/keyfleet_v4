<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CompanyResource\Pages;
use App\Filament\Admin\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Accounts';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['subscription'])
            ->withCount(['cars', 'bookings']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('avatar_url')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ]),
                TextInput::make('name')
                    ->required(),
                Textarea::make('address')
                    ->required(),
                Textarea::make('contacts')
                    ->required(),
                ColorPicker::make('primary_color')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(''),
                TextColumn::make('name')
                    ->label('Company')
                    ->searchable(),
                TextColumn::make('cars_count')
                    ->label('Cars'),

                TextColumn::make('bookings_count')
                    ->label('Bookings'),
                TextColumn::make('latest_booking')
                    ->label('Latest Booking')
                    ->getStateUsing(function ($record) {
                        $latestBooking = $record->bookings()->latest('created_at')->first();

                        if (!$latestBooking) {
                            return 'N/A';
                        }

                        return Carbon::parse($latestBooking->created_at)->format('M d, Y h:i A');
                    }),
                TextColumn::make('created_at')
                    ->label('Date Registered')
                    ->getStateUsing(function ($record) {
                        if (!$record->created_at) {
                            return 'N/A';
                        }
                        return 
                            Carbon::parse($record->created_at)->format('F d, Y ');
                    }),
                TextColumn::make('subscription_info')
                    ->label('Plan / Expires')
                    ->getStateUsing(function ($record) {
                        if (!$record->subscription) {
                            return 'N/A';
                        }

                        $planName = $record->subscription->plan ? $record->subscription->plan->name : 'No Plan';
                        $endsAt = $record->subscription->ends_at 
                            ? Carbon::parse($record->subscription->ends_at)->format('M d, Y') 
                            : 'N/A';

                        return "{$planName} / {$endsAt}";
                    }),

                TextColumn::make('days_to_expire')
                    ->label('Days Left')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->subscription || !$record->subscription->ends_at) {
                            return 'N/A';
                        }

                        $endsAt = \Carbon\Carbon::parse($record->subscription->ends_at);
                        $now = \Carbon\Carbon::now();

                        if ($now->gt($endsAt)) {
                            return 'Expired';
                        }

                        return round($now->diffInDays($endsAt)) . ' day(s)';
                    })
                    ->color(function ($record) {
                        if (!$record->subscription || !$record->subscription->ends_at) {
                            return 'gray';
                        }

                        $endsAt = \Carbon\Carbon::parse($record->subscription->ends_at);
                        $now = \Carbon\Carbon::now();

                        if ($now->gt($endsAt)) {
                            return 'danger';
                        }

                        if(round($now->diffInDays($endsAt)) < 10 && round($now->diffInDays($endsAt)) > 0) {
                            return 'warning';
                        }

                        return round($now->diffInDays($endsAt)) > 10 ? 'success' : 'danger';
                    }),
                
            ])
            ->filters([
                // Status filter
                SelectFilter::make('status')
                    ->label('Subscription Status')
                    ->options([
                        // '' => 'All',          // added All option
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? 'all';

                        if ($value === 'active') {
                            return $query->whereHas('subscription', function ($q) {
                                $q->where('ends_at', '>=', now());
                            });
                        }

                        if ($value === 'inactive') {
                            return $query->whereDoesntHave('subscription', function ($q) {
                                $q->where('ends_at', '>=', now());
                            });
                        }

                        // 'all' returns unfiltered
                        return $query;
                    }),

                // Plan filter
                SelectFilter::make('plan')
                    ->label('Plan')
                    ->options(\App\Models\Plan::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? '';

                        if ($value === '') {
                            return $query; // no filter
                        }

                        return $query->whereHas('subscription', function ($q) use ($value) {
                            $q->where('plan_id', $value);
                        });
                    }),
            ])


            ->actions([
                Tables\Actions\ViewAction::make(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
            'view' => Pages\ViewCompany::route('/{record}'),
        ];
    }
}
