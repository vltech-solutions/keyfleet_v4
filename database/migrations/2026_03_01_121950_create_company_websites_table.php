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
        Schema::create('company_websites', function (Blueprint $table) {
            $table->id();
            $table->text('header_text')->nullable();
            $table->text('subheader')->nullable();
            $table->text('banner')->nullable();
            $table->longText('about_us')->nullable();
            $table->longText('business_address')->nullable();
            $table->foreignId('company_id')->default(1)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_websites');
    }
};
