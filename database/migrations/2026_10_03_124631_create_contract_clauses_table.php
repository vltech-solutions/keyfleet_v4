<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_clauses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type')->default('custom');
            $table->string('title');
            $table->longText('body');

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->index([
                'contract_id',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_clauses');
    }
};