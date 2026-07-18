<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->string('container_number');
            $table->string('cargo_type');
            $table->foreignId('origin_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('destination_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('origin_port_id')->nullable()->constrained('ports')->onDelete('set null');
            $table->foreignId('destination_port_id')->nullable()->constrained('ports')->onDelete('set null');
            $table->datetime('estimated_departure');
            $table->datetime('estimated_arrival');
            $table->string('status')->default('Pending'); // Pending, In Transit, Arrived, Delayed, Cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
