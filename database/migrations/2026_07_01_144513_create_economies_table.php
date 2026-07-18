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
        // economies table is redundant. economic_data is used instead.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};