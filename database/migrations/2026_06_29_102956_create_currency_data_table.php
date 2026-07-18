<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_data', function (Blueprint $table) {

            $table->id();

            // Nama mata uang
            $table->string('name');

            // Kode mata uang
            $table->string('code',10)->unique();

            // Simbol mata uang
            $table->string('symbol',10);

            // Nilai tukar
            $table->decimal('rate',15,2);

            // Status mata uang
            $table->enum('status',['Stable','Increase','Decrease'])
                  ->default('Stable');

            // Persentase perubahan
            $table->decimal('change_percent',5,2)
                  ->default(0);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_data');
    }
};