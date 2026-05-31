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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('amount', 8, 2); 
            $table->enum('type', ['fixed', 'percentage'])->default('fixed');
            $table->date('valid_until');
            $table->json('company_ids')->nullable(); 
            $table->unsignedInteger('usage_limit')->nullable(); 
            $table->unsignedInteger('per_company_limit')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
