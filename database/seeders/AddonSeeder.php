<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addon = \App\Models\Addon::create([
            'name' => 'Keyfleet Booking PRO',
            'slug' => 'booking-pro',
            'is_active' => true
        ]);

        $prices = [
            ['cycle' => 'monthly', 'discount' => 0],
            ['cycle' => '3_months', 'discount' => 5],
            ['cycle' => '6_months', 'discount' => 10],
            ['cycle' => 'annual', 'discount' => 20],
        ];

        foreach ($prices as $p) {
            \App\Models\AddonPrice::create([
                'addon_id' => $addon->id,
                'billing_cycle' => $p['cycle'],
                'price' => 0, // Base monthly price
                'discount_percentage' => $p['discount']
            ]);
        }

        $addon = \App\Models\Addon::create([
            'name' => 'Keyfleet Smart Inspection',
            'slug' => 'inspection',
            'is_active' => true
        ]);

        $prices = [
            ['cycle' => 'monthly', 'discount' => 0],
            ['cycle' => '3_months', 'discount' => 5],
            ['cycle' => '6_months', 'discount' => 10],
            ['cycle' => 'annual', 'discount' => 20],
        ];

        foreach ($prices as $p) {
            \App\Models\AddonPrice::create([
                'addon_id' => $addon->id,
                'billing_cycle' => $p['cycle'],
                'price' => 0, // Base monthly price
                'discount_percentage' => $p['discount']
            ]);
        }
    }
}
