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
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->foreignId('country_id')->after('id')->constrained('countries')->onDelete('cascade');
            $table->decimal('weather_score', 5, 2)->after('country_id');
            $table->decimal('currency_score', 5, 2)->after('weather_score');
            $table->decimal('economy_score', 5, 2)->after('currency_score');
            $table->decimal('port_score', 5, 2)->after('economy_score');
            $table->decimal('total_score', 5, 2)->after('port_score');
            $table->string('risk_level', 20)->after('total_score');
        });

        Schema::table('watchlists', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            $table->foreignId('country_id')->after('user_id')->constrained('countries')->onDelete('cascade');
            $table->string('note', 255)->nullable()->after('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id', 'weather_score', 'currency_score', 'economy_score', 'port_score', 'total_score', 'risk_level']);
        });

        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn(['user_id', 'country_id', 'note']);
        });
    }
};
