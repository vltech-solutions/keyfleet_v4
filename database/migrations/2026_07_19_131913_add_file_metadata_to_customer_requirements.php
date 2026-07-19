<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customer_requirements', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('path');
            $table->bigInteger('file_size')->nullable()->after('file_name');
            $table->string('mime_type')->nullable()->after('file_size');
        });
    }

    public function down()
    {
        Schema::table('customer_requirements', function (Blueprint $table) {
            $table->dropColumn(['file_name', 'file_size', 'mime_type']);
        });
    }
};