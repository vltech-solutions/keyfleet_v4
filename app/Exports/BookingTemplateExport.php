<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingTemplateExport implements WithHeadings
{
    public function headings(): array
    {
        return [
            'car',             // Car name or ID based on your import
            'source',          // Source name or ID
            'start_datetime',  // e.g., 10/06/2025 08:00
            'end_datetime',    // e.g., 11/06/2025 08:00
            'renter_name',
            'contact_number',
            'renter_address',
            'destination',
            'total_due',
            'paid_amount',
            'balance',
        ];
    }
}