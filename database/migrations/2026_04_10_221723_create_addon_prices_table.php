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
        Schema::create('addon_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_id')->constrained()->onDelete('cascade');
            $table->string('billing_cycle'); // monthly, 3_months, 6_months, annual
            $table->decimal('price', 10, 2); 
            $table->integer('discount_percentage')->default(0);
            $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_prices');
    }
};
