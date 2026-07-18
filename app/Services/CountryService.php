<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    /**
     * Import countries from REST Countries API (mledoze/countries raw JSON backup endpoint)
     */
    public function importFromAPI(): array
    {
        try {
            // Mengambil data lengkap (250+ negara) dari endpoint handal yang stabil
            $response = Http::timeout(120)->get('https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json');

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data dari REST Countries API. Status: ' . $response->status(),
                    'count' => 0
                ];
            }

            $countries = $response->json();
            
            if (!is_array($countries)) {
                return [
                    'success' => false,
                    'message' => 'Format respon API tidak valid.',
                    'count' => 0
                ];
            }

            $count = 0;

            foreach ($countries as $countryData) {
                $name = $countryData['name']['common'] ?? null;
                $code = $countryData['cca2'] ?? null;
                
                if (!$name || !$code) continue;

                $code = strtoupper($code);

                // Extraction data
                $capital = isset($countryData['capital']) && is_array($countryData['capital']) && !empty($countryData['capital'])
                    ? $countryData['capital'][0]
                    : '-';
                $region = $countryData['region'] ?? '-';
                
                $currency = '-';
                if (isset($countryData['currencies']) && is_array($countryData['currencies']) && !empty($countryData['currencies'])) {
                    $currency = array_key_first($countryData['currencies']);
                }
                
                $population = $countryData['population'] ?? 0;
                $latitude = isset($countryData['latlng']) && is_array($countryData['latlng']) && isset($countryData['latlng'][0])
                    ? $countryData['latlng'][0]
                    : null;
                $longitude = isset($countryData['latlng']) && is_array($countryData['latlng']) && isset($countryData['latlng'][1])
                    ? $countryData['latlng'][1]
                    : null;
                
                // Gunakan flagcdn secara dinamis dan handal
                $flag = "https://flagcdn.com/w320/" . strtolower($code) . ".png";

                // Mencegah data duplikat jika country seeder awal tidak memiliki code
                Country::where('name', $name)
                    ->where(function ($query) {
                        $query->whereNull('code')->orWhere('code', '');
                    })
                    ->update(['code' => $code]);

                // Simpan atau update
                Country::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'flag' => $flag,
                        'capital' => $capital,
                        'region' => $region,
                        'currency' => $currency,
                        'population' => $population,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]
                );

                $count++;
            }

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$count} negara.",
                'count' => $count
            ];

        } catch (\Exception $e) {
            Log::error('CountryService Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke REST Countries API. Periksa koneksi internet Anda. Error: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }

    public function getByCode(string $code): ?array
    {
        try {
            $response = Http::timeout(30)->get("https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json");
            if ($response->successful()) {
                $countries = $response->json();
                foreach ($countries as $c) {
                    if (strtoupper($c['cca2'] ?? '') === strtoupper($code)) {
                        return $c;
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('CountryService getByCode Error: ' . $e->getMessage());
            return null;
        }
    }

    public function searchByName(string $name): array
    {
        try {
            $response = Http::timeout(30)->get("https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json");
            $results = [];
            if ($response->successful()) {
                $countries = $response->json();
                foreach ($countries as $c) {
                    if (stripos($c['name']['common'] ?? '', $name) !== false) {
                        $results[] = $c;
                    }
                }
            }
            return $results;
        } catch (\Exception $e) {
            Log::error('CountryService searchByName Error: ' . $e->getMessage());
            return [];
        }
    }
}
