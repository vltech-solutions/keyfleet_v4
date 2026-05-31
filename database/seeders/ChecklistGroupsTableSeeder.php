<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ChecklistGroup;

class ChecklistGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['name' => 'Exterior', 'order' => 1],
            ['name' => 'Interior', 'order' => 2],
            ['name' => 'Functions / Operational', 'order' => 3],
            ['name' => 'Odometer / Fuel / Toll Reading', 'order' => 4],
        ];

        foreach ($groups as $group) {
            ChecklistGroup::updateOrCreate(
                ['name' => $group['name']],
                ['order' => $group['order']]
            );
        }
    }
}
