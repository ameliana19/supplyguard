<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_data', function (Blueprint $table) {

            $table->id();

            $table->foreignId('country_id')
                  ->constrained('countries')
                  ->onDelete('cascade');

            $table->decimal('gdp', 15, 2);

            $table->decimal('inflation', 5, 2);

            $table->decimal('unemployment', 5, 2);

            $table->decimal('exports', 15, 2);

            $table->decimal('imports', 15, 2);

            $table->year('year');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_data');
    }
};