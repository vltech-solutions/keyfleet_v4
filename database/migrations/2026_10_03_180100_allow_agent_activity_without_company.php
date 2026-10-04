<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('activitylog.table_name'), function (Blueprint $table): void {
            $table->unsignedBigInteger('company_id')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Agent users legitimately have no tenant company, so null audit rows cannot
        // be safely converted back to the legacy company_id default.
    }
};
