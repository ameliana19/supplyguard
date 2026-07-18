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
        Schema::table('currency_data', function (Blueprint $table) {
            // Drop unique index on code
            $table->dropUnique('currency_data_code_unique');
            // Add country_id foreign key referencing countries table
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_data', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->unique('code');
        });
    }
};
