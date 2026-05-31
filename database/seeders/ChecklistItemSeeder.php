<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\ChecklistItem;
use Carbon\Carbon;

class ChecklistItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            // GROUP 1 – Exterior
            1 => [
                'Front bumper',
                'Rear bumper',
                'Left door',
                'Right door',
                'Hood',
                'Trunk',
                'Windshield / Windows',
                'Headlights / Tail lights',
                'Side mirrors',
                'Front Left Tire',
                'Front Right Tire',
                'Rear Left Tire',
                'Rear Right Tire',
            ],

            // GROUP 2 – Interior
            2 => [
                'Driver seat',
                'Passenger seat',
                'Rear seats',
                'Dashboard / Console',
                'AC / Heater',
                'Horn / Controls',
                'Floor mats',
                'Seat covers',
                'Interior cleanliness',
                'Accessories (USB, sunshades, handles)',
            ],

            // GROUP 3 – Functional
            3 => [
                'Engine start/stop',
                'Headlights / Tail lights',
                'Indicators / Hazard',
                'Wipers / Washer',
                'Horn',
                'Brakes (pedal / handbrake)',
                'Dashboard warning lights',
                'Audio / GPS / Dashcam',
                'AC / Heater function',
            ],
        ];

        Company::each(function ($company) use ($items, $now) {
            foreach ($items as $groupId => $groupItems) {
                foreach ($groupItems as $index => $item) {
                    ChecklistItem::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'group_id'   => $groupId,
                            'item'       => $item,
                        ],
                        [
                            'order'      => $index + 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        });
    }
}
