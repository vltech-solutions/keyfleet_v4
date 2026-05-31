<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Cache;
use Filament\Facades\Filament;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Google\Client;
use Google\Service\Calendar\Event;

use Illuminate\Support\Facades\DB;
use Google\Service\Calendar;

class Booking extends Model
{
    // use LogsActivity;
    
    protected $fillable = [
        'booking_id',
        'car_id',
        'source_id',
        'start_datetime',
        'end_datetime',
        'customer_id',
        'renter_name',
        'renter_address',
        'contact_number',
        'destination',
        'delivery_address',
        'return_address',
        'daily_rate',
        'days_rented',
        'extend_hours',
        'extend_due',
        'total_rent_due',
        'delivery_fee',
        'discount',
        'total_due',
        'paid_amount',
        'balance',
        'fuel_charge',
        'out_of_bounds',
        'rfid',
        'damages',
        'carwash_fee',
        'with_driver',
        'driver_fee',
        'insurance',
        'security_deposit',
        'remarks',
        'other_drivers',
        'company_id',
        'partner_commission',
        'company_earnings',
        'google_event_id',
        'status'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'with_driver' => 'boolean',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class)->withTrashed();
    }

    public function partner()
    {
        return $this->car?->partner();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(BookingPayments::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //     ->logOnly(['source_id',
    //     'start_datetime',
    //     'end_datetime',
    //     'renter_name',
    //     'contact_number',
    //     'destination',
    //     'daily_rate',
    //     'days_rented',
    //     'total_rent_due',
    //     'delivery_fee',
    //     'discount',
    //     'total_due',
    //     'paid_amount',
    //     'balance']);
    // }

    public static function expandDatesForBooking(self $model): array
    {
        $model->loadMissing('car'); 

        $start = Carbon::parse($model->start_datetime)->startOfDay();
        $end = Carbon::parse($model->end_datetime)->startOfDay();
        $dates = [];

        while ($start->lte($end)) {
            $dates[] = [
                'id' => $model->id,
                'image' => $model->car->image,
                'car_name' => $model->car->name,
                'renter_name' => $model->renter_name,
                'car_id' => $model->car_id,
                'date' => $start->copy(),
            ];
            $start->addDay();
        }

        return $dates;
    }

    protected static function booted(): void
    {
        static::saving(function ($booking) {
            /*
            |--------------------------------------------------------------------------
            | Generate booking_id (only once)
            |--------------------------------------------------------------------------
            */
            if (! $booking->booking_id && $booking->company_id) {

                $date = $booking->created_at
                    ? Carbon::parse($booking->created_at)
                    : now();

                $dateKey = $date->format('Y-m-d');
                $lastSequence = Booking::whereDate('created_at', $dateKey)
                    ->where('company_id', $booking->company_id)
                    ->whereNotNull('booking_id')
                    ->max(DB::raw('CAST(SUBSTRING_INDEX(booking_id, "-", -1) AS UNSIGNED)')) ?? 0;

                $sequence = $lastSequence + 1;

                $booking->booking_id = sprintf(
                    '%s%s%s-%s-%05d',
                    $date->year,
                    str_pad($date->month, 2, '0', STR_PAD_LEFT),
                    str_pad($date->day, 2, '0', STR_PAD_LEFT),
                    $booking->company_id,
                    $sequence
                );
            }

            if ($booking->car && $booking->car->partner) {
                $booking->partner_commission = $booking->total_due - $booking->company_earnings;
            } else {
                $booking->company_earnings = null;
                $booking->partner_commission = null;
            }

           $customerFields = [
                'customer_name'  => $booking->renter_name,
                'address' => $booking->renter_address,
                'contact_number' => $booking->contact_number,
            ];

            if ($booking->customer_id) {
                $customer = Customer::find($booking->customer_id);
                if ($customer) {
                    if (!$booking->end_datetime || $booking->end_datetime >= now()) {
                        $dirty = false;

                        foreach ($customerFields as $field => $value) {
                            if ($customer->$field !== $value) {
                                $customer->$field = $value;
                                $dirty = true;
                            }
                        }

                        if ($dirty) {
                            $customer->save();
                        }
                    }
                }
            } else {
                $customer = Customer::where('customer_name', $booking->renter_name)
                    ->where('contact_number', $booking->contact_number)
                    ->first();

                if (!$customer) {
                    $customer = Customer::create($customerFields);
                }

                $booking->customer_id = $customer->id;
                $booking->save();
            }
        });
        
         // Create: Append to cache
        static::created(function ($booking) {
            $cacheKey = "events_for_car_".Filament::getTenant()->id;

            if ($booking->status !== 'quotation') {
                if (Cache::has($cacheKey)) {
                    $cached = Cache::get($cacheKey)->toArray();
                    $dates = self::expandDatesForBooking($booking);
                    $updated = array_merge($cached, $dates);

                    Cache::put($cacheKey, collect($updated));
                }
            }

            try {
                if (auth()->user()->google_token) {
                    $booking->syncToGoogleCalendar();
                }
            } catch (\Exception $e) {
                \Log::error("Google Calendar sync failed: {$e->getMessage()}");
                Notification::make()
                    ->title('Google Calendar sync failed')
                    ->body("Your token expired. Please try to reconnect to Google Calendar.")
                    ->error()
                    ->send();
            }
        });

        static::updated(function ($booking) {
            $tenantId = Filament::getTenant()?->id ?? $booking->company_id;

            if (! $tenantId) {
                return;
            }

            $cacheKey = "events_for_car_" . $tenantId;

            if (Cache::has($cacheKey)) {
                $cached = Cache::get($cacheKey);

                $filtered = collect($cached)
                    ->reject(fn ($item) => $item['id'] === $booking->id)
                    ->values();

                if (! in_array($booking->status, ['quotation', 'cancelled'])) {
                    $filtered = $filtered->merge(self::expandDatesForBooking($booking));
                }

                Cache::put($cacheKey, $filtered);
            }

            // update also in google
            // 
            try {
                if (auth()->user()->google_token && $booking->google_event_id) {
                    $booking->updateGoogleCalendarEvent();
                }
            } catch (\Exception $e) {
                \Log::error("Google Calendar sync failed: {$e->getMessage()}");
                // Notification::make()
                //     ->title('Google Calendar sync failed')
                //     ->body("Your token expired. Please try to reconnect to Google Calendar.")
                //     ->error()
                //     ->send();
            }

        });


        static::deleting(function ($booking) {
            $booking->payments->each->delete();

            if($booking->reservation){
                $booking->reservation->update([
                    'booking_id' => null,
                    'status' => 'pending'
                ]);
            }
            
            try {
                if (auth()->user()->google_token && $booking->google_event_id) {
                    $booking->deleteGoogleCalendarEvent($booking);
                }
            } catch (\Exception $e) {
                \Log::error("Google Calendar sync failed: {$e->getMessage()}");
                Notification::make()
                    ->title('Google Calendar sync failed')
                    ->body("Your token expired. Please try to reconnect to Google Calendar.")
                    ->error()
                    ->send();
            }
        });

        static::deleted(function ($booking) {
            $cacheKey = "events_for_car_".Filament::getTenant()->id;

            if ($booking->status !== 'quotation') {
                if (Cache::has($cacheKey)) {
                    $cached = Cache::get($cacheKey);
                    $filtered = $cached->reject(fn($item) => $item['id'] === $booking->id)->values();

                    Cache::put($cacheKey, collect($filtered));
                }
            }
            
        });
    }

    public function reservation(){
        return $this->hasOne(Reservation::class);
    }

    public static function toGoogleEvents()
    {
        return self::query()
            ->where('end_datetime', '>=', now()) 
            ->whereNull('google_event_id')
            ->where('status', 'approved')
            ->get()
            ->map(function ($booking) {
                $pickup = $booking->delivery_address ?: 'Garage';
                $return = $booking->return_address ?: 'Garage';

                $description = "Contact: {$booking->contact_number}\n" .
                            "Pickup: {$pickup}\n" .
                            "Return: {$return}";

                if (! empty($booking->remarks)) {
                    $description .= "\nRemarks: {$booking->remarks}";
                }

                $event = new \Google\Service\Calendar\Event([
                    'summary' => "Booking: {$booking->renter_name} ({$booking->car?->name})",
                    'location' => $booking->destination ?? 'N/A',
                    'description' => $description,
                    'start' => [
                        'dateTime' => \Carbon\Carbon::parse($booking->start_datetime)->format('Y-m-d\TH:i:sP'),
                        'timeZone' => 'Asia/Manila',
                    ],
                    'end' => [
                        'dateTime' => \Carbon\Carbon::parse($booking->end_datetime)->format('Y-m-d\TH:i:sP'),
                        'timeZone' => 'Asia/Manila',
                    ],
                ]);

                return [
                    'booking' => $booking,
                    'event'   => $event,
                ];
            });
    }


    protected function getGoogleClient()
    {
        $user = auth()->user();
        if (!$user || !$user->google_token) {
            return null;
        }

        $tokenData = json_decode($user->google_token, true);

        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');   
        $client->setPrompt('consent');      
        $client->setAccessToken($tokenData);

        // Refresh token if access token expired
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                // keep refresh token if missing
                if (!isset($newToken['refresh_token'])) {
                    $newToken['refresh_token'] = $client->getRefreshToken();
                }
                $client->setAccessToken($newToken);
                $user->update(['google_token' => json_encode($newToken)]);
            } else {
                // no refresh token → user must reconnect
                return null;
            }
        }

        return $client;
    }

    /**
     * Build event description
     */
    protected function buildEventDescription(): string
    {
        $pickup = $this->delivery_address ?: 'Garage';
        $return = $this->return_address ?: 'Garage';

        $description = "Contact: {$this->contact_number}\nPickup: {$pickup}\nReturn: {$return}";

        if (!empty($this->remarks)) {
            $description .= "\nRemarks: {$this->remarks}";
        }

        return $description;
    }

   /**
     * Create or sync Google Calendar event
     */
    public function syncToGoogleCalendar(): void
    {
        $client = $this->getGoogleClient();
        if (!$client) return;

        $service = new Calendar($client);

        $event = new Event([
            'summary' => "Booking: {$this->renter_name} ({$this->car?->name})",
            'location' => $this->destination ?? 'N/A',
            'description' => $this->buildEventDescription(),
            'start' => [
                'dateTime' => Carbon::parse($this->start_datetime)
                    ->setTimezone('Asia/Manila')
                    ->format('Y-m-d\TH:i:sP'),
                'timeZone' => 'Asia/Manila',
            ],
            'end' => [
                'dateTime' => Carbon::parse($this->end_datetime)
                    ->setTimezone('Asia/Manila')
                    ->format('Y-m-d\TH:i:sP'),
                'timeZone' => 'Asia/Manila',
            ],
        ]);

        $createdEvent = $service->events->insert('primary', $event);

        $this->google_event_id = $createdEvent->id;
        $this->save();
    }

    /**
     * Update existing Google Calendar event
     */
    public function updateGoogleCalendarEvent(): void
    {
        // if (!$this->google_event_id) {
        //     $this->syncToGoogleCalendar();
        //     return;
        // }

        $client = $this->getGoogleClient();
        if (!$client) return;

        $service = new Calendar($client);

        $event = new Event([
            'summary' => "Booking: {$this->renter_name} ({$this->car?->name})",
            'location' => $this->destination ?? 'N/A',
            'description' => $this->buildEventDescription(),
            'start' => [
                'dateTime' => Carbon::parse($this->start_datetime)
                    ->setTimezone('Asia/Manila')
                    ->format('Y-m-d\TH:i:sP'),
                'timeZone' => 'Asia/Manila',
            ],
            'end' => [
                'dateTime' => Carbon::parse($this->end_datetime)
                    ->setTimezone('Asia/Manila')
                    ->format('Y-m-d\TH:i:sP'),
                'timeZone' => 'Asia/Manila',
            ],
        ]);

        try {
            $service->events->update('primary', $this->google_event_id, $event);
        } catch (\Exception $e) {
            // fallback: recreate if missing
            $this->syncToGoogleCalendar();
        }
    }

    /**
     * Delete event from Google Calendar
     */
    public function deleteGoogleCalendarEvent($booking): void
    {
        $client = $this->getGoogleClient();
        if (!$client || !$booking->google_event_id) return;

        $service = new Calendar($client);

        try {
            $service->events->delete('primary', $booking->google_event_id);
        } catch (\Exception $e) {
            \Log::error("Failed to delete Google Calendar event: {$e->getMessage()}");
        }
    }

    public function inspections()
    {
        return $this->hasMany(BookingInspection::class);
    }

    public function preInspection()
    {
        return $this->hasOne(BookingInspection::class)->where('type', 'pre');
    }

    public function postInspection()
    {
        return $this->hasOne(BookingInspection::class)->where('type', 'post');
    }
    
}
