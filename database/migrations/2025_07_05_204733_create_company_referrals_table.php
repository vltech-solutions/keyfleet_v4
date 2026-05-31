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
        Schema::create('company_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('referred_company_id')->constrained('companies')->cascadeOnDelete();
            $table->boolean('is_converted')->default(false);
            $table->boolean('reward_given')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_referrals');
    }
};
