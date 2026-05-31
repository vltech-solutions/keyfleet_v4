<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\FundType;
use App\Models\BookingPayments;
use App\Models\Company;

class MigrateBookingPayments extends Command
{
    protected $signature = 'migrate:booking-payments';
    protected $description = 'Migrate existing bookings into booking payments with proper fund allocations';

    public function handle()
    {
        $count = 0;

        // Ensure each company has Income & Partner's Fund
        Company::chunk(100, function ($companies) {
            foreach ($companies as $company) {
                FundType::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name'       => 'Income',
                    ],
                    [
                        'balance' => 0,
                    ]
                );

                FundType::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name'       => "Partner's Fund",
                    ],
                    [
                        'balance' => 0,
                    ]
                );
            }
        });

        // Process bookings
        Booking::with('car.partner')->chunk(100, function ($bookings) use (&$count) {
            foreach ($bookings as $booking) {
                if ($booking->paid_amount > 0) {
                    $paymentDate = $booking->created_at;

                    $incomeFundId = FundType::where('name', 'Income')
                        ->where('company_id', $booking->company_id)
                        ->first()?->id;

                    $partnerFundId = FundType::where('name', "Partner's Fund")
                        ->where('company_id', $booking->company_id)
                        ->first()?->id;

                    // Calculate missing commissions if null
                    if ($booking->car && $booking->car->partner && (is_null($booking->company_earnings) || is_null($booking->partner_commission))) {
                        $partner = $booking->car->partner;

                        $baseAmount = match ($partner->commission_base) {
                            'total_due' => $booking->total_due,
                            default     => $booking->total_rent,
                        };

                        if ($partner->commission_type === 'percentage') {
                            $companyCommission = ($partner->commission_value / 100) * $baseAmount;
                        } else {
                            $companyCommission = $partner->commission_value;
                        }

                        $companyCommission  = round($companyCommission, 2);
                        $partnerCommission  = round($baseAmount - $companyCommission, 2);

                        // Save to booking
                        $booking->company_earnings   = $companyCommission;
                        $booking->partner_commission = $partnerCommission;
                        $booking->save();
                    }

                    // Pull latest values
                    $companyCommission = $booking->company_earnings ?? 0;
                    $partnerCommission = $booking->partner_commission ?? 0;
                    $amountPaid        = $booking->paid_amount;

                    if ($booking->car && $booking->car->partner) {
                        if ($amountPaid <= $companyCommission) {
                            BookingPayments::create([
                                'booking_id'    => $booking->id,
                                'company_id'    => $booking->company_id,
                                'amount'        => $amountPaid,
                                'payment_date'  => $paymentDate,
                                'payment_notes' => '',
                                'fund_type_id'  => $incomeFundId,
                            ]);
                        } else {
                            BookingPayments::create([
                                'booking_id'    => $booking->id,
                                'company_id'    => $booking->company_id,
                                'amount'        => $companyCommission,
                                'payment_date'  => $paymentDate,
                                'payment_notes' => '',
                                'fund_type_id'  => $incomeFundId,
                            ]);

                            BookingPayments::create([
                                'booking_id'    => $booking->id,
                                'company_id'    => $booking->company_id,
                                'amount'        => $amountPaid - $companyCommission,
                                'payment_date'  => $paymentDate,
                                'payment_notes' => '',
                                'fund_type_id'  => $partnerFundId,
                            ]);
                        }
                    } else {
                        BookingPayments::create([
                            'booking_id'    => $booking->id,
                            'company_id'    => $booking->company_id,
                            'amount'        => $amountPaid,
                            'payment_date'  => $paymentDate,
                            'payment_notes' => '',
                            'fund_type_id'  => $incomeFundId,
                        ]);
                    }

                    $count++;
                }
            }
        });

        $this->info("Migration complete. {$count} payments created.");
    }
}
