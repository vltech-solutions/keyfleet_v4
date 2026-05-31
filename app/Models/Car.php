<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'brand',
        'model',
        'year',
        'color',
        'plate_number',
        'image',
        'seat_count',
        'fuel_type',
        'coding',
        'price_starts_at',
        'transmission',
        'car_type_id',
        'description',
        'is_available'
    ];

    public function partner()
    {
        return $this->belongsTo(Partners::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)
                    ->where('status', 'approved');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public static function isAvailableAt($carId, $startDateTime, $endDateTime = null, $excludeBookingId = null)
    {
        $endDateTime = $endDateTime ?? $startDateTime;

        return ! Booking::where('car_id', $carId)
            ->where('status', 'approved')
            ->when($excludeBookingId, function ($query) use ($excludeBookingId) {
                $query->where('id', '!=', $excludeBookingId);
            })
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where('start_datetime', '<=', $endDateTime)
                    ->where('end_datetime', '>=', $startDateTime);
            })
            ->exists();
    }

    public function getBusyDates()
    {
        $bookings = Booking::where('car_id', $this->id)
            ->where('status', 'approved')
            ->where('start_datetime', '>=',now())
            ->get(['start_datetime', 'end_datetime']);

        $busyDates = [];

        foreach ($bookings as $booking) {
            $period = CarbonPeriod::create(
                $booking->start_datetime->toDateString(), 
                $booking->end_datetime->toDateString()
            );
            
            foreach ($period as $date) {
                $busyDates[] = $date->format('Y-m-d');
            }
        }

        return array_values(array_unique($busyDates)); 
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function carType(): BelongsTo
    {
        return $this->belongsTo(CarType::class);
    }

    public function carDocuments()
    {
        return $this->hasMany(CarDocument::class);
    }

    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    protected static function booted()
    {
        static::saved(function ($car) {
            self::clearCarCache();
        });
        
        static::updating(function ($car) {
            if ($car->isDirty('image') && $car->getOriginal('image')) {
                \Storage::disk('public')->delete($car->getOriginal('image'));
            }

            self::clearCarCache();
        });

        // static::updated(function ($car) {
        //     if ($car->getOriginal('partner_id') !== $car->partner_id && $car->partner_id) {

        //         $partner = $car->partner;

        //         $bookings = $car->bookings()->get();

        //         foreach ($bookings as $booking) {
        //             $baseAmount = match ($partner->commission_base) {
        //                 'total_due' => $booking->total_due ?? 0,
        //                 default     => $booking->total_rent_due ?? 0,
        //             };

        //             if ($partner->commission_type === 'percentage') {
        //                 $companyCommission = ($partner->commission_value / 100) * $baseAmount;
        //             } else {
        //                 $companyCommission = $partner->commission_value;
        //             }

        //             $partnerCommission = $baseAmount - $companyCommission;

        //             $booking->partner_commission = $partnerCommission;
        //             $booking->company_earnings = $companyCommission;
        //             $booking->save();
        //         }
        //     }

        //     else if ($car->getOriginal('partner_id') && is_null($car->partner_id)) {

        //         $bookings = $car->bookings()->get();

        //         foreach ($bookings as $booking) {
        //             $booking->partner_commission = null;
        //             $booking->company_earnings = null;
        //             $booking->save();
        //         }
        //     }

        //     self::clearCarCache();
        // });

        static::deleted(function ($car) {
            self::clearCarCache();
        });
    }

    protected static function clearCarCache()
    {
        $tenant = auth()->user()->companies()->first();
        if ($tenant) {
            $cacheKey = 'events_for_car_' . $tenant->id;
            Cache::forget($cacheKey);
        }
    }

    public function computeRevenue(): float
    {
        $commissionBase = $this->partner?->commission_base ?? 'total_rent_due';

        return $this->bookings->sum(function ($booking) use ($commissionBase) {
            return match ($commissionBase) {
                'total_due' => $booking->total_due ?? 0,
                default     => $booking->total_rent_due ?? 0,
            };
        });
    }

    public function getPartnerNetIncome(): float
    {
        return $this->bookings->sum('paid_amount') - $this->bookings->sum('company_earnings');
    }

    public function getThumbnailOrDefaultUrlAttribute(): ?string
    {
        // Find thumbnail
        $thumbnail = $this->images->firstWhere('image_type', 'thumbnail');

        // If thumbnail exists and has a valid path
        if ($thumbnail && !empty($thumbnail->path)) {
            return Storage::disk('s3')->temporaryUrl($thumbnail->path, now()->addMinutes(5));
        }

        // Fallback to local car image if it exists
        if (!empty($this->image)) {
            return Storage::url($this->image);
        }

        // Optional: fallback to a placeholder if no image exists
        return asset('images/placeholder-car.png');
    }
}
