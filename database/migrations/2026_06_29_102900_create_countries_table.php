<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 10)->nullable()->unique();
            $table->string('capital')->nullable();
            $table->string('region')->nullable();
            $table->string('currency')->nullable();

            $table->bigInteger('population')->nullable();

            // ✅ FIX PENTING: HARUS NULLABLE
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};