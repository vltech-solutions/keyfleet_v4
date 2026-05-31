<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Filament\Resources\CarResource\RelationManagers;
use App\Models\Car;
use App\Models\CarType;
use App\Models\Partners;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;

use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class CarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 1;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Fleet Management';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; 
    }

    public static function form(Form $form): Form
    {
        $tabs = [
            Tab::make('General Info')
                ->schema([
                    Grid::make(3)->schema([
                        FileUpload::make('image')
                            ->disk('public')
                            ->directory('cars')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                            ->required()
                            ->resize(50)
                            ->maxSize(2048)
                            ->helperText(fn () => new HtmlString(
                                'For better calendar output, car image should have a transparent background. 
                                <a href="https://www.remove.bg" target="_blank" class="underline text-primary-600">Remove background here</a>.'
                            ))
                            ->columnSpanFull(),

                        Toggle::make('is_available')
                            ->label('Available for Booking')
                            ->default(true)
                            ->columnSpanFull(),

                        Select::make('partner_id')
                            ->label('Partner')
                            ->relationship('partner', 'name')
                            ->searchable()
                            ->nullable()
                            ->helperText('Leave blank if company-owned.')
                            ->columnSpan(1),

                        Select::make('car_type_id')
                            ->label('Car Type')
                            ->relationship('carType', 'car_type')
                            ->required()
                            ->columnSpan(1),

                        Select::make('transmission')
                            ->label('Transmission')
                            ->options([
                                'Automatic' => 'Automatic',
                                'Manual' => 'Manual',
                                'CVT' => 'CVT',
                                'Semi-Automatic' => 'Semi-Automatic',
                                'Dual-Clutch' => 'Dual-Clutch',
                                'Electric' => 'Electric',
                                'Hybrid' => 'Hybrid',
                            ])
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('name')->required()->columnSpan(1),
                        TextInput::make('brand')->required()->columnSpan(1),
                        TextInput::make('model')->required()->columnSpan(1),

                        TextInput::make('year')->required()->columnSpan(1),
                        TextInput::make('color')->required()->columnSpan(1),
                        TextInput::make('plate_number')->required()->columnSpan(1),

                        TextInput::make('seat_count')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('fuel_type')->columnSpan(1),

                        Select::make('coding')
                            ->options([
                                'MONDAY' => 'MONDAY',
                                'TUESDAY' => 'TUESDAY',
                                'WEDNESDAY' => 'WEDNESDAY',
                                'THURSDAY' => 'THURSDAY',
                                'FRIDAY' => 'FRIDAY',
                            ])
                            ->columnSpan(1),

                        TextInput::make('price_starts_at')
                            ->label('Price Per Day')
                            ->numeric()
                            ->required()
                            ->helperText('Displayed in the online booking form. This is not a fixed price.'),

                        Textarea::make('description')
                            ->columnSpanFull()
                            ->helperText('Tell something about this vehicle, this will be shown in booking form')
                            ->visible(fn () => Filament::getTenant()?->hasAddon('booking-pro')),
                    ]),
                ]),
        ];

        $company = auth()->user()->companies()->first();

        if($company->hasAddon('booking-pro')){
            $tabs[] = Tab::make('Images')
                        ->schema([
                            Repeater::make('images')
                                ->relationship() // Ensure this matches your Model's relationship
                                ->schema([
                                    Hidden::make('image_type'),

                                    FileUpload::make('path')
                                        ->image()
                                        ->disk('s3')
                                        ->directory('booking/cars')
                                        ->visibility('public')
                                        ->maxSize(5120)
                                        ->imageEditor()
                                        ->imageResizeTargetWidth('1200')
                                        ->imageResizeMode('contain')
                                        ->optimize('webp')
                                ])
                                ->default(fn () => collect(['thumbnail', 'front', 'back', 'left', 'right', 'interior_front', 'interior_back', 'trunk'])
                                    ->map(fn ($type) => [
                                        'image_type' => $type,
                                        'path' => null,
                                    ])
                                    ->toArray()
                                )
                                ->itemLabel(fn (array $state): ?string => 
                                    isset($state['image_type']) 
                                        ? str($state['image_type'])->replace('_', ' ')->title() 
                                        : null
                                )

                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false) // Set to false to prevent messing up the fixed order
                                ->columns(1)
                                ->grid(4)
                        ]);
        }

        return $form
            ->schema([
                Tabs::make('Car Form')
                    ->tabs($tabs)
                    ->columnSpanFull(),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereNull('deleted_at')
            ->with('partner');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('ownership')
                    ->label('Ownership')
                    ->badge()
                    ->color(fn ($record) => $record->partner ? 'info' : 'gray')
                    ->getStateUsing(fn ($record) => 
                        $record->partner 
                            ? $record->partner->name 
                            : 'Company-Owned'
                    ),

                TextColumn::make('carType.car_type')
                    ->label('Type'),

                TextColumn::make('full_details')
                    ->label('Vehicle Info')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $details = "{$record->brand} {$record->model} ({$record->year})";

                        $extras = [];

                        if (!empty($record->color)) {
                            $extras[] = $record->color;
                        }

                        if (!empty($record->seat_count)) {
                            $extras[] = "{$record->seat_count} seater";
                        }

                        if (!empty($record->fuel_type)) {
                            $extras[] = "{$record->fuel_type}";
                        }

                        if (!empty($record->coding)) {
                            $extras[] = "Coding: {$record->coding}";
                        }

                        $extrasText = implode(' | ', $extras);

                        return "{$details}<br><span style='color:gray;'>{$extrasText}</span>";
                    }),

                ToggleColumn::make('is_available')
                    ->label('Available')
                    ->onColor('success')
                    ->offColor('danger'),
                    
                TextColumn::make('plate_number')
                    ->badge(),

                TextColumn::make('bookings_count')
                    ->label('Bookings Count')
                    ->counts('bookings')
                    ->sortable(),
            ])

            // ->columns([
            //      Grid::make(1) // one column inside each grid item
            //         ->schema([
            //             ViewColumn::make('full_info')
            //                 ->label('Car Info')
            //                 ->view('components.tables.car-info')
            //                 ->extraCellAttributes([
            //                     'class' => 'fi-car-info-cell',
            //                 ])
            //                 ->searchable(['name','brand','model','year','color','plate_number','image','seat_count','fuel_type','coding']),
            //         ]),
            // ])
            // ->contentGrid([
            //     'md' => 2,
            //     'xl' => 3,
            // ])
            ->filters([
                SelectFilter::make('ownership')
                    ->label('Ownership')
                    ->options(function () {
                        return [
                            'company' => 'Company-Owned',
                        ] + Partners::pluck('name', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'company' => $query->whereNull('partner_id'),
                            default => is_numeric($value)
                                ? $query->where('partner_id', $value)
                                : $query,
                        };
                    }),
                SelectFilter::make('car_type_id')
                    ->label('Car Type')
                    ->relationship('carType', 'car_type'),
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->actions(
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\EditAction::make()->color('gray'),
                    Tables\Actions\DeleteAction::make()->color('gray'),
                ] : []
            )
            ->actions([
                Tables\Actions\ActionGroup::make([
                    ...(
                        auth()->user()->hasActiveSubscription()
                            ? [
                                Tables\Actions\EditAction::make()->color('gray'),
                                Tables\Actions\DeleteAction::make()->color('gray'),
                            ]
                            : []
                    ),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-horizontal-circle')
                    ->size(ActionSize::ExtraLarge),
            ])
            ->bulkActions(
                []
            );
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
            'index' => Pages\ListCars::route('/'),
            'create' => Pages\CreateCar::route('/create'),
            'edit' => Pages\EditCar::route('/{record}/edit'),
        ];
    }

    public static function beforeDelete(DeleteRecord $page): void
    {
        if ($page->record->bookings()->exists()) {
            Notification::make()
                ->title('Cannot delete')
                ->danger()
                ->body('This car has bookings and cannot be deleted.')
                ->send();

            $page->halt(); // stop the delete
        }
    }

    protected function mutateFormDataBeforeDelete(array $data): array
    {
        try {
            return parent::mutateFormDataBeforeDelete($data);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                Notification::make()
                    ->title('Cannot Delete')
                    ->body('This record is in use and cannot be deleted.')
                    ->danger()
                    ->send();
            }

            throw $e; // optional: remove if you don't want Laravel to show the error
        }
    }
}
