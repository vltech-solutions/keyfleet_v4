<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CustomerExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Customer::withCount('bookings')->get();
    }

    public function map($customer): array
    {
        return [
            $customer->customer_name ?? '',
            $customer->address ?? '',
            $customer->contact_number ?? '',
            $customer->email ?? '',
            $customer->facebook_name ?? '',
            $customer->bookings_count ?? '0'
        ];
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Address',
            'Contact Number',
            'Email',
            'Facebook Name',
            'Bookings Count'
        ];
    }
}