<?php

namespace App\Filament\Pages;

use App\Models\Addon;
use App\Models\AddonPrice;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Filament\Forms;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tooltip;
use Illuminate\Support\Facades\Http;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionOverview extends Page implements HasTable,HasForms
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = '';
    protected static string $view = 'filament.pages.subscription-overview';
    protected static ?string $slug = 'subscription-overview';
    protected static ?string $title = 'My Subscription';
    public bool $showSubscriptionModal = false;
    public ?int $selectedPlanId = null;
    public ?int $selectedPlanPriceId = null;
    public float $planPriceAmount = 0;
    public float $refundAmount = 0;
    public float $processingFee = 10;
    public float $totalDue = 0;
    public float $addonTotal = 0;
    public ?array $selectedAddonsDetails = [];
    public ?string $voucherCodeInput = null;
    public float $voucherDiscount = 0;
    public ?array $voucherFeedback = [];
    public ?int $voucher_id = null;
    
    public static function shouldRegisterNavigation(): bool
    {
        return false; 
    }

    public function getHeaderActions(): array
    {
        $company = Company::find(Filament::getTenant()?->id);
        $subscription = $company->subscription;
        $isExpired = !$subscription || now()->gt($subscription->ends_at);
        
        return [
            Action::make('subscribe')
                ->label($isExpired ? 'Add Subscription' : 'Change Subscription Plan')
                ->modalHeading($isExpired ? 'Add Subscription' : 'Change Subscription Plan')
                ->modalButton(fn () => __('💳 Pay Now'))
                ->closeModalByClickingAway(false)
                ->form(fn () => $this->getFormSchema())
                ->action(function (array $data) {
                    $planPrice = PlanPrice::findOrFail($data['selectedPlanPriceId']);
                    $plan = $planPrice->plan;

                    $company = Company::find(Filament::getTenant()?->id);
                    $subscription = $company->subscription;

                    $refund = 0;
                    if ($subscription && $subscription->ends_at && now()->lt($subscription->ends_at)) {
                        $daysRemaining = now()->diffInDays($subscription->ends_at);
                        $totalDays = $subscription->starts_at->diffInDays($subscription->ends_at);
                        $refundFraction = $daysRemaining / max($totalDays, 1);
                        $refund = round($subscription->planPrice->price * $refundFraction, 2);
                    }

                    $voucher = null;
                    $discount = 0;

                    if (!empty($data['voucher_code'])) {
                        $voucher = Voucher::where('code', $data['voucher_code'])
                            ->where('active', true)
                            ->whereDate('valid_until', '>=', now())
                            ->first();

                        if (!$voucher) {
                            Notification::make()->title('Invalid or expired voucher.')->danger()->send();
                            return;
                        }

                        if ($voucher->company_ids && !in_array($company->id, $voucher->company_ids)) {
                            Notification::make()->title('Voucher not allowed for your company.')->danger()->send();
                            return;
                        }

                        $globalUsage = VoucherUsage::where('voucher_id', $voucher->id)->count();
                        if ($voucher->usage_limit && $globalUsage >= $voucher->usage_limit) {
                            Notification::make()->title('Voucher usage limit reached.')->danger()->send();
                            return;
                        }

                        $companyUsage = VoucherUsage::where('voucher_id', $voucher->id)
                            ->where('company_id', $company->id)
                            ->count();

                        if ($voucher->per_company_limit && $companyUsage >= $voucher->per_company_limit) {
                            Notification::make()->title('Your company has already used this voucher.')->danger()->send();
                            return;
                        }

                        $discount = $voucher->type === 'fixed'
                            ? $voucher->amount
                            : round($planPrice->price * ($voucher->amount / 100), 2);
                    }

                    $lineItems = [];
                    $addonTotal = 0;
                    $selectedAddonsInfo = [];

                    if (!empty($data['selectedAddons'])) {
                        $addons = AddonPrice::whereIn('addon_id', $data['selectedAddons'])
                            ->where('billing_cycle', $planPrice->billing_cycle)
                            ->with('addon')
                            ->get();

                        foreach ($addons as $ap) {
                            $addonTotal += $ap->calculated_price;
                            $lineItems[] = [
                                'name' => "Add-on: " . $ap->addon->name,
                                'currency' => 'PHP',
                                'amount' => round($ap->calculated_price * 100),
                                'quantity' => 1,
                            ];

                            $selectedAddonsInfo[] = [
                                'addon_id' => $ap->addon_id,
                                'addon_price_id' => $ap->id,
                                'price' => $ap->calculated_price,
                            ];
                        }
                    }

                    $finalPlanPrice = max(($planPrice->price + $this->processingFee) - ($refund + $discount), 0);
                    
                    array_unshift($lineItems, [
                        'name' => $plan->name . ' (' . $planPrice->billing_cycle . ')',
                        'currency' => 'PHP',
                        'amount' => round($finalPlanPrice * 100),
                        'quantity' => 1,
                    ]);

                    $totalAmount = $finalPlanPrice + $addonTotal;

                    $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
                        ->post('https://api.paymongo.com/v1/checkout_sessions', [
                            'data' => [
                                'attributes' => [
                                    'billing' => [
                                        'name' => auth()->user()->name,
                                        'email' => auth()->user()->email,
                                    ],
                                    'line_items' => $lineItems,
                                    'payment_method_types' => ['gcash', 'paymaya', 'card'],
                                    'success_url' => route('subscription.success'),
                                    'cancel_url' => route('subscription.cancel'),
                                ]
                            ]
                        ]);

                    if ($response->failed()) {
                        Notification::make()->title('Payment provider error.')->danger()->send();
                        return;
                    }

                    $checkoutUrl = $response->json('data.attributes.checkout_url');
                    $checkoutSessionId = $response->json('data.id');

                    session()->put('pending_subscription', [
                        'company_id'      => $company->id,
                        'plan_id'         => $plan->id,
                        'plan_price_id'   => $planPrice->id,
                        'plan_name'       => $plan->name,
                        'billing_cycle'   => $planPrice->billing_cycle,
                        'selected_addons' => $selectedAddonsInfo,
                        'refund'          => $refund,
                        'processing_fee'  => $this->processingFee,
                        'subtotal'        => $planPrice->price + $addonTotal + $this->processingFee,
                        'discount'        => $discount,
                        'voucher_id'      => $voucher?->id,
                        'voucher_code'    => $voucher?->code,
                        'total'           => $totalAmount,
                        'checkout_id'     => $checkoutSessionId, 
                    ]);

                    return redirect()->to($checkoutUrl);
                })
                ->beforeFormFilled(function () {
                    $this->selectedPlanId = null;
                    $this->selectedPlanPriceId = null;
                    $this->planPriceAmount = 0;
                    $this->refundAmount = 0;
                    $this->voucherDiscount = 0;
                    $this->addonTotal = 0;
                    $this->voucher_id = null;
                    $this->voucherCodeInput = null;
                    $this->voucherFeedback = null;
                    $this->totalDue = 0;
                }),
        ];
    }

    protected function getFormSchema(): array
    {
        $company = Company::find(Filament::getTenant()?->id);
        $existingCars = $company->cars()->count();

        return [
            Grid::make(['default' => 1, 'lg' => 4])
                ->schema([
                    Group::make()
                        ->schema([
                            Section::make('Subscription Plan')
                                ->description('Choose a plan and optional features for your fleet.')
                                ->icon('heroicon-m-sparkles')
                                ->schema([
                                    // --- PLAN SELECTION ---
                                    Select::make('selectedPlanId')
                                        ->label('Select Plan')
                                        ->options(
                                            Plan::where('is_active', true)
                                                ->where('car_limit', '>=', $existingCars)
                                                ->whereRaw('LOWER(name) NOT LIKE ?', ['%free%'])
                                                ->get()
                                                ->mapWithKeys(fn($plan) => [
                                                    $plan->id => "{$plan->name} (Max {$plan->car_limit} Cars)"
                                                ])
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->helperText("Your current fleet: {$existingCars} cars.")
                                        ->live() // Mas modern kaysa reactive()
                                        ->required()
                                        ->afterStateUpdated(function (callable $set) {
                                            $set('selectedPlanPriceId', null);
                                            $set('selectedAddons', []); // Reset addons pag nagpalit ng plan
                                            $this->resetOrderSummary();
                                        }),

                                    Select::make('selectedPlanPriceId')
                                        ->label('Billing Cycle')
                                        ->prefixIcon('heroicon-m-arrow-path')
                                        ->placeholder(fn ($get) => $get('selectedPlanId') ? 'Select a cycle' : 'Pick a plan first...')
                                        ->options(function (callable $get) {
                                            $planId = $get('selectedPlanId');
                                            if (!$planId) return [];

                                            return PlanPrice::where('plan_id', $planId)
                                                ->get()
                                                ->mapWithKeys(fn($price) => [
                                                    $price->id => strtoupper($price->billing_cycle) . " – ₱" . number_format($price->price, 2),
                                                ]);
                                        })
                                        ->disabled(fn (callable $get) => ! $get('selectedPlanId'))
                                        ->live()
                                        ->afterStateUpdated(function ($get) {
                                            $this->updateOrderSummary($get);
                                        })
                                        ->required(),

                                    // --- ADD-ONS SECTION (NEW) ---
                                    Forms\Components\Placeholder::make('divider')
                                        ->label('')
                                        ->content(new \Illuminate\Support\HtmlString('<hr class="border-t border-gray-200 dark:border-white/10">'))
                                        ->visible(fn ($get) => filled($get('selectedPlanPriceId')))
                                        ->columnSpanFull(),

                                    Forms\Components\CheckboxList::make('selectedAddons')
                                        ->label('Enhance your Plan with Add-ons')
                                        ->helperText('These features will match your chosen billing cycle.')
                                        ->visible(fn ($get) => filled($get('selectedPlanPriceId')))
                                        ->options(function (callable $get) {
                                            $planPrice = PlanPrice::find($get('selectedPlanPriceId'));
                                            if (!$planPrice) return [];

                                            return Addon::active()
                                                ->whereHas('prices', fn($q) => $q->where('billing_cycle', $planPrice->billing_cycle))
                                                ->pluck('name', 'id');
                                        })
                                        // ->descriptions(Addon::active()->pluck('name', 'id'))
                                        ->columnSpanFull()
                                        ->gridDirection('row')
                                        ->live()
                                        ->afterStateUpdated(fn ($get) => $this->updateOrderSummary($get)),

                                ]),

                            Section::make('Promotions')
                                ->schema([
                                    TextInput::make('voucher_code')
                                        ->label('Voucher Code')
                                        ->placeholder('SUMMER2024')
                                        ->extraInputAttributes(['class' => 'uppercase'])
                                        ->suffixAction(
                                            \Filament\Forms\Components\Actions\Action::make('applyVoucher')
                                                ->label('Apply Code')
                                                // ->icon('heroicon-m-check-badge')
                                                ->color('primary')
                                                ->action(fn (callable $get) => $this->handleVoucherApply($get))
                                        ),
                                    View::make('filament.components.voucher-feedback')
                                        ->visible(fn () => filled($this->voucherFeedback)),
                                ]),
                        ])
                        ->columnSpan(['lg' => 2]),

                    // Right Column - Order Summary
                    Group::make()
                        ->schema([
                            Section::make('Order Summary')
                                ->icon('heroicon-m-shopping-bag')
                                ->description('Summary of your selection')
                                ->schema([
                                    View::make('filament.components.order-summary'),
                                    Placeholder::make('disclaimer')
                                        ->label('')
                                        ->content('Prices are inclusive of all local taxes.')
                                        ->extraAttributes(['class' => 'text-xs text-gray-500 italic']),
                                ]),
                        ])
                        ->columnSpan(['lg' => 2]),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Subscription::query()
                ->where('company_id', Filament::getTenant()?->id)
                ->with(['plan', 'planPrice', 'addonSubscriptions.addon'])
                ->orderByDesc('starts_at')
            )
            ->columns([
                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge(),

                TextColumn::make('planPrice.billing_cycle')
                    ->label('Billing Cycle'),

                TextColumn::make('planPrice.price')
                    ->label('Price')
                    ->money('PHP'),
                
                TextColumn::make('processing_fee')
                    ->label('Processing Fee')
                    ->money('PHP'),

                TextColumn::make('addons_summary')
                    ->label('Addons')
                    ->html() // Allows the use of <br/>
                    ->listWithLineBreaks()
                    ->getStateUsing(function ($record) {
                        $addonLines = [];
                        
                        foreach ($record->addonSubscriptions as $addonSub) {
                            $multiplier = match ($record->planPrice->billing_cycle) {
                                'annually' => 12,
                                '6months'  => 6,
                                '3months'  => 3,
                                default    => 1,
                            };

                            $total = (float) ($addonSub->total_paid ?? 0);
                            $unitPrice = $total / max($multiplier, 1);
                            
                            // Building a multiline string for a single addon
                            $addonLines[] = "<strong>{$addonSub->addon->name}</strong><br/>" . 
                                            "₱" . number_format($unitPrice, 2) . " per month<br/>" . 
                                            "{$multiplier} month(s) = ₱" . number_format($total, 2);
                        }

                        return $addonLines; 
                    })
                    ->color('gray')
                    ->placeholder('—'),
                
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('PHP'),

                TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->color('danger')
                    ->money('PHP'),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->html()
                    ->color('danger')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->voucher_code && $state > 0) {
                            return "{$record->voucher_code} <br/> ₱" . number_format($state, 2);
                        }else{
                          if($state > 0){
                            return "₱" . number_format($state, 2);
                          }
                        }

                        return '—';
                    }),

                TextColumn::make('total_due')
                    ->label('Total Due')
                    ->color('success')
                    ->money('PHP'),

                TextColumn::make('starts_at')
                    ->label('Starts At')
                    ->date(),

                TextColumn::make('ends_at')
                    ->label('Ends At')
                    ->date()
                    ->description(fn ($record) =>
                        $record->referral_bonus_days
                            ? "+{$record->referral_bonus_days} days referral bonus"
                            : null
                    ),

                // Tables\Columns\IconColumn::make('auto_renew')
                //     ->boolean()
                //     ->label('Auto Renew'),
            ])
            ->defaultSort('starts_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function handleVoucherApply(callable $get): void
    {
        $code = trim($get('voucher_code'));
        $planPriceId = $get('selectedPlanPriceId');
        $companyId = Filament::getTenant()?->id;

        $this->voucherDiscount = 0;
        $this->voucher_id = null;

        if (!$code) {
            $this->setVoucherError('Voucher code is required.', $get);
            return;
        }

        $voucher = Voucher::where('code', $code)
            ->where('active', true)
            ->whereDate('valid_until', '>=', now())
            ->first();

        if (!$voucher) {
            $this->setVoucherError('Invalid or expired voucher.', $get);
            return;
        }

        if ($voucher->company_ids && !in_array($companyId, $voucher->company_ids)) {
            $this->setVoucherError('Voucher not allowed for your company.', $get);
            return;
        }

        $globalUsage = VoucherUsage::where('voucher_id', $voucher->id)->count();
        if ($voucher->usage_limit && $globalUsage >= $voucher->usage_limit) {
            $this->setVoucherError('Voucher usage limit reached.', $get);
            return;
        }

        $companyUsage = VoucherUsage::where('voucher_id', $voucher->id)
            ->where('company_id', $companyId)
            ->count();

        if ($voucher->per_company_limit && $companyUsage >= $voucher->per_company_limit) {
            $this->setVoucherError('Your company has already used this voucher.', $get);
            return;
        }

        $planPrice = PlanPrice::find($planPriceId);
        if (!$planPrice) {
            $this->setVoucherError('Please select a plan first.', $get);
            return;
        }

        $discount = $voucher->type === 'fixed'
            ? $voucher->amount
            : round($planPrice->price * ($voucher->amount / 100), 2);

        $this->voucherDiscount = $discount;
        $this->voucher_id = $voucher->id;
        $this->voucherFeedback = [
            'type' => 'success',
            'message' => 'Voucher applied successfully. Discount: ₱' . number_format($discount, 2),
        ];

        $this->updateOrderSummary($get);
    }

    protected function setVoucherError(string $message, callable $get)
    {
        $this->voucherFeedback = ['type' => 'error', 'message' => $message];
        $this->voucherDiscount = 0;
        $this->voucher_id = null;
        
        $this->updateOrderSummary($get);
    }

    public function updateOrderSummary(callable $get): void
    {
        $this->planPriceAmount = 0;
        $this->refundAmount = 0;
        $this->totalDue = 0;
        $this->addonTotal = 0;
        $this->selectedAddonsDetails = [];

        $planPriceId = $get('selectedPlanPriceId');
        $selectedAddonIds = $get('selectedAddons') ?? [];

        $planPrice = PlanPrice::find($planPriceId);
        if (!$planPrice) return;

        $this->planPriceAmount = $planPrice->price;

        if (!empty($selectedAddonIds)) {
            $addonPrices = AddonPrice::whereIn('addon_id', $selectedAddonIds)
                ->where('billing_cycle', $planPrice->billing_cycle)
                ->with('addon')
                ->get();

            foreach ($addonPrices as $ap) {
                $this->addonTotal += $ap->calculated_price;

                $multiplier = match ($planPrice->billing_cycle) {
                    'annually' => 12,
                    '6months'  => 6,
                    '3months'  => 3,
                    'monthly'  => 1,
                    default    => 1,
                };

                $unitPrice = $ap->calculated_price / max($multiplier, 1);

                $this->selectedAddonsDetails[] = [
                    'name'       => $ap->addon->name,
                    'price'      => $ap->calculated_price,
                    'breakdown'  => "{$multiplier} x ₱" . number_format($unitPrice, 2),
                    'cycle_text' => $planPrice->billing_cycle
                ];
            }
        }

        $company = Company::find(Filament::getTenant()?->id);
        $subscription = $company?->subscription;

        if ($subscription && $subscription->ends_at && now()->lt($subscription->ends_at)) {
            $daysRemaining = now()->diffInDays($subscription->ends_at);
            $totalDays = $subscription->starts_at->diffInDays($subscription->ends_at);
            $refundFraction = $daysRemaining / max($totalDays, 1);
            $this->refundAmount = round($subscription->planPrice->price * $refundFraction);
        }

        $this->totalDue = max(
            ($this->planPriceAmount + $this->addonTotal + $this->processingFee) 
            - ($this->refundAmount + $this->voucherDiscount), 
            0
        );
    }

    public function resetOrderSummary(): void
    {
        $this->planPriceAmount = 0;
        $this->refundAmount = 0;
        $this->totalDue = 0;
    }
    
}
