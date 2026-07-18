<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Sync currency rates from ExchangeRate API for all countries in master table
     */
    public function syncRates(): array
    {
        try {
            // Naikkan limit waktu eksekusi
            set_time_limit(180);

            // Ambil data nilai tukar terbaru dari ExchangeRate API (hanya 1 API call)
            $response = Http::timeout(30)->get('https://open.er-api.com/v6/latest/USD');

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data dari ExchangeRate API.',
                    'count' => 0
                ];
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

            // Ambil data nama mata uang asli dari openexchangerates API
            $currencyNames = [];
            try {
                $namesResponse = Http::timeout(15)->get('https://openexchangerates.org/api/currencies.json');
                if ($namesResponse->successful()) {
                    $currencyNames = $namesResponse->json();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch currency names from openexchangerates: ' . $e->getMessage());
            }

            // Fallback kamus nama dan simbol mata uang umum
            $fallbackCurrencyNames = [
                'USD' => ['name' => 'United States Dollar', 'symbol' => '$'],
                'EUR' => ['name' => 'Euro', 'symbol' => '€'],
                'GBP' => ['name' => 'British Pound Sterling', 'symbol' => '£'],
                'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
                'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
                'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
                'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
                'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
                'THB' => ['name' => 'Thai Baht', 'symbol' => '฿'],
                'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
                'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
                'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'Fr'],
                'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
                'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩'],
                'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$'],
                'RUB' => ['name' => 'Russian Ruble', 'symbol' => '₽'],
                'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
                'MXN' => ['name' => 'Mexican Peso', 'symbol' => '$'],
                'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱'],
                'VND' => ['name' => 'Vietnamese Dong', 'symbol' => '₫'],
            ];

            // Ambil data symbol mata uang asli dari mledoze countries dataset
            $currencySymbols = [];
            try {
                $symbolsResponse = Http::timeout(15)->get('https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json');
                if ($symbolsResponse->successful()) {
                    $rawCountries = $symbolsResponse->json();
                    if (is_array($rawCountries)) {
                        foreach ($rawCountries as $cData) {
                            if (isset($cData['currencies']) && is_array($cData['currencies'])) {
                                foreach ($cData['currencies'] as $cCode => $info) {
                                    $symbol = $info['symbol'] ?? null;
                                    if ($symbol) {
                                        $currencySymbols[strtoupper($cCode)] = $symbol;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch currency symbols: ' . $e->getMessage());
            }

            // Master data negara
            $countries = Country::all();
            $count = 0;

            foreach ($countries as $country) {
                $code = strtoupper($country->currency ?? '');
                
                if (!$code || $code === '-') {
                    continue;
                }

                $rate = $rates[$code] ?? null;
                if (!$rate || $rate <= 0) {
                    // Fallback default jika kode rate tidak ditemukan di API (misal USD base rate = 1)
                    $rate = ($code === 'USD') ? 1.00 : null;
                }

                if ($rate === null) {
                    continue;
                }

                // Cari riwayat nilai tukar sebelumnya untuk country ini
                $existingCurrency = Currency::where('country_id', $country->id)->first();
                $previousRate = $existingCurrency ? (float) $existingCurrency->rate : null;

                $status = 'Stable';
                $changePercent = 0.00;

                if ($previousRate && $previousRate > 0) {
                    if ($rate > $previousRate) {
                        $status = 'Increase';
                    } elseif ($rate < $previousRate) {
                        $status = 'Decrease';
                    }
                    $changePercent = (($rate - $previousRate) / $previousRate) * 100;
                }

                $name = $currencyNames[$code] ?? ($fallbackCurrencyNames[$code]['name'] ?? $code);
                $symbol = $currencySymbols[$code] ?? ($fallbackCurrencyNames[$code]['symbol'] ?? $code);

                // Simpan satu record mata uang untuk setiap negara
                Currency::updateOrCreate(
                    ['country_id' => $country->id],
                    [
                        'name' => $name,
                        'code' => $code,
                        'symbol' => $symbol,
                        'rate' => $rate,
                        'status' => $status,
                        'change_percent' => round($changePercent, 2),
                    ]
                );

                $count++;
            }

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$count} data mata uang negara.",
                'count' => $count
            ];

        } catch (\Exception $e) {
            Log::error('CurrencyService syncRates Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi mata uang: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }

    public function convert(float $amount, string $from, string $to): ?float
    {
        try {
            $fromCurrency = Currency::where('code', $from)->first();
            $toCurrency = Currency::where('code', $to)->first();

            if (!$fromCurrency || !$toCurrency) return null;

            $usdAmount = $amount / $fromCurrency->rate;
            $result = $usdAmount * $toCurrency->rate;

            return round($result, 2);
        } catch (\Exception $e) {
            Log::error('CurrencyService convert Error: ' . $e->getMessage());
            return null;
        }
    }
}
