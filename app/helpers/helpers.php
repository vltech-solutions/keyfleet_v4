<?php

use App\Models\Car;

if (!function_exists('calculateTotalBookingDue')) {
    function calculateTotalBookingDue(callable $set, callable $get)
    {
        $days = floatval($get('days_rented')) ?: 1;
        $daily = floatval($get('daily_rate'));
        $extend = floatval($get('extend_due'));
        $delivery = floatval($get('delivery_fee'));
        $discount = floatval($get('discount'));
        $paid = floatval($get('paid_amount'));

        $fuel = floatval($get('fuel_charge'));
        $bounds = floatval($get('out_of_bounds'));
        $rfid = floatval($get('rfid'));
        $damage = floatval($get('damages'));
        $carwash = floatval($get('carwash_fee'));
        $driver_fee = floatval($get('driver_fee'));
        $insurance = floatval($get('insurance'));
        $security_deposit = floatval($get('security_deposit'));

        $totalRent = ($days * $daily) + $extend;
        $additional = $fuel + $bounds + $rfid + $damage + $carwash + $driver_fee + $insurance + $security_deposit;

        $totalDue = $totalRent + $delivery + $additional - $discount;
        $balance = $totalDue - $paid;

        $set('total_rent_due', $totalRent);
        $set('total_due', $totalDue);
        $set('balance', $balance);

        $carId = $get('car_id'); // ensure car_id is available in your form
        $car = Car::find($carId);
        $partner = $car?->partner;

        if ($partner) {
            $baseAmount = match ($partner->commission_base) {
                'total_due' => $totalDue,
                default     => $totalRent,
            };

            if ($partner->commission_type === 'percentage') {
                $companyCommission = ($partner->commission_value / 100) * $baseAmount;
            } else {
                $companyCommission = $partner->commission_value;
            }

            $set('company_earnings', round($companyCommission, 2));
        } else {
            $set('company_earnings', 0);
        }
    }
}