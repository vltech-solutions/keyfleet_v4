<?php


namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\Page;
use Filament\Pages\Actions\Action;
use App\Models\Booking;
use App\Models\FundType;
use App\Models\Contract;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\MoneyColumn;
use Filament\Tables\Components\Columns;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ViewBooking extends Page
{
    use \Filament\Actions\Concerns\InteractsWithActions;
    use \Filament\Forms\Concerns\InteractsWithForms;

    protected static string $resource = BookingResource::class;
    protected static string $view = 'filament.resources.booking-resource.pages.view-booking';
    protected static ?string $slug = 'bookings/{record}/view';
    protected static bool $shouldRegisterNavigation = false;
    
    public $record;

    protected $bookingInfolist;

    public $company;

    public function mount($record)
    {
        $this->record = Booking::with(['car', 'inspections', 'payments.fundType'])->findOrFail($record);

        $this->company = filament()->getTenant();

        

        // $this->bookingInfolist = Infolist::make()
        //     ->record($this->record)
        //     ->schema([
        //         Tabs::make('BookingTabs')
        //             ->tabs($tabs)
        //             ->columnSpanFull(),
        //     ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // Define your tabs logic here
        $tabs = [
             // TAB 1: CUSTOM BLADE LAYOUT
            Tabs\Tab::make('Booking Details')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    ViewEntry::make('custom_details')
                        ->view('filament.infolists.booking-details')
                        ->columnSpanFull(),
                ]),

            // TAB 2: PAYMENTS (Standard Filament)
            Tabs\Tab::make('Payments')
                ->icon('heroicon-o-currency-dollar')
                ->schema([
                    ViewEntry::make('payments_view')
                        ->view('filament.infolists.payment-details')
                        ->columnSpanFull(),
                ]),
        ];
        if ($this->company && $this->company->hasAddon('inspection')) {
            $tabs[] = Tabs\Tab::make('Inspections')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                ViewEntry::make('inspections_view')
                                    ->view('filament.infolists.inspections')
                                    ->columnSpanFull(),
                            ]);
        }

        return $infolist
            ->record($this->record)
            ->schema([
                Tabs::make('BookingTabs')
                    ->tabs($tabs)
                    ->columnSpanFull(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Details';
    }

    public function getSubheading(): string
    {

        return $this->record && $this->record->booking_id
            ? 'Booking # ' . $this->record->booking_id
            : 'View Booking';
    }

    /**
     * Helper to keep the mount method clean
     */
    private function calculateDuration($record): string
    {
        if (!$record->start_datetime || !$record->end_datetime) {
            return 'N/A';
        }

        $start = Carbon::parse($record->start_datetime);
        $end = Carbon::parse($record->end_datetime);
        $hours = $start->diffInHours($end);

        return $hours > 24 
            ? floor($hours / 24) . ' days (' . ($hours % 24) . ' hours)' 
            : $hours . ' hours';
    }
  
    public function getContractBody()
    {
        $company = auth()->user()->companies()->first();
        $booking = $this->record;

        $cachedTemplate = Cache::get("contract_template_{$company->id}");
        $body = $cachedTemplate 
            ? (new Contract(['body' => $cachedTemplate]))->render($booking) 
            : $company->contract?->render($booking);

        return str_replace('', '<div class="print-pagebreak"></div>', $body);
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('invoice')
              ->label('Generate Invoice')
              ->icon('heroicon-s-cloud-arrow-down')
              ->action(function () {
                  // I-trigger ang download logic sa loob ng action
                  $booking = $this->record;
                  $company = \App\Models\Company::findOrFail($booking->company_id);
                  $invoiceBlade = explode('.', $company->invoice_template)[0];

                  $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.' . $invoiceBlade, [
                      'invoiceData' => ['company' => $company, 'booking' => $booking]
                  ]);

                  // Ito ang magic part: stream/download nang hindi umaalis sa page
                  return response()->streamDownload(function () use ($pdf) {
                      echo $pdf->output();
                  }, "invoice-{$booking->id}.pdf");
              }),
            
            Action::make('addPayment')
                    ->label('Add Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn () => $this->record->status !== 'cancelled')
                    ->form([
                        Select::make('fund_type_id')
                            ->label('Fund')
                            ->options(fn() => FundType::where('company_id', $this->company->id)->pluck('name', 'id'))
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required(),

                        TextInput::make('payment_notes')
                            ->label('Payment Note'),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $this->record->payments()->create($data);

                        $totalPaid = $this->record->payments()->sum('amount');
                        $this->record->update([
                            'paid_amount' => $totalPaid,
                            'balance' => $this->record->total_due - $totalPaid,
                        ]);

                        Notification::make()
                            ->title('Payment added successfully!')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('md')
                    ->modalHeading('Add Booking Payment')
                    ->modalButton('Save Payment'),

            Action::make('editBooking')
                ->label('Edit Booking')
                ->icon('heroicon-o-pencil-square')
                ->color('secondary')
                ->url(fn () => EditBooking::getUrl([
                    'record' => $this->record->id,
                ])),

            // New Contract Preview action
            Action::make('contractPreview')
              ->label('Contract Preview')
              ->icon('heroicon-m-document-text')
              ->modalHeading('Contract Preview')
              ->modalWidth('4xl') // Malapad para sa dokumento
              ->modalSubmitAction(false) // Alisin ang save button
              ->modalCancelAction(false)
              ->visible(fn () => $this->record && auth()->user()->companies()->first()?->contract)
              ->modalContent(fn () => view('contracts.preview-modal', [
                  'booking' => $this->record,
                  'body' => $this->getContractBody(), // Gawa ka ng method sa class para makuha ang body
              ])),
        ];

        if (
            $this->company
            && (
                $this->company->hasNonBasicPaidSubscription()
                || $this->company->hasActiveFreeSubscription()
                || $this->company->hasAddon('inspection')
            )
        ) {

            $isFinished = $this->record->end_datetime && \Carbon\Carbon::parse($this->record->end_datetime)->isPast();

            if(!$isFinished){
                $actions[] = Action::make('preInspection')
                    ->label(fn () =>
                        $this->record->inspections()->where('type', 'pre')->exists()
                            ? 'View Pre Inspection'
                            : 'Start Pre Inspection'
                    )
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(function () {
                        $pre = $this->record->inspections()->where('type', 'pre')->first();
                        return $pre
                            ? \App\Filament\Pages\ViewInspectionPage::getUrl(['record' => $pre->id])
                            : \App\Filament\Pages\BookingInspectionPage::getUrl([
                                'bookingId' => $this->record->id,
                                'type' => 'pre',
                            ]);
                    });

                $actions[] = Action::make('postInspection')
                ->label(fn () =>
                    $this->record->inspections()->where('type', 'post')->exists()
                        ? 'View Post Inspection'
                        : 'Start Post Inspection'
                )
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->url(function () {
                    $post = $this->record->inspections()->where('type', 'post')->first();
                    return $post
                        ? \App\Filament\Pages\ViewInspectionPage::getUrl(['record' => $post->id])
                        : \App\Filament\Pages\BookingInspectionPage::getUrl([
                            'bookingId' => $this->record->id,
                            'type' => 'post',
                        ]);
                })
                ->visible(fn () => $this->record->inspections()->where('type', 'pre')->exists());
            }
        }

        return [
            ActionGroup::make($actions)
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray')
                ->button(),
        ];
    }



}
