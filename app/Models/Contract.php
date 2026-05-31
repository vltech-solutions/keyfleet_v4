<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Contract extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'body',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function render(Booking $booking): string
    {
        $content = $this->body;

        $content = preg_replace_callback('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', function ($matches) {
            $src = $matches[1];
            if (str_starts_with($src, '/storage')) {
                return str_replace($src, url($src), $matches[0]);
            }
            return $matches[0];
        }, $content);

        $data = Arr::dot($booking->loadMissing('car')->toArray());
        $dateToday = ['date_today'];
        // Set Manila timezone for date_today
        $data['date_today'] = Carbon::now('Asia/Manila')->format('F d, Y');

        foreach ($data as $key => $value) {
            if (is_array($value)) continue;

            $dateTimeKeys = ['start_datetime', 'end_datetime'];
            $moneyKeys = ['daily_rate', 'extend_due', 'total_rent_due', 'delivery_fee', 'discount', 'total_due', 'paid_amount', 'balance', 'fuel_charge', 'out_of_bounds', 'damages', 'carwash_fee', 'driver_fee', 'security_deposit', 'partner_commission', 'company_earnings'];
            $dateKeys = ['created_at', 'updated_at'];

            if (in_array($key, $dateTimeKeys)) {
                // Parse and convert to Asia/Manila timezone
                $value = Carbon::parse($value)->setTimezone('Asia/Manila')->format('F d, Y h:i A');
            } elseif (in_array($key, $moneyKeys)) {
                $value = number_format((float) $value, 2);
            } elseif (in_array($key, $dateKeys)) {
                // Parse and convert to Asia/Manila timezone
                $value = Carbon::parse($value)->setTimezone('Asia/Manila')->format('F d, Y');
            } elseif (in_array($key, $dateToday)) {
                $value = Carbon::parse($value)->setTimezone('Asia/Manila')->format('F d, Y');
            }

            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }
        return $content;
    }
}
