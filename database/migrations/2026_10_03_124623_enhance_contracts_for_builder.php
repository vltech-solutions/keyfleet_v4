<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('builder_mode')
                ->default('simple')
                ->after('body');

            $table->json('settings')
                ->nullable()
                ->after('builder_mode');

            $table->timestamp('published_at')
                ->nullable()
                ->after('settings');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'builder_mode',
                'settings',
                'published_at',
            ]);
        });
    }
};