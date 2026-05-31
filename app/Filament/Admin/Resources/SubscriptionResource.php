<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriptionResource\Pages;
use App\Filament\Admin\Resources\SubscriptionResource\RelationManagers;
use App\Models\Subscription;
use App\Models\PlanPrice;
use App\Services\ReferralRewardService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Plans & Subscriptions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),

                Select::make('plan_price_id')
                    ->label('Plan & Billing Cycle')
                    ->options(function () {
                        return PlanPrice::with('plan')->get()->mapWithKeys(function ($planPrice) {
                            return [
                                $planPrice->id => $planPrice->plan->name . ' - ' . ucfirst($planPrice->billing_cycle) . ' (₱' . number_format($planPrice->price, 2) . ')',
                            ];
                        });
                    })
                    ->searchable()
                    ->reactive() // Mark reactive to listen for changes
                    ->required()
                    ->afterStateUpdated(function (callable $set) {
                        // When plan changes, clear ends_at to force recalculation
                        $set('starts_at', null);
                        $set('ends_at', null);
                    }),

                DatePicker::make('starts_at')
                    ->label('Start Date')
                    ->required()
                    ->reactive() // Listen for changes to starts_at
                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                        $planPriceId = $get('plan_price_id');
                        if (!$planPriceId) {
                            return;
                        }

                        $planPrice = PlanPrice::find($planPriceId);
                        if (!$planPrice) {
                            return;
                        }

                        $billingCycle = $planPrice->billing_cycle;

                        $startDate = Carbon::parse($state);

                        // Calculate end date based on billing cycle
                        $endDate = match ($billingCycle) {
                            'monthly' => $startDate->copy()->addMonth(),
                            '6months' => $startDate->copy()->addMonths(6),
                            'annually' => $startDate->copy()->addYear(),
                            '2years' => $startDate->copy()->addYears(2),
                            default => $startDate->copy()->addMonth(),
                        };

                        $set('ends_at', $endDate->format('Y-m-d'));
                    }),

                DatePicker::make('ends_at')
                    ->label('End Date')
                    ->required(),

                Toggle::make('auto_renew'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable(),

                TextColumn::make('plan_info')
                    ->label('Plan Info')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $plan = $record->planPrice->plan->name ?? 'N/A';
                        $cycle = ucfirst($record->planPrice->billing_cycle ?? 'N/A');
                        $price = $record->planPrice->price 
                            ? '₱' . number_format($record->planPrice->price, 2) 
                            : 'N/A';

                        return "
                            <div style='display: table; width: 100%;'>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray; width: 100px;'>Plan: </div>
                                    <div style='display: table-cell; padding-left: 5px;'>{$plan}</div>
                                </div>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray;'>Cycle: </div>
                                    <div style='display: table-cell; padding-left: 5px;'>{$cycle}</div>
                                </div>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray;'>Price: </div>
                                    <div style='display: table-cell; padding-left: 5px;'>{$price}</div>
                                </div>
                            </div>
                        ";
                    }),

                TextColumn::make('pay_from')
                    ->label('Pay From')
                    ->html()
                    ->getStateUsing(function ($record) {
                        if ($record->processing_fee > 0) {
                            return "
                                <div>
                                    Paymongo Payment<br>
                                    <strong>Fee:</strong> ₱" . number_format($record->processing_fee, 2) . "
                                </div>
                            ";
                        }

                        return '-'; // hides the column if no fee
                    }),
                TextColumn::make('subscription_period')
                    ->label('Subscription Period')
                    ->html()
                    ->grow(false)
                    ->extraAttributes(['class' => 'w-64 truncate'])
                    ->getStateUsing(function ($record) {
                        $start = $record->starts_at
                            ? Carbon::parse($record->starts_at)->format('M d, Y')
                            : 'N/A';

                        $end = $record->ends_at
                            ? Carbon::parse($record->ends_at)->format('M d, Y')
                            : 'N/A';

                        return "
                            <div style='display: table; width: 100%;'>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray; width: 60px;'>Start:</div>
                                    <div style='display: table-cell; padding-left: 5px;'> {$start}</div>
                                </div>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray;'>End:</div>
                                    <div style='display: table-cell; padding-left: 5px;'> {$end}</div>
                                </div>
                            </div>
                        ";
                    }),

                TextColumn::make('id')
                    ->label('Payment Details')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $details = [];
                        
                        if ($record->voucher_code) {
                            $details[] = "<strong>Voucher:</strong> {$record->voucher_code}";
                        }

                        if ($record->discount_amount) {
                            $details[] = "<strong>Discount:</strong> ₱" . number_format($record->discount_amount, 2);
                        }

                        if ($record->processing_fee) {
                            $details[] = "<strong>Processing Fee:</strong> ₱" . number_format($record->processing_fee, 2);
                        }

                        if ($record->payment_source === 'paymongo') {
                            $details[] = "<strong>Marked as:</strong> PayMongo";
                        }

                        if ($record->sub_total) {
                            $details[] = "<strong>Subtotal:</strong> ₱" . number_format($record->sub_total, 2);
                        }

                        if ($record->total_due) {
                            $details[] = "<strong>Total Due:</strong> ₱" . number_format($record->total_due, 2);
                        }

                        if ($record->payment_source) {
                            $details[] = "<strong>Payment Source:</strong> {$record->payment_source}";
                        }

                        if ($record->paid_at) {
                            $details[] = "<strong>Date Paid:</strong> " . \Carbon\Carbon::parse($record->paid_at)->format('F d, Y');
                        }

                        if ($record->net_amount) {
                            $details[] = "<strong>Net Amount:</strong> <span style='color:green;font-weight:bold'>₱" . number_format($record->net_amount, 2)."</span>";
                        }

                        return implode('<br>', $details);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListSubscriptions::route('/'),
            // 'create' => Pages\CreateSubscription::route('/create'),
            // 'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
