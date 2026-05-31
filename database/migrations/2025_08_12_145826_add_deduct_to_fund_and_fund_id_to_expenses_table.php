<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('deduct_to_fund')->default(false)->after('amount');
            $table->foreignId('fund_type_id')->nullable()->after('deduct_to_fund')->constrained('fund_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['fund_type_id']);
            $table->dropColumn(['deduct_to_fund', 'fund_type_id']);
        });
    }
};
