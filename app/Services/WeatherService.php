<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Sync weather data from OpenWeather API for a specific country
     */
    public function syncForCountry(int $countryId): array
    {
        try {
            $country = Country::find($countryId);
            
            if (!$country) {
                return [
                    'success' => false,
                    'message' => 'Negara tidak ditemukan.',
                    'count' => 0
                ];
            }

            $city = $country->capital ?? $country->name;
            
            if (!$city || $city === '-') {
                return [
                    'success' => false,
                    'message' => 'Negara tidak memiliki ibu kota yang valid.',
                    'count' => 0
                ];
            }

            $apiKey = env('OPENWEATHER_API_KEY');
            $useSimulated = false;
            $data = null;

            if ($apiKey) {
                try {
                    $response = Http::timeout(3)->retry(2, 50)->get("https://api.openweathermap.org/data/2.5/weather", [
                        'q' => $city,
                        'appid' => $apiKey,
                        'units' => 'metric'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                    } else {
                        Log::warning("Weather API failed for {$city}, status: " . $response->status() . ". Using simulation fallback.");
                        $useSimulated = true;
                    }
                } catch (\Exception $apiEx) {
                    Log::warning("Weather API exception for {$city}: " . $apiEx->getMessage() . ". Using simulation fallback.");
                    $useSimulated = true;
                }
            } else {
                $useSimulated = true;
            }

            if ($useSimulated) {
                // Generate realistic simulated weather based on region/latitude
                $temp = 25.0; // Default
                $humidity = 65;
                $pressure = 1012;
                $windSpeed = 5.0;
                $conditions = ['Clear', 'Clouds', 'Rain', 'Drizzle', 'Thunderstorm'];
                $condition = $conditions[array_rand($conditions)];
                $icon = '01d';

                if ($country->region === 'Asia') {
                    $temp = rand(26, 34); // Tropical
                    $humidity = rand(70, 90);
                } elseif ($country->region === 'Europe') {
                    $temp = rand(10, 22);
                    $humidity = rand(50, 75);
                } else {
                    $temp = rand(15, 28);
                    $humidity = rand(60, 80);
                }

                if ($condition === 'Rain' || $condition === 'Thunderstorm') {
                    $humidity = rand(85, 98);
                    $windSpeed = rand(8, 18);
                    $icon = $condition === 'Thunderstorm' ? '11d' : '10d';
                } elseif ($condition === 'Clouds') {
                    $icon = '03d';
                }

                $data = [
                    'main' => ['temp' => $temp, 'humidity' => $humidity, 'pressure' => $pressure],
                    'wind' => ['speed' => $windSpeed],
                    'weather' => [['main' => $condition, 'icon' => $icon]]
                ];
            }

            // Extract weather data
            $temperature = $data['main']['temp'] ?? 0;
            $humidity = $data['main']['humidity'] ?? 0;
            $pressure = $data['main']['pressure'] ?? 0;
            $windSpeed = $data['wind']['speed'] ?? 0;
            $weatherCondition = $data['weather'][0]['main'] ?? 'Unknown';
            $weatherIcon = $data['weather'][0]['icon'] ?? null;

            WeatherData::updateOrCreate(
                [
                    'country_id' => $countryId,
                    'city' => $city
                ],
                [
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'pressure' => $pressure,
                    'wind_speed' => $windSpeed,
                    'weather_condition' => $weatherCondition,
                    'weather_icon' => $weatherIcon,
                    'recorded_at' => now(),
                ]
            );

            $msg = $useSimulated 
                ? "Data cuaca simulasi untuk {$city} berhasil diterapkan (Mode Cadangan)."
                : "Data cuaca untuk {$city} berhasil disinkronkan dari API.";

            return [
                'success' => true,
                'message' => $msg,
                'count' => 1
            ];

        } catch (\Exception $e) {
            Log::error("WeatherService syncForCountry Exception untuk negara ID {$countryId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Sync weather data for all countries
     */
    public function syncAll(): array
    {
        try {
            // Naikkan limit waktu eksekusi agar tidak timeout pada sinkronisasi besar
            set_time_limit(300);

            $countries = Country::whereNotNull('capital')
                ->where('capital', '!=', '-')
                ->get();
                
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            // Proses per negara dengan jeda kecil agar tidak melampaui rate limit API
            foreach ($countries as $country) {
                $result = $this->syncForCountry($country->id);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "{$country->name}: {$result['message']}";
                }

                // Delay 100ms
                usleep(100000);
            }

            return [
                'success' => true,
                'message' => "Sinkronisasi cuaca selesai. Berhasil: {$successCount}, Gagal: {$failCount}.",
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('WeatherService syncAll Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi cuaca: ' . $e->getMessage(),
                'success_count' => 0,
                'fail_count' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function getWeatherByCity(string $city): ?array
    {
        try {
            $apiKey = env('OPENWEATHER_API_KEY');
            if (!$apiKey) return null;

            $response = Http::timeout(3)->get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('WeatherService getWeatherByCity Error: ' . $e->getMessage());
            return null;
        }
    }
}
