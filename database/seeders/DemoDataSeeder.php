<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Port;
use App\Models\WeatherData;
use App\Models\Economy;
use App\Models\RiskScore;
use App\Models\Article;
use App\Models\Profile;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Countries
        $this->call(CountrySeeder::class);

        // Seed Currencies (use insertOrIgnore to avoid duplicates)
        Currency::insertOrIgnore([
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'rate' => 1.00, 'status' => 'Stable', 'change_percent' => 0.00],
            ['name' => 'Indonesian Rupiah', 'code' => 'IDR', 'symbol' => 'Rp', 'rate' => 16500.00, 'status' => 'Stable', 'change_percent' => 0.00],
            ['name' => 'Malaysian Ringgit', 'code' => 'MYR', 'symbol' => 'RM', 'rate' => 4.75, 'status' => 'Stable', 'change_percent' => 0.00],
            ['name' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥', 'rate' => 150.00, 'status' => 'Stable', 'change_percent' => 0.00],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'rate' => 0.92, 'status' => 'Stable', 'change_percent' => 0.00],
        ]);

        $indonesia = Country::where('name', 'Indonesia')->first();
        $malaysia = Country::where('name', 'Malaysia')->first();
        $japan = Country::where('name', 'Japan')->first();
        $china = Country::where('name', 'China')->first();
        $usa = Country::where('name', 'United States')->first();
        $singapore = Country::where('name', 'Singapore')->first();

        // Seed Ports using PortService
        app(\App\Services\PortService::class)->syncPorts();

        // Seed Weather Data
        if ($indonesia) {
            WeatherData::insert([
                ['country_id' => $indonesia->id, 'city' => 'Jakarta', 'temperature' => 32.5, 'humidity' => 75, 'wind_speed' => 12.5, 'weather_condition' => 'Clouds', 'pressure' => 1013, 'weather_icon' => '03d', 'recorded_at' => now()],
            ]);
        }

        if ($malaysia) {
            WeatherData::insert([
                ['country_id' => $malaysia->id, 'city' => 'Kuala Lumpur', 'temperature' => 30.0, 'humidity' => 85, 'wind_speed' => 8.0, 'weather_condition' => 'Rain', 'pressure' => 1010, 'weather_icon' => '10d', 'recorded_at' => now()],
            ]);
        }

        if ($japan) {
            WeatherData::insert([
                ['country_id' => $japan->id, 'city' => 'Tokyo', 'temperature' => 25.0, 'humidity' => 60, 'wind_speed' => 15.0, 'weather_condition' => 'Clear', 'pressure' => 1015, 'weather_icon' => '01d', 'recorded_at' => now()],
            ]);
        }

        // Seed Economy Data
        if ($indonesia) {
            Economy::insert([
                ['country_id' => $indonesia->id, 'gdp' => 1200000000000, 'inflation' => 3.5, 'unemployment' => 5.2, 'exports' => 250000000000, 'imports' => 220000000000, 'year' => 2024],
            ]);
        }

        if ($malaysia) {
            Economy::insert([
                ['country_id' => $malaysia->id, 'gdp' => 450000000000, 'inflation' => 2.8, 'unemployment' => 3.5, 'exports' => 300000000000, 'imports' => 280000000000, 'year' => 2024],
            ]);
        }

        if ($japan) {
            Economy::insert([
                ['country_id' => $japan->id, 'gdp' => 5000000000000, 'inflation' => 2.2, 'unemployment' => 2.5, 'exports' => 700000000000, 'imports' => 750000000000, 'year' => 2024],
            ]);
        }

        // Seed Risk Scores
        if ($indonesia) {
            RiskScore::insert([
                ['country_id' => $indonesia->id, 'weather_score' => 45.0, 'currency_score' => 60.0, 'economy_score' => 50.0, 'port_score' => 30.0, 'total_score' => 46.25, 'risk_level' => 'Medium'],
            ]);
        }

        if ($malaysia) {
            RiskScore::insert([
                ['country_id' => $malaysia->id, 'weather_score' => 55.0, 'currency_score' => 40.0, 'economy_score' => 35.0, 'port_score' => 25.0, 'total_score' => 38.75, 'risk_level' => 'Medium'],
            ]);
        }

        if ($japan) {
            RiskScore::insert([
                ['country_id' => $japan->id, 'weather_score' => 25.0, 'currency_score' => 30.0, 'economy_score' => 25.0, 'port_score' => 20.0, 'total_score' => 25.0, 'risk_level' => 'Low'],
            ]);
        }

        // Seed News Articles via NewsService syncNews
        app(\App\Services\NewsService::class)->syncNews();

        // Seed Profile for admin user
        $adminUser = \App\Models\User::where('email', 'suciameliana19@gmail.com')->first();
        if ($adminUser) {
            Profile::updateOrCreate(
                ['user_id' => $adminUser->id],
                [
                    'full_name' => 'Admin',
                    'photo' => null,
                    'phone_number' => null,
                    'company' => 'SupplyGuard',
                    'address' => null,
                    'role' => 'Administrator',
                ]
            );
        }
    }
}
