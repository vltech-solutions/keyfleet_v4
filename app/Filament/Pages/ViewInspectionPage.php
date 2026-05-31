<?php

namespace App\Filament\Pages;

use App\Models\BookingInspection;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Actions\Action as ButtonAction;
use Illuminate\Support\Facades\Storage;

class ViewInspectionPage extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.view-inspection-page';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'view-inspection';

    public $record;

    public static function getRoutePath(): string
    {
        return '/view-inspection/{record}';
    }

    public function mount($record): void
    {
        // Using the same eager loading as your controller
        $this->record = BookingInspection::with([
            'booking.customer', 
            'booking.car', 
            'booking.company',
            'items'
        ])->findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ButtonAction::make('downloadPdf')
          
                ->label('Download Report')
                ->icon('heroicon-m-printer')
                ->color('success')
                ->action(function () {
                        // Access the record directly from the Page property
                  $record = $this->record; 

                  $record->load(['booking.car', 'items', 'booking.customer', 'booking.company']);

                  // Process S3 URLs
                  $record->items->each(function ($item) {
                    if ($item->photo_path) {
                      $item->temp_url = \Illuminate\Support\Facades\Storage::disk('s3')
                        ->temporaryUrl($item->photo_path, now()->addMinutes(15));
                    }
                  });

                  $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.inspection-pdf', [
                    'inspection' => $record,
                  ])->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                  ]);

                  // Ito ang magic part: stream/download nang hindi umaalis sa page
                  return response()->streamDownload(function () use ($pdf) {
                      echo $pdf->output();
                  }, "Inspection-{$record->id}.pdf");
              }),

            ButtonAction::make('goToBooking')
                ->label('Back to Booking')
                ->color('gray')
                ->icon('heroicon-m-arrow-left')
                ->url(fn () => "/app/{$this->record->booking->company->slug}/bookings/{$this->record->booking_id}/view")
        ];
    }

    public function inspectionInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Vehicle & Trip Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('booking.car.brand')
                            ->label('Vehicle')
                            ->formatStateUsing(fn ($record) => "{$record->booking->car->brand} {$record->booking->car->model}"),
                        
                        TextEntry::make('booking.car.plate_number')
                            ->label('Plate Number')
                            ->badge(),

                        TextEntry::make('type')
                            ->label('Inspection Phase')
                            ->badge()
                            ->color(fn ($state) => $state === 'Pre Inspection' ? 'warning' : 'success'),

                        TextEntry::make('odo')->label('Odometer Reading')->numeric()->suffix(' KM'),
                        
                        ViewEntry::make('gas')
                            ->label('Fuel Level')
                            ->view('filament.components.fuel-level'),

                        TextEntry::make('created_at')->label('Inspection Date')->dateTime(),
                    ]),

                Section::make('Technical & Mechanical Audit')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        \Filament\Infolists\Components\KeyValueEntry::make('functions')
                            ->label('Function Check')
                            ->keyLabel('Component')
                            ->valueLabel('Status')
                            ->getStateUsing(function ($record) {
                                // We manually map the JSON keys to readable Labels
                                return collect($record->functions)->mapWithKeys(function ($value, $key) {
                                    $cleanLabel = ucwords(str_replace('_', ' ', $key));
                                    $statusText = $value ? '✅ PASS' : '❌ FAIL';
                                    return [$cleanLabel => $statusText];
                                })->toArray();
                            }),

                        \Filament\Infolists\Components\KeyValueEntry::make('tires')
                            ->label('Tire Condition')
                            ->keyLabel('Position')
                            ->valueLabel('Status')
                            ->getStateUsing(function ($record) {
                                return collect($record->tires)->mapWithKeys(function ($value, $key) {
                                    $cleanLabel = ucwords(str_replace('_', ' ', $key));
                                    return [$cleanLabel => strtoupper($value)];
                                })->toArray();
                            }),
                    ]),

                Section::make('Condition Report (Damages & Issues)')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->hidden(fn ($record) => $record->items->isEmpty())
                            ->grid(2) // 2 columns for better professional layout
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        ImageEntry::make('photo_path') // Matching controller field name
                                            ->label('')
                                            ->disk('s3')
                                            ->size(120)
                                            ->state(function ($record) {
                                                return $record->photo_path 
                                                    ? Storage::disk('s3')->temporaryUrl($record->photo_path, now()->addMinutes(10)) 
                                                    : null;
                                            })
                                            ->extraImgAttributes(['class' => 'rounded-lg border shadow-sm']),

                                        TextEntry::make('zone_id')
                                            ->label('Zone')
                                            ->prefix('Area: ')
                                            ->weight('bold'),

                                        TextEntry::make('condition')
                                            ->badge()
                                            ->color(fn ($state) => match($state) {
                                                'damaged' => 'danger',
                                                'fair' => 'warning',
                                                default => 'success'
                                            }),

                                        TextEntry::make('notes')
                                            ->columnSpanFull()
                                            ->placeholder('No specific notes provided.')
                                    ])
                            ]),

                        TextEntry::make('empty_state')
                            ->label('')
                            ->default('✅ No visual damages or issues recorded.')
                            ->hidden(fn ($record) => $record->items->isNotEmpty())
                            ->color('success'),
                    ]),
            ]);
    }
}