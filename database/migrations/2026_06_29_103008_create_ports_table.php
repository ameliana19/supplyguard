<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {

            $table->id();

            $table->foreignId('country_id')
                  ->constrained('countries')
                  ->onDelete('cascade');

            $table->string('port_name');
            $table->string('port_code')->unique();
            $table->string('city');
            $table->string('type');

            $table->bigInteger('capacity')->default(0);

            $table->enum('status', [
                'Open',
                'Busy',
                'Maintenance',
                'Closed'
            ])->default('Open');

            $table->decimal('latitude',10,7)->nullable();
            $table->decimal('longitude',10,7)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};