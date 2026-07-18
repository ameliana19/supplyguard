<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::query()->delete();

        Country::insert([
            [
                'name' => 'Indonesia',
                'code' => 'ID',
                'flag' => 'https://flagcdn.com/w320/id.png',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'currency' => 'IDR',
                'population' => 277000000,
                'latitude' => -6.2,
                'longitude' => 106.8,
            ],
            [
                'name' => 'Malaysia',
                'code' => 'MY',
                'flag' => 'https://flagcdn.com/w320/my.png',
                'capital' => 'Kuala Lumpur',
                'region' => 'Asia',
                'currency' => 'MYR',
                'population' => 34000000,
                'latitude' => 3.1,
                'longitude' => 101.6,
            ],
            [
                'name' => 'Japan',
                'code' => 'JP',
                'flag' => 'https://flagcdn.com/w320/jp.png',
                'capital' => 'Tokyo',
                'region' => 'Asia',
                'currency' => 'JPY',
                'population' => 125000000,
                'latitude' => 35.6,
                'longitude' => 139.7,
            ],
            [
                'name' => 'China',
                'code' => 'CN',
                'flag' => 'https://flagcdn.com/w320/cn.png',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'currency' => 'CNY',
                'population' => 1412000000,
                'latitude' => 35.8617,
                'longitude' => 104.1954,
            ],
            [
                'name' => 'United States',
                'code' => 'US',
                'flag' => 'https://flagcdn.com/w320/us.png',
                'capital' => 'Washington D.C.',
                'region' => 'Americas',
                'currency' => 'USD',
                'population' => 331000000,
                'latitude' => 37.0902,
                'longitude' => -95.7129,
            ],
            [
                'name' => 'Singapore',
                'code' => 'SG',
                'flag' => 'https://flagcdn.com/w320/sg.png',
                'capital' => 'Singapore',
                'region' => 'Asia',
                'currency' => 'SGD',
                'population' => 5600000,
                'latitude' => 1.3521,
                'longitude' => 103.8198,
            ],
        ]);
    }
}