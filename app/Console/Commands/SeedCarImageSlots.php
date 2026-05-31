<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Car;
use App\Models\CarImage;

class SeedCarImageSlots extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:seed-car-image-slots';

    /**
     * The console command description.
     */
    protected $description = 'Creates empty car_image rows for every image type for all existing cars';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $types = [
            'thumbnail',
            'front',
            'back',
            'left',
            'right',
            'interior_front',
            'interior_back',
            'trunk'
        ];

        $cars = Car::all();

        if ($cars->isEmpty()) {
            $this->warn('No cars found in the database.');
            return;
        }

        $this->info("Processing {$cars->count()} cars...");

        $bar = $this->output->createProgressBar($cars->count());
        $bar->start();

        foreach ($cars as $car) {
            foreach ($types as $type) {
                // firstOrCreate ensures we don't duplicate if the type already exists
                CarImage::firstOrCreate(
                    [
                        'car_id' => $car->id,
                        'image_type' => $type,
                    ],
                    [
                        'path' => null, // Path is now nullable thanks to your migration
                    ]
                );
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Successfully seeded image slots for all cars.');
    }
}