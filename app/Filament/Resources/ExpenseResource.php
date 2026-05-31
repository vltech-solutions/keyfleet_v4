<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ExpenseImporter;
use App\Filament\Exports\ExpenseExporter;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Imports\ExpenseImport;
use App\Exports\ExpenseTemplateExport;
use App\Exports\ExpenseExport;
use App\Filament\Resources\ExpenseResource\Widgets\ExpenseOverview;
use App\Models\FundType;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\MaxWidth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Radio::make('type')
                    ->label('Expense Category')
                    ->options([
                        'car' => 'Car Related',
                        'other' => 'General Expense',
                    ])
                    ->default('car')
                    ->inline()
                    ->reactive()
                    ->afterStateHydrated(function ($set, $record) {
                        if ($record) {
                            $set('type', $record->car_id ? 'car' : 'other');
                        }
                    })
                    ->afterStateUpdated(fn ($set) => $set('car_id', null) && $set('other_payment_type', null)),

                Grid::make(3)
                    ->schema([
                        Select::make('car_id')
                            ->label('Select Car')
                            ->relationship('car', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('type') === 'car')
                            ->required(fn ($get) => $get('type') === 'car')
                            ->columnSpan(2),

                        Select::make('other_payment_type')
                            ->label('Category')
                            ->options([
                                'Operations' => [
                                    'Vehicle Insurance' => 'Vehicle Insurance',
                                    'Registration' => 'Registration',
                                    'LTO Fees' => 'LTO Fees',
                                    'Cleaning & Detailing' => 'Cleaning & Detailing',
                                    'Toll Fee' => 'Toll Fee',
                                    'Parking' => 'Parking',
                                    'Fuel' => 'Fuel',
                                ],
                                'Admin/Fixed' => [
                                    'Office Supplies' => 'Office Supplies',
                                    'Utility Bills' => 'Utility Bills',
                                    'Staff Salaries' => 'Staff Salaries',
                                    'Rent' => 'Rent',
                                    'Taxes' => 'Taxes',
                                ],
                                'Marketing' => [
                                    'Ads & Marketing' => 'Ads & Marketing',
                                ],
                                'Personal' => [
                                    'Transportation' => 'Transportation',
                                    'Food' => 'Food',
                                    'Personal Advance' => 'Personal Advance',
                                    'Others' => 'Others',
                                ],
                            ])
                            ->visible(fn ($get) => $get('type') === 'other')
                            ->required(fn ($get) => $get('type') === 'other')
                            ->searchable()
                            ->columnSpan(2),

                        DatePicker::make('date')
                            ->native(false)
                            ->default(now())
                            ->required()
                            ->columnSpan(1),
                    ]),

                TextInput::make('expense_description')
                    ->label('Description / Particulars')
                    ->placeholder('e.g. Monthly maintenance or Office rental for March')
                    ->required()
                    ->columnSpanFull(),

                Fieldset::make('Financial Impact')
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('PHP')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => static::checkFundBalance($state, $get('fund_type_id'), $set)),

                                Forms\Components\Toggle::make('deduct_to_fund')
                                    ->label('Deduct to Fund')
                                    ->default(true)
                                    ->reactive()
                                    ->inline(false),

                                Forms\Components\Select::make('fund_type_id')
                                    ->label('Fund Source')
                                    ->relationship('fundType', 'name', fn ($query) => $query->where('name', '!=', "Partner's Fund"))
                                    ->visible(fn ($get) => $get('deduct_to_fund'))
                                    ->required(fn ($get) => $get('deduct_to_fund'))
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => static::checkFundBalance($get('amount'), $state, $set)),
                            ])
                            ->columns(3),
            ]);
    }

    protected static function checkFundBalance($amount, $fundTypeId, callable $set)
    {
        if (!$amount || !$fundTypeId) {
            return;
        }

        $fundType = FundType::find($fundTypeId);
        if (!$fundType) {
            return;
        }

        if ($fundType->balance < $amount) {
            Notification::make()
                ->warning()
                ->title('Insufficient Fund Balance')
                ->body("The selected fund '{$fundType->name}' does not have enough balance to cover this amount.")
                ->send();

            $set('fund_type_id', null);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('car.name')
                    ->label('Reference')
                    ->getStateUsing(function ($record) {
                        return $record->car?->name ?? $record->other_payment_type;
                    })
                    ->description(fn ($record) => $record->car_id ? 'Vehicle Expense' : 'General Expense')
                    // ->icon(fn ($record) => $record->car_id ? 'heroicon-m-truck' : 'heroicon-m-user')
                    ->color('primary')
                    ->badge()
                    ->searchable(['other_payment_type']),

                TextColumn::make('expense_description')
                    ->label('Description / Particulars')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('expense_description')
                    ->label('Description / Particulars')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('fundType.name')
                    ->label('Source')
                    ->formatStateUsing(fn ($state, $record) => $record->deduct_to_fund ? $state : 'Cash/Direct')
                    ->badge()
                    ->color(fn ($state) => $state === 'Cash/Direct' ? 'gray' : 'success'),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->money('PHP')
                    ->alignRight()
                    ->color('danger')
                    ->extraAttributes(['class' => 'font-bold'])
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('PHP')
                            ->label('Total'),
                    ]),
            ])
            ->filters([
                SelectFilter::make('car')
                    ->relationship('car', 'name')
                    ->label('Filter by Car'),

                Tables\Filters\TernaryFilter::make('is_car_related')
                    ->label('Expense Type')
                    ->placeholder('All Expenses')
                    ->trueLabel('Vehicle Only')
                    ->falseLabel('General Only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('car_id'),
                        false: fn (Builder $query) => $query->whereNull('car_id'),
                    ),

                SelectFilter::make('other_payment_type')
                    ->label('General Expense Category')
                    ->options([
                        'Operations' => [
                            'Vehicle Insurance' => 'Vehicle Insurance',
                            'Registration' => 'Registration',
                            'LTO Fees' => 'LTO Fees',
                            'Cleaning & Detailing' => 'Cleaning & Detailing',
                            'Toll Fee' => 'Toll Fee',
                            'Parking' => 'Parking',
                            'Fuel' => 'Fuel',
                        ],
                        'Admin/Fixed' => [
                            'Office Supplies' => 'Office Supplies',
                            'Utility Bills' => 'Utility Bills',
                            'Staff Salaries' => 'Staff Salaries',
                            'Rent' => 'Rent',
                            'Taxes' => 'Taxes',
                        ],
                        'Marketing' => [
                            'Ads & Marketing' => 'Ads & Marketing',
                        ],
                        'Personal' => [
                            'Transportation' => 'Transportation',
                            'Food' => 'Food',
                            'Personal Advance' => 'Personal Advance',
                            'Others' => 'Others',
                        ],
                    ])
                    ->searchable(),

                Filter::make('date_range')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->label('From')->native(false),
                            DatePicker::make('until')->label('To')->native(false),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    ...(
                        auth()->user()->hasActiveSubscription()
                            ? [
                                Tables\Actions\EditAction::make()->color('gray')->modalWidth(MaxWidth::FourExtraLarge),
                                Tables\Actions\DeleteAction::make()->color('gray'),
                            ]
                            : []
                    ),
                ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-horizontal-circle')
                ->size(ActionSize::ExtraLarge),
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
            'index' => Pages\ListExpenses::route('/'),
            // 'create' => Pages\CreateExpense::route('/create'),
            // 'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
