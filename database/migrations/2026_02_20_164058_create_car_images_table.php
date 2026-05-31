<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')
                ->constrained('cars') // assumes you have a cars table
                ->onDelete('cascade'); // delete images if car is deleted
            $table->string('image_type'); // front, back, left, right, interior_front, interior_back, trunk
            $table->string('path'); // S3 path
            $table->timestamps();

            $table->unique(['car_id', 'image_type']); // prevent duplicates per car
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_images');
    }
};