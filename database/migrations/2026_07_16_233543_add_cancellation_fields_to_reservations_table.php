<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->datetime('datetime_cancelled')->nullable()->after('decline_reason');
            $table->string('cancellation_reason')->nullable()->after('datetime_cancelled');
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['datetime_cancelled', 'cancellation_reason']);
        });
    }
};