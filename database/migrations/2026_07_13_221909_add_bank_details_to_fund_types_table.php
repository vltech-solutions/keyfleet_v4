<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fund_types', function (Blueprint $table) {
            $table->string('account_number')->nullable()->after('balance');
            $table->string('account_name')->nullable()->after('account_number');
            $table->string('qr_code')->nullable()->after('account_name');
        });
    }

    public function down()
    {
        Schema::table('fund_types', function (Blueprint $table) {
            $table->dropColumn([
                'account_number',
                'account_name',
                'qr_code'
            ]);
        });
    }
};