<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SyncBookingsWithCustomers extends Command
{
    protected $signature = 'sync:bookings-customers';
    protected $description = 'Sync unique renter_name, address, contact_number, and company_id from bookings into customers table, then update bookings with customer_id';

    public function handle()
    {
        $this->info("Syncing bookings with customers...");
        $count = 0;
        // Step 1: Get all unique renters by renter_name + contact_number + company_id
        $renters = Booking::select(
            'renter_name',
            'company_id',
            DB::raw('MIN(contact_number) as contact_number'),
            DB::raw('MIN(renter_address) as renter_address')
        )
        ->whereNotNull('renter_name')
        ->where('renter_name', '!=', '')
        ->groupBy('renter_name', 'company_id')
        ->get();

        foreach ($renters as $renter) {
            // Step 2: Check if already exists in customers
            $customer = Customer::where('customer_name', $renter->renter_name)
                ->where('contact_number', $renter->contact_number)
                ->where('company_id', $renter->company_id)
                ->first();

            if (! $customer) {
                $customer = Customer::create([
                    'customer_name' => $renter->renter_name ?? '',
                    'address' => $renter->renter_address ?? '',
                    'contact_number' => $renter->contact_number ?? '',
                    'company_id' => $renter->company_id,
                    'email' => null, // since nullable
                ]);
                $this->info("Inserted new customer: {$customer->customer_name} ({$renter->company_id})");
                $count++;
            }

            // Step 3: Update bookings to point to customer_id
            Booking::where('renter_name', $renter->renter_name)
                // ->where('contact_number', $renter->contact_number)
                // ->where('renter_address', $renter->renter_address)
                ->where('company_id', $renter->company_id)
                ->update(['customer_id' => $customer->id]);

        }

        $this->info("Sync completed successfully! Total customers created: {$count}");
    }
}
