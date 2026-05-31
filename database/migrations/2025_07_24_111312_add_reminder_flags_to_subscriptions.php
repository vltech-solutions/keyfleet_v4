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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('reminder_sent_7d')->default(false);
            $table->boolean('reminder_sent_3d')->default(false);
            $table->boolean('reminder_sent_1d')->default(false);
            $table->boolean('reminder_sent_0d')->default(false);
            $table->boolean('reminder_sent_after3d')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
};
