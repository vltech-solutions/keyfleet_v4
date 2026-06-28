<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('access_token')->unique()->nullable()->after('id');
            $table->timestamp('token_expires_at')->nullable()->after('access_token');
        });

        // Generate tokens for existing partners
        $partners = \App\Models\Partners::all();
        foreach ($partners as $partner) {
            $partner->access_token = Str::random(64);
            $partner->save();
        }
    }

    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['access_token', 'token_expires_at']);
        });
    }
};