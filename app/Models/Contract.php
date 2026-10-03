<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Contract extends Model
{
    use TracksUserAttribution;

    protected $fillable = [
        'company_id',
        'title',
        'body',
        'builder_mode',
        'settings',
        'published_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function clauses(): HasMany
    {
        return $this->hasMany(ContractClause::class)
            ->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Contract Rendering
    |--------------------------------------------------------------------------
    */

    public function render(Booking $booking): string
    {
        $content = $this->body ?? '';

        if ($content === '') {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | Convert local storage images to absolute URLs
        |--------------------------------------------------------------------------
        |
        | TinyMCE may save images using:
        |
        | /storage/uploads/example.jpg
        |
        | When generating PDFs or rendering outside the browser context,
        | an absolute URL is safer.
        |
        */

        $content = preg_replace_callback(
            '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i',
            function (array $matches): string {
                $src = $matches[1];

                if (
                    str_starts_with($src, '/storage/')
                    || str_starts_with($src, 'storage/')
                ) {
                    $normalized = '/' . ltrim($src, '/');

                    return str_replace(
                        $src,
                        url($normalized),
                        $matches[0]
                    );
                }

                return $matches[0];
            },
            $content
        );

        /*
        |--------------------------------------------------------------------------
        | Load Required Relationships
        |--------------------------------------------------------------------------
        */

        $booking->loadMissing([
            'car',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Convert Booking Data to Dot Notation
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | car.name
        | car.brand
        | car.plate_number
        |
        */

        $data = Arr::dot($booking->toArray());

        /*
        |--------------------------------------------------------------------------
        | Additional Dynamic Fields
        |--------------------------------------------------------------------------
        */

        $data['date_today'] = Carbon::now('Asia/Manila')
            ->format('F d, Y');

        /*
        |--------------------------------------------------------------------------
        | Field Formatting
        |--------------------------------------------------------------------------
        */

        $dateTimeKeys = [
            'start_datetime',
            'end_datetime',
        ];

        $dateKeys = [
            'created_at',
            'updated_at',
        ];

        $moneyKeys = [
            'daily_rate',
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
            'driver_fee',
            'security_deposit',
            'partner_commission',
            'company_earnings',
        ];

        foreach ($data as $key => $value) {
            /*
            |--------------------------------------------------------------------------
            | Ignore unsupported values
            |--------------------------------------------------------------------------
            */

            if (is_array($value) || is_object($value)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Handle Null Values
            |--------------------------------------------------------------------------
            */

            if ($value === null) {
                $value = '';
            }

            /*
            |--------------------------------------------------------------------------
            | Date & Time Fields
            |--------------------------------------------------------------------------
            */

            if (
                in_array($key, $dateTimeKeys, true)
                && filled($value)
            ) {
                try {
                    $value = Carbon::parse($value)
                        ->setTimezone('Asia/Manila')
                        ->format('F d, Y h:i A');
                } catch (\Throwable) {
                    // Keep original value if parsing fails.
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Date Fields
            |--------------------------------------------------------------------------
            */

            elseif (
                in_array($key, $dateKeys, true)
                && filled($value)
            ) {
                try {
                    $value = Carbon::parse($value)
                        ->setTimezone('Asia/Manila')
                        ->format('F d, Y');
                } catch (\Throwable) {
                    // Keep original value if parsing fails.
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Money Fields
            |--------------------------------------------------------------------------
            */

            elseif (in_array($key, $moneyKeys, true)) {
                $value = number_format(
                    (float) ($value ?: 0),
                    2
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Boolean Values
            |--------------------------------------------------------------------------
            */

            elseif (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            /*
            |--------------------------------------------------------------------------
            | Replace Placeholder
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | {{renter_name}}
            | {{car.name}}
            | {{total_due}}
            |
            */

            $content = str_replace(
                '{{' . $key . '}}',
                e((string) $value),
                $content
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Replace Known Missing Placeholders
        |--------------------------------------------------------------------------
        |
        | This prevents unused/null fields from remaining visible as:
        |
        | {{some_field}}
        |
        | in the generated contract.
        |
        */

        $content = preg_replace(
            '/\{\{[a-zA-Z0-9_.]+\}\}/',
            '',
            $content
        );

        return $content;
    }
}