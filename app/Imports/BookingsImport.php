<?php
namespace App\Imports;

use App\Models\Booking;
use App\Models\BookingPayments;
use App\Models\Car;
use App\Models\Customer;
use App\Models\FundType;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;

class BookingsImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row)
    {
        $failures = [];

        $car = Car::where('name', trim($row['car']))->first();
        if (! $car) {
            $this->onFailure(new Failure(
                0, // Row number is unknown here; will be set internally
                'car',
                ["Car not found: {$row['car']}"],
                $row
            ));
            return null;
        }

        $source = Source::where('source', trim($row['source']))->first();
        if (! $source) {
            $this->onFailure(new Failure(
                0,
                'source',
                ["Source not found: {$row['source']}"],
                $row
            ));
            return null;
        }

        $start = $this->parseDate($row['start_datetime']);
        $end = $this->parseDate($row['end_datetime']);

        $hasConflict = Booking::where('car_id', $car->id)
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_datetime', '<', $start)
                            ->where('end_datetime', '>', $end);
                    });
            })
            ->exists();

        if ($hasConflict) {
            $this->onFailure(new Failure(
                0,
                'start_datetime',
                ["Double booking conflict for car '{$car->name}' from {$start->format('Y-m-d')} to {$end->format('Y-m-d')}"],
                $row
            ));
            return null;
        }

        $days = max($start->diffInDays($end), 1);
        $dailyRate = $row['total_due'] / $days;

        // return new Booking([
        //     'car_id' => $car->id,
        //     'source_id' => $source->id,
        //     'start_datetime' => $start,
        //     'end_datetime' => $end,
        //     'renter_name' => $row['renter_name'] ?? null,
        //     'contact_number' => $row['contact_number'] ?? null,
        //     'destination' => $row['destination'] ?? null,
        //     'total_due' => $row['total_due'] ?? 0,
        //     'paid_amount' => $row['paid_amount'] ?? 0,
        //     'balance' => $row['balance'] ?? 0,
        //     'total_rent_due' => $row['total_due'] ?? 0,
        //     'daily_rate' => $dailyRate,
        //     'days_rented' => $days,
        //     'company_id' => auth()->user()?->companies->first()?->id,
        // ]);

        $companyId = auth()->user()?->companies->first()?->id;

        $existingCustomer = Customer::where('customer_name', $row['renter_name'] ?? null)
            ->where('contact_number', $row['contact_number'] ?? null)
            ->where('company_id', $companyId)
            ->first();

        if (! $existingCustomer) {
            Customer::create([
                'customer_name'  => $row['renter_name'] ?? null,
                'contact_number' => $row['contact_number'] ?? null,
                'company_id'     => $companyId,
                'address'        => $row['renter_address'] ?? '',
                'email'          => $row['email'] ?? '',
                'facebook_name'  => $row['facebook_name'] ?? '',
                'repeat_token'   => Str::random(60),
            ]);
        }

        $booking = new Booking([
            'car_id'        => $car->id,
            'source_id'     => $source->id,
            'start_datetime'=> $start,
            'end_datetime'  => $end,
            'renter_name'   => $row['renter_name'] ?? null,
            'contact_number'=> $row['contact_number'] ?? null,
            'renter_address'=> $row['renter_address'] ?? null,
            'destination'   => $row['destination'] ?? null,
            'total_due'     => $row['total_due'] ?? 0,
            'paid_amount'   => $row['paid_amount'] ?? 0,
            'balance'       => $row['balance'] ?? 0,
            'total_rent_due'=> $row['total_due'] ?? 0,
            'daily_rate'    => $dailyRate,
            'days_rented'   => $days,
            'company_id'    => auth()->user()?->companies->first()?->id,
        ]);

        $booking->save(); 


        if ($booking->paid_amount > 0) {
            $incomeFundId = FundType::where('name', 'Income')
                ->where('company_id', $booking->company_id)
                ->first()?->id;

            BookingPayments::create([
                'booking_id'   => $booking->id,
                'company_id'   => $booking->company_id,
                'amount'       => $booking->paid_amount,
                'payment_date' => Carbon::parse($booking->start_datetime)->format('Y-m-d'),
                'fund_type_id' => $incomeFundId,
            ]);
        }

        return $booking;
    }

    private function parseDate($value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value));
        }

        return Carbon::createFromFormat('d/m/Y H:i', trim($value));
    }
}
