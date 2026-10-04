<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->char('calendar_feed_token_hash', 64)->nullable()->unique()->after('google_token');
            $table->timestamp('calendar_feed_token_generated_at')->nullable()->after('calendar_feed_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['calendar_feed_token_hash', 'calendar_feed_token_generated_at']);
        });
    }
};
