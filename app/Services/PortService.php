<?php

namespace App\Services;

use App\Models\Port;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

class PortService
{
    /**
     * Synchronize ports from the curated and global sea-ports dataset
     */
    public function syncPorts(): array
    {
        try {
            // 1. Ambil data kurasi port yang valid
            $curatedPorts = $this->getCuratedPorts();
            $curatedCodes = [];
            $validPortCodes = [];
            $count = 0;
            $created = 0;
            $updated = 0;

            // Pastikan model Country dimuat untuk performa query cepat
            $countriesCollection = Country::all();

            // Simpan/update curated ports terlebih dahulu
            foreach ($curatedPorts as $data) {
                $country = $countriesCollection->where('code', $data['country_code'])->first();
                if (!$country) continue;

                $portCode = $data['port_code'];
                $curatedCodes[] = $portCode;
                $validPortCodes[] = $portCode;

                $existingPort = Port::where('port_code', $portCode)->first();
                $status = $existingPort ? $existingPort->status : 'Open';

                $port = Port::updateOrCreate(
                    ['port_code' => $portCode],
                    [
                        'country_id' => $country->id,
                        'port_name' => $data['port_name'],
                        'city' => $data['city'],
                        'type' => $data['type'],
                        'capacity' => $data['capacity'],
                        'status' => $status,
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                    ]
                );

                if ($port->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
                $count++;
            }

            // Whitelist of uncoded port codes in the dataset that are verified sea ports for coastal countries
            $seaportWhitelist = [
                'KNBAS', // Basseterre (Saint Kitts and Nevis)
                'MCMON', // Monaco (Monaco)
                'MMRGN', // Yangon (Myanmar)
                'REPDG', // Pointe des Galets (Reunion)
                'SBHIR', // Honiara (Solomon Islands)
                'SBNOR', // Noro (Solomon Islands)
                'STTMS', // Sao Tome (Sao Tome and Principe)
                'TOTBU', // Nuku'alofa (Tonga)
                'VCKST', // Kingstown (Saint Vincent and the Grenadines)
                'VGRT',  // Road Town (British Virgin Islands)
                'YTDZA'  // Dzaoudzi (Mayotte)
            ];

            // 2. Unduh dataset lengkap dari Github
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(2)->get('https://raw.githubusercontent.com/marchah/sea-ports/master/lib/ports.json');
                if ($response->successful()) {
                    $rawPorts = $response->json();
                    if (is_array($rawPorts)) {
                        foreach ($rawPorts as $portCode => $data) {
                            if (strlen($portCode) < 5) continue;

                            // Jika bukan seaport berdasarkan kepemilikan WPI code atau whitelist, lewati!
                            if (!isset($data['code']) && !in_array($portCode, $seaportWhitelist)) {
                                continue;
                            }

                            // Jika sudah di-seed lewat curated list, jangan ditimpa
                            if (in_array($portCode, $curatedCodes)) {
                                $validPortCodes[] = $portCode;
                                continue;
                            }

                            $countryCode = strtoupper(substr($portCode, 0, 2));
                            $country = $countriesCollection->where('code', $countryCode)->first();
                            if (!$country) {
                                $countryName = $data['country'] ?? null;
                                if ($countryName) {
                                    $country = $countriesCollection->where('name', $countryName)->first();
                                }
                            }

                            // Lewati jika negara tidak terdaftar di sistem
                            if (!$country) continue;

                            $rawName = $data['name'] ?? 'Unnamed Port';
                            
                            // Format nama pelabuhan resmi agar tidak hanya nama kota
                            $name = $rawName;
                            if (stripos($name, 'port') === false && stripos($name, 'terminal') === false && stripos($name, 'harbor') === false && stripos($name, 'harbour') === false) {
                                if (in_array($countryCode, ['ID', 'MY', 'SG'])) {
                                    $name = $name . ' Port';
                                } else {
                                    $name = 'Port of ' . $name;
                                }
                            }

                            $city = $data['city'] ?? $country->capital ?? '-';
                            $lng = isset($data['coordinates'][0]) ? (float)$data['coordinates'][0] : null;
                            $lat = isset($data['coordinates'][1]) ? (float)$data['coordinates'][1] : null;

                            // Hitung kapasitas secara dinamis dan realistis berdasarkan volume perdagangan ekspor-impor negara
                            $countryEconomy = \App\Models\Economy::where('country_id', $country->id)->orderBy('year', 'desc')->first();
                            $tradeVolume = $countryEconomy ? ($countryEconomy->exports + $countryEconomy->imports) : 20; // in billions
                            if ($tradeVolume <= 0) $tradeVolume = 20;

                            // Estimasi kapasitas berbasis total volume trade negara
                            $countryTotalCapacity = $tradeVolume * 2000000;
                            // Estimasi kasar pembagi berdasarkan total pelabuhan negara
                            $countryPortsCount = 5; 
                            $baseShare = $countryTotalCapacity / $countryPortsCount;
                            // Berikan variasi deterministik berbasis hash port_code (+/- 30%)
                            $varianceFactor = 0.7 + (abs(crc32($portCode)) % 61) / 100.0;
                            $capacity = (int) ($baseShare * $varianceFactor);

                            // Batasan realistis minimum dan maksimum
                            if ($capacity < 100000) {
                                $capacity = 500000 + (abs(crc32($portCode)) % 450000);
                            }

                            $existingPort = Port::where('port_code', $portCode)->first();
                            $status = $existingPort ? $existingPort->status : 'Open';

                            $port = Port::updateOrCreate(
                                ['port_code' => $portCode],
                                [
                                    'country_id' => $country->id,
                                    'port_name' => $name,
                                    'city' => $city,
                                    'type' => 'Sea Port',
                                    'capacity' => $capacity,
                                    'status' => $status,
                                    'latitude' => $lat,
                                    'longitude' => $lng,
                                ]
                            );

                            if ($port->wasRecentlyCreated) {
                                $created++;
                            } else {
                                $updated++;
                            }
                            $validPortCodes[] = $portCode;
                            $count++;
                        }
                    }
                }
            } catch (\Exception $apiEx) {
                Log::warning('Gagal mengunduh dataset lengkap dari Github: ' . $apiEx->getMessage());
            }

            // 3. Bersihkan pelabuhan yang tidak valid (inland cities, dry ports, dll.) dari database
            if (!empty($validPortCodes)) {
                Port::whereNotIn('port_code', $validPortCodes)->delete();
            }

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$count} pelabuhan laut utama. ({$created} baru, {$updated} diperbarui)",
                'count' => $count
            ];

        } catch (\Exception $e) {
            Log::error('PortService Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi pelabuhan: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Curated list of real major sea ports with actual/official names, locations, and capacities.
     */
    private function getCuratedPorts(): array
    {
        return [
            [
                'port_code' => 'IDTPP',
                'port_name' => 'Tanjung Priok Port',
                'city' => 'Jakarta',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 80000000,
                'latitude' => -6.1033,
                'longitude' => 106.8789
            ],
            [
                'port_code' => 'IDJKT',
                'port_name' => 'Tanjung Priok Port',
                'city' => 'Jakarta',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 80000000,
                'latitude' => -6.1033,
                'longitude' => 106.8789
            ],
            [
                'port_code' => 'IDTPE',
                'port_name' => 'Tanjung Perak Port',
                'city' => 'Surabaya',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 35000000,
                'latitude' => -7.2025,
                'longitude' => 112.7277
            ],
            [
                'port_code' => 'IDSUB',
                'port_name' => 'Tanjung Perak Port',
                'city' => 'Surabaya',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 35000000,
                'latitude' => -7.2025,
                'longitude' => 112.7277
            ],
            [
                'port_code' => 'IDBLW',
                'port_name' => 'Port of Belawan',
                'city' => 'Medan',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 15000000,
                'latitude' => 3.7843,
                'longitude' => 98.6942
            ],
            [
                'port_code' => 'IDBPN',
                'port_name' => 'Semayang Port',
                'city' => 'Balikpapan',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 12000000,
                'latitude' => -1.25,
                'longitude' => 116.82
            ],
            [
                'port_code' => 'IDUPG',
                'port_name' => 'Soekarno-Hatta Port',
                'city' => 'Makassar',
                'country_code' => 'ID',
                'type' => 'Sea Port',
                'capacity' => 10000000,
                'latitude' => -5.1333,
                'longitude' => 119.4167
            ],
            [
                'port_code' => 'MYPKG',
                'port_name' => 'Port Klang',
                'city' => 'Klang',
                'country_code' => 'MY',
                'type' => 'Sea Port',
                'capacity' => 220000000,
                'latitude' => 2.9994,
                'longitude' => 101.3922
            ],
            [
                'port_code' => 'MYKUL',
                'port_name' => 'Port Klang',
                'city' => 'Klang',
                'country_code' => 'MY',
                'type' => 'Sea Port',
                'capacity' => 220000000,
                'latitude' => 2.9994,
                'longitude' => 101.3922
            ],
            [
                'port_code' => 'MYPEN',
                'port_name' => 'Penang Port',
                'city' => 'Penang',
                'country_code' => 'MY',
                'type' => 'Sea Port',
                'capacity' => 30000000,
                'latitude' => 5.4105,
                'longitude' => 100.3708
            ],
            [
                'port_code' => 'MYTPP',
                'port_name' => 'Port of Tanjung Pelepas',
                'city' => 'Johor',
                'country_code' => 'MY',
                'type' => 'Sea Port',
                'capacity' => 150000000,
                'latitude' => 1.37,
                'longitude' => 103.55
            ],
            [
                'port_code' => 'SGSIN',
                'port_name' => 'Port of Singapore',
                'city' => 'Singapore',
                'country_code' => 'SG',
                'type' => 'Sea Port',
                'capacity' => 622670000,
                'latitude' => 1.264,
                'longitude' => 103.84
            ],
            [
                'port_code' => 'SGJUR',
                'port_name' => 'Jurong Port',
                'city' => 'Singapore',
                'country_code' => 'SG',
                'type' => 'Sea Port',
                'capacity' => 40000000,
                'latitude' => 1.33,
                'longitude' => 103.7
            ],
            [
                'port_code' => 'JPTYO',
                'port_name' => 'Port of Tokyo',
                'city' => 'Tokyo',
                'country_code' => 'JP',
                'type' => 'Sea Port',
                'capacity' => 85000000,
                'latitude' => 35.6264,
                'longitude' => 139.7894
            ],
            [
                'port_code' => 'JPYOK',
                'port_name' => 'Port of Yokohama',
                'city' => 'Yokohama',
                'country_code' => 'JP',
                'type' => 'Sea Port',
                'capacity' => 110000000,
                'latitude' => 35.45,
                'longitude' => 139.667
            ],
            [
                'port_code' => 'JPKOB',
                'port_name' => 'Port of Kobe',
                'city' => 'Kobe',
                'country_code' => 'JP',
                'type' => 'Sea Port',
                'capacity' => 80000000,
                'latitude' => 34.65,
                'longitude' => 135.18
            ],
            [
                'port_code' => 'JPOSA',
                'port_name' => 'Port of Osaka',
                'city' => 'Osaka',
                'country_code' => 'JP',
                'type' => 'Sea Port',
                'capacity' => 75000000,
                'latitude' => 34.67,
                'longitude' => 135.43
            ],
            [
                'port_code' => 'JPNGO',
                'port_name' => 'Port of Nagoya',
                'city' => 'Nagoya',
                'country_code' => 'JP',
                'type' => 'Sea Port',
                'capacity' => 185000000,
                'latitude' => 35.05,
                'longitude' => 136.87
            ],
            [
                'port_code' => 'CNSHA',
                'port_name' => 'Port of Shanghai',
                'city' => 'Shanghai',
                'country_code' => 'CN',
                'type' => 'Sea Port',
                'capacity' => 573000000,
                'latitude' => 31.2222,
                'longitude' => 121.5397
            ],
            [
                'port_code' => 'CNSZX',
                'port_name' => 'Port of Shenzhen',
                'city' => 'Shenzhen',
                'country_code' => 'CN',
                'type' => 'Sea Port',
                'capacity' => 290000000,
                'latitude' => 22.508,
                'longitude' => 113.883
            ],
            [
                'port_code' => 'CNNGB',
                'port_name' => 'Port of Ningbo-Zhoushan',
                'city' => 'Ningbo',
                'country_code' => 'CN',
                'type' => 'Sea Port',
                'capacity' => 1250000000,
                'latitude' => 29.86,
                'longitude' => 121.56
            ],
            [
                'port_code' => 'CNCAN',
                'port_name' => 'Port of Guangzhou',
                'city' => 'Guangzhou',
                'country_code' => 'CN',
                'type' => 'Sea Port',
                'capacity' => 600000000,
                'latitude' => 23.129,
                'longitude' => 113.264
            ],
            [
                'port_code' => 'CNTAO',
                'port_name' => 'Port of Qingdao',
                'city' => 'Qingdao',
                'country_code' => 'CN',
                'type' => 'Sea Port',
                'capacity' => 630000000,
                'latitude' => 36.067,
                'longitude' => 120.383
            ],
            [
                'port_code' => 'CNTSN',
                'port_name' => 'Port of Tianjin',
                'city' => 'Tianjin',
                'country_code' => 'CN',
                'type' => 'Sea Port',
                'capacity' => 550000000,
                'latitude' => 38.967,
                'longitude' => 117.783
            ],
            [
                'port_code' => 'USLAX',
                'port_name' => 'Port of Los Angeles',
                'city' => 'Los Angeles',
                'country_code' => 'US',
                'type' => 'Sea Port',
                'capacity' => 170000000,
                'latitude' => 33.7287,
                'longitude' => -118.262
            ],
            [
                'port_code' => 'USLGB',
                'port_name' => 'Port of Long Beach',
                'city' => 'Long Beach',
                'country_code' => 'US',
                'type' => 'Sea Port',
                'capacity' => 80000000,
                'latitude' => 33.754,
                'longitude' => -118.215
            ],
            [
                'port_code' => 'USNYC',
                'port_name' => 'Port of New York and New Jersey',
                'city' => 'New York',
                'country_code' => 'US',
                'type' => 'Sea Port',
                'capacity' => 90000000,
                'latitude' => 40.667,
                'longitude' => -74.167
            ],
            [
                'port_code' => 'USHOU',
                'port_name' => 'Port of Houston',
                'city' => 'Houston',
                'country_code' => 'US',
                'type' => 'Sea Port',
                'capacity' => 280000000,
                'latitude' => 29.75,
                'longitude' => -95.35
            ],
            [
                'port_code' => 'USSEA',
                'port_name' => 'Port of Seattle',
                'city' => 'Seattle',
                'country_code' => 'US',
                'type' => 'Sea Port',
                'capacity' => 25000000,
                'latitude' => 47.6,
                'longitude' => -122.33
            ],
            [
                'port_code' => 'NLRTM',
                'port_name' => 'Port of Rotterdam',
                'city' => 'Rotterdam',
                'country_code' => 'NL',
                'type' => 'Sea Port',
                'capacity' => 440000000,
                'latitude' => 51.9167,
                'longitude' => 4.5
            ],
            [
                'port_code' => 'DEHAM',
                'port_name' => 'Port of Hamburg',
                'city' => 'Hamburg',
                'country_code' => 'DE',
                'type' => 'Sea Port',
                'capacity' => 120000000,
                'latitude' => 53.55,
                'longitude' => 9.98
            ],
            [
                'port_code' => 'BEANR',
                'port_name' => 'Port of Antwerp',
                'city' => 'Antwerp',
                'country_code' => 'BE',
                'type' => 'Sea Port',
                'capacity' => 240000000,
                'latitude' => 51.24,
                'longitude' => 4.41
            ],
            [
                'port_code' => 'KRPUS',
                'port_name' => 'Port of Busan',
                'city' => 'Busan',
                'country_code' => 'KR',
                'type' => 'Sea Port',
                'capacity' => 400000000,
                'latitude' => 35.104,
                'longitude' => 129.043
            ],
            [
                'port_code' => 'THLCH',
                'port_name' => 'Laem Chabang Port',
                'city' => 'Chonburi',
                'country_code' => 'TH',
                'type' => 'Sea Port',
                'capacity' => 90000000,
                'latitude' => 13.09,
                'longitude' => 100.89
            ],
            [
                'port_code' => 'VNSGN',
                'port_name' => 'Saigon Port',
                'city' => 'Ho Chi Minh City',
                'country_code' => 'VN',
                'type' => 'Sea Port',
                'capacity' => 100000000,
                'latitude' => 10.77,
                'longitude' => 106.7
            ],
            [
                'port_code' => 'SAJED',
                'port_name' => 'Jeddah Islamic Port',
                'city' => 'Jeddah',
                'country_code' => 'SA',
                'type' => 'Sea Port',
                'capacity' => 60000000,
                'latitude' => 21.48,
                'longitude' => 39.18
            ],
            [
                'port_code' => 'AEJEA',
                'port_name' => 'Port of Jebel Ali',
                'city' => 'Dubai',
                'country_code' => 'AE',
                'type' => 'Sea Port',
                'capacity' => 140000000,
                'latitude' => 24.9857,
                'longitude' => 55.0273
            ],
            [
                'port_code' => 'ZADUR',
                'port_name' => 'Port of Durban',
                'city' => 'Durban',
                'country_code' => 'ZA',
                'type' => 'Sea Port',
                'capacity' => 80000000,
                'latitude' => -29.8587,
                'longitude' => 31.0218
            ],
            [
                'port_code' => 'AUMEL',
                'port_name' => 'Port of Melbourne',
                'city' => 'Melbourne',
                'country_code' => 'AU',
                'type' => 'Sea Port',
                'capacity' => 40000000,
                'latitude' => -37.85,
                'longitude' => 144.9
            ],
            [
                'port_code' => 'BRSSZ',
                'port_name' => 'Port of Santos',
                'city' => 'Santos',
                'country_code' => 'BR',
                'type' => 'Sea Port',
                'capacity' => 160000000,
                'latitude' => -23.95,
                'longitude' => -46.3
            ],
            [
                'port_code' => 'GBFXT',
                'port_name' => 'Port of Felixstowe',
                'city' => 'Felixstowe',
                'country_code' => 'GB',
                'type' => 'Sea Port',
                'capacity' => 35000000,
                'latitude' => 51.96,
                'longitude' => 1.31
            ]
        ];
    }
}
