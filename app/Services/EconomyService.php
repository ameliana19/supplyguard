<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Economy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EconomyService
{
    /**
     * Sync economy indicators for all countries using highly efficient World Bank bulk API calls
     */
    public function syncAll(): array
    {
        try {
            // Set limit eksekusi agar aman
            set_time_limit(180);

            $year = date('Y') - 4; // Gunakan tahun stabil (contoh: 2022) agar data indikator global lengkap terisi
            $apiUrl = env('WORLDBANK_API_URL', 'https://api.worldbank.org/v2');

            // Kita buat bulk request untuk 5 indikator utama (5 API call total untuk 250+ negara)
            Log::info("Memulai pengambilan data ekonomi World Bank untuk tahun {$year}...");

            $gdpResponse = Http::timeout(25)->get("{$apiUrl}/indicator/NY.GDP.MKTP.CD?format=json&date={$year}&per_page=300");
            $infResponse = Http::timeout(25)->get("{$apiUrl}/indicator/FP.CPI.TOTL.ZG?format=json&date={$year}&per_page=300");
            $unempResponse = Http::timeout(25)->get("{$apiUrl}/indicator/SL.UEM.TOTL.ZS?format=json&date={$year}&per_page=300");
            $expResponse = Http::timeout(25)->get("{$apiUrl}/indicator/TX.VAL.MRCH.CD.WD?format=json&date={$year}&per_page=300");
            $impResponse = Http::timeout(25)->get("{$apiUrl}/indicator/TM.VAL.MRCH.CD.WD?format=json&date={$year}&per_page=300");

            $gdpData = $this->extractIndicatorData($gdpResponse);
            $inflationData = $this->extractIndicatorData($infResponse);
            $unemploymentData = $this->extractIndicatorData($unempResponse);
            $exportsData = $this->extractIndicatorData($expResponse);
            $importsData = $this->extractIndicatorData($impResponse);

            // Master data negara
            $countries = Country::all();
            $successCount = 0;

            foreach ($countries as $country) {
                try {
                    $code = strtoupper($country->code ?? '');
                    if (!$code || $code === '-') continue;

                    // Satuan PDB, Ekspor, Impor diubah ke satuan Miliar Dollar ($)
                    $gdpRaw = $gdpData[$code] ?? 0;
                    $gdp = $gdpRaw ? ($gdpRaw / 1000000000) : 0;

                    $inflation = $inflationData[$code] ?? 0;
                    $unemployment = $unemploymentData[$code] ?? 0;

                    $expRaw = $exportsData[$code] ?? 0;
                    $exports = $expRaw ? ($expRaw / 1000000000) : 0;

                    $impRaw = $importsData[$code] ?? 0;
                    $imports = $impRaw ? ($impRaw / 1000000000) : 0;

                    // Simpan atau update satu record untuk setiap negara
                    Economy::updateOrCreate(
                        [
                            'country_id' => $country->id,
                            'year' => $year,
                        ],
                        [
                            'gdp' => round($gdp, 2),
                            'inflation' => round($inflation, 2),
                            'unemployment' => round($unemployment, 2),
                            'exports' => round($exports, 2),
                            'imports' => round($imports, 2),
                        ]
                    );

                    $successCount++;
                } catch (\Exception $ex) {
                    Log::warning("Gagal menyimpan data ekonomi untuk negara {$country->name}: " . $ex->getMessage());
                    continue;
                }
            }

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan data ekonomi untuk {$successCount} negara.",
                'success_count' => $successCount,
                'fail_count' => 0,
                'errors' => []
            ];

        } catch (\Exception $e) {
            Log::error('EconomyService syncAll Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyinkronkan data ekonomi: ' . $e->getMessage(),
                'success_count' => 0,
                'fail_count' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Helper to map indicator response into country-code-keyed array
     */
    private function extractIndicatorData($response): array
    {
        $mapped = [];
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data[1]) && is_array($data[1])) {
                foreach ($data[1] as $item) {
                    if (!is_array($item)) continue;
                    
                    $countryCode = isset($item['country']['id']) ? strtoupper($item['country']['id']) : '';
                    $value = isset($item['value']) ? $item['value'] : null;
                    
                    if ($countryCode && $value !== null) {
                        $mapped[$countryCode] = floatval($value);
                    }
                }
            }
        }
        return $mapped;
    }
}
