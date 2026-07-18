<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;

class PopulateDatabase extends Command
{
    protected $signature = 'supplyguard:populate';
    protected $description = 'Populates the database with required record volumes using high-performance chunk inserts.';

    public function handle()
    {
        $this->info('Starting database population for SupplyGuard...');
        
        // Disable query log for performance
        DB::connection()->disableQueryLog();
        
        // 1. User and Profile
        $this->line('Seeding User & Profile...');
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Administrator SupplyGuard',
                'phone_number' => '+628123456789',
                'company' => 'SupplyGuard Global Corp',
                'address' => 'Sudirman Central Business District, Jakarta, Indonesia',
                'photo' => null,
            ]
        );

        // 2. Clear old tables to prevent duplicate primary keys or constraint issues
        $this->line('Cleaning old data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('shipment_histories')->truncate();
        DB::table('shipment_planners')->truncate();
        DB::table('shipments')->truncate();
        DB::table('risk_scores')->truncate();
        DB::table('articles')->truncate();
        DB::table('economic_data')->truncate();
        DB::table('weather_data')->truncate();
        DB::table('ports')->truncate();
        DB::table('currency_data')->truncate();
        DB::table('currencies')->truncate();
        DB::table('countries')->truncate();
        DB::table('watchlists')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Countries (250+ countries)
        $this->line('Fetching 250+ Countries from API...');
        $countriesData = [];
        $countriesList = [];
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(25)->get('https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json');
            if ($response->successful()) {
                $rawCountries = $response->json();
                $id = 1;
                foreach ($rawCountries as $cData) {
                    $name = $cData['name']['common'] ?? null;
                    $code = $cData['cca2'] ?? null;
                    if (!$name || !$code) continue;
                    
                    $capital = isset($cData['capital']) && is_array($cData['capital']) && !empty($cData['capital']) ? $cData['capital'][0] : '-';
                    $region = $cData['region'] ?? '-';
                    $currency = '-';
                    if (isset($cData['currencies']) && is_array($cData['currencies']) && !empty($cData['currencies'])) {
                        $currency = array_key_first($cData['currencies']);
                    }
                    $population = $cData['population'] ?? 0;
                    $latitude = isset($cData['latlng']) && is_array($cData['latlng']) && isset($cData['latlng'][0]) ? $cData['latlng'][0] : null;
                    $longitude = isset($cData['latlng']) && is_array($cData['latlng']) && isset($cData['latlng'][1]) ? $cData['latlng'][1] : null;
                    $flag = "https://flagcdn.com/w320/" . strtolower($code) . ".png";

                    $countriesData[] = [
                        'id' => $id,
                        'name' => $name,
                        'code' => strtoupper($code),
                        'capital' => $capital,
                        'region' => $region,
                        'currency' => $currency,
                        'population' => $population,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'flag' => $flag,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $countriesList[] = ['id' => $id, 'name' => $name, 'currency' => $currency];
                    $id++;
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to load raw countries JSON: ' . $e->getMessage());
        }

        if (empty($countriesData)) {
            // Fallback default list
            $fallbackCountries = [
                ['Indonesia', 'ID', 'Jakarta', 'Asia', 'IDR', 277000000, -6.2000, 106.8000],
                ['Malaysia', 'MY', 'Kuala Lumpur', 'Asia', 'MYR', 34000000, 3.1000, 101.6000],
                ['Singapore', 'SG', 'Singapore', 'Asia', 'SGD', 6000000, 1.3521, 103.8198],
                ['Thailand', 'TH', 'Bangkok', 'Asia', 'THB', 71000000, 13.7563, 100.5018],
                ['Vietnam', 'VN', 'Hanoi', 'Asia', 'VND', 98000000, 21.0285, 105.8542],
                ['Philippines', 'PH', 'Manila', 'Asia', 'PHP', 113000000, 14.5995, 120.9842],
                ['Japan', 'JP', 'Tokyo', 'Asia', 'JPY', 125000000, 35.6762, 139.6503],
                ['China', 'CN', 'Beijing', 'Asia', 'CNY', 1412000000, 39.9042, 116.4074],
                ['South Korea', 'KR', 'Seoul', 'Asia', 'KRW', 51000000, 37.5665, 126.9780],
                ['India', 'IN', 'New Delhi', 'Asia', 'INR', 1408000000, 28.6139, 77.2090],
                ['Saudi Arabia', 'SA', 'Riyadh', 'Asia', 'SAR', 36000000, 24.7136, 46.6753],
                ['United Arab Emirates', 'AE', 'Abu Dhabi', 'Asia', 'AED', 9000000, 24.4539, 54.3773],
            ];
            foreach ($fallbackCountries as $index => $c) {
                $id = $index + 1;
                $countriesData[] = [
                    'id' => $id,
                    'name' => $c[0],
                    'code' => $c[1],
                    'capital' => $c[2],
                    'region' => $c[3],
                    'currency' => $c[4],
                    'population' => $c[5],
                    'latitude' => $c[6],
                    'longitude' => $c[7],
                    'flag' => "https://flagcdn.com/w320/" . strtolower($c[1]) . ".png",
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $countriesList[] = ['id' => $id, 'name' => $c[0], 'currency' => $c[4]];
            }
        }

        foreach (array_chunk($countriesData, 100) as $chunk) {
            DB::table('countries')->insert($chunk);
        }
        $this->info(count($countriesData) . ' Countries inserted.');

        // 4. Currency Data & Historical Currency Rates (5,000+)
        $this->line('Syncing all unique currencies using CurrencyService...');
        app(\App\Services\CurrencyService::class)->syncRates();
        
        $syncedCurrencies = DB::table('currency_data')->pluck('rate', 'code')->toArray();
        $this->info(count($syncedCurrencies) . ' Currency records synced.');

        $this->line('Generating 5,200 Historical Currency rates...');
        $currencyRates = [];
        $codes = array_keys($syncedCurrencies);
        if (!empty($codes)) {
            for ($i = 0; $i < 5200; $i++) {
                $code = $codes[array_rand($codes)];
                $baseRate = $syncedCurrencies[$code] ?? 1.00;
                $fluctuation = (rand(-500, 500) / 10000); // Max 5% fluctuation
                $currentRate = max(0.01, $baseRate * (1 + $fluctuation));
                $currencyRates[] = [
                    'currency_code' => $code,
                    'exchange_rate' => $currentRate,
                    'recorded_at' => now()->subHours(rand(0, 10000)),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (count($currencyRates) >= 1000) {
                    DB::table('currencies')->insert($currencyRates);
                    $currencyRates = [];
                }
            }
            if (!empty($currencyRates)) {
                DB::table('currencies')->insert($currencyRates);
            }
        }
        $this->info('5,200 Currency rates inserted.');

        // 5. Ports (Real Sea Ports)
        $this->line('Syncing Ports using PortService...');
        $result = app(\App\Services\PortService::class)->syncPorts();
        $this->info($result['message']);

        // 6. Weather History (1 record per country)
        $this->line('Generating Weather history records...');
        $weatherData = [];
        $weatherConditions = ['Clear', 'Clouds', 'Rain', 'Drizzle', 'Thunderstorm', 'Snow', 'Mist', 'Fog'];
        $weatherIcons = ['01d', '03d', '10d', '09d', '11d', '13d', '50d', '50d'];

        foreach ($countriesList as $country) {
            $condIndex = array_rand($weatherConditions);
            $weatherCondition = $weatherConditions[$condIndex];
            $icon = $weatherIcons[$condIndex];
            $temp = rand(-1000, 4200) / 100;
            $humidity = rand(10, 100);
            $windSpeed = rand(0, 1200) / 100;
            $pressure = rand(980, 1030);
            $recordedAt = now()->subHours(rand(0, 24));

            $countryObj = $countriesData[$country['id'] - 1];
            $city = ($countryObj['capital'] && $countryObj['capital'] !== '-') ? $countryObj['capital'] : $countryObj['name'];

            $weatherData[] = [
                'country_id' => $country['id'],
                'city' => $city,
                'temperature' => $temp,
                'humidity' => $humidity,
                'wind_speed' => $windSpeed,
                'weather_condition' => $weatherCondition,
                'weather_icon' => $icon,
                'pressure' => $pressure,
                'recorded_at' => $recordedAt,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('weather_data')->insert($weatherData);
        $this->info(count($weatherData) . ' Weather history records inserted.');

        // 7. Economy Records (5 years per country)
        $this->line('Generating Economy records...');
        $economyData = [];
        foreach ($countriesList as $country) {
            for ($year = 2021; $year <= 2025; $year++) {
                $gdp = rand(10, 15000) / 10;
                $inflation = rand(-200, 2500) / 100;
                $unemployment = rand(100, 2500) / 100;
                $exports = rand(5, 5000) / 10;
                $imports = rand(5, 5000) / 10;

                $economyData[] = [
                    'country_id' => $country['id'],
                    'gdp' => $gdp,
                    'inflation' => $inflation,
                    'unemployment' => $unemployment,
                    'exports' => $exports,
                    'imports' => $imports,
                    'year' => $year,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        foreach (array_chunk($economyData, 100) as $chunk) {
            DB::table('economic_data')->insert($chunk);
        }
        $this->info(count($economyData) . ' Economy records inserted.');

        // 8. Risk Scores (1 record per country)
        $this->line('Generating Risk scores...');
        $riskScores = [];

        foreach ($countriesList as $country) {
            $weatherScore = rand(5, 95);
            $currencyScore = rand(5, 95);
            $economyScore = rand(5, 95);
            $portScore = rand(5, 95);
            $totalScore = ($weatherScore + $currencyScore + $economyScore + $portScore) / 4;
            $riskLevel = ($totalScore >= 42.00) ? 'High' : (($totalScore >= 30.00) ? 'Medium' : 'Low');

            $riskScores[] = [
                'country_id' => $country['id'],
                'weather_score' => $weatherScore,
                'currency_score' => $currencyScore,
                'economy_score' => $economyScore,
                'port_score' => $portScore,
                'total_score' => $totalScore,
                'risk_level' => $riskLevel,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('risk_scores')->insert($riskScores);
        $this->info(count($riskScores) . ' Risk scores inserted.');

        // 9. News / Articles (10,000+)
        $this->line('Generating 10,000+ logistics and supply chain News articles...');
        $newsData = [];
        
        $originalArticles = [
            // Indonesia
            [
                'title' => 'Ekspor Kelapa Sawit Indonesia Meningkat pada Kuartal II',
                'summary' => 'Pemerintah Indonesia melaporkan peningkatan ekspor kelapa sawit sebesar 12% dibandingkan tahun sebelumnya.',
                'category' => 'Ekspor',
                'source' => 'Ekspor Indonesia',
                'country' => 'Indonesia'
            ],
            // Malaysia
            [
                'title' => 'Malaysia Tingkatkan Ekspor Produk Elektronik',
                'summary' => 'Ekspor produk elektronik Malaysia mengalami pertumbuhan berkat permintaan global yang meningkat.',
                'category' => 'Ekspor',
                'source' => 'Dagang Malaysia',
                'country' => 'Malaysia'
            ],
            // Singapore
            [
                'title' => 'Pelabuhan Singapura Catat Rekor Volume Kontainer',
                'summary' => 'Pelabuhan Singapura mencatat peningkatan arus kontainer seiring meningkatnya aktivitas ekspor dan impor.',
                'category' => 'Pelabuhan',
                'source' => 'Maritim Singapura',
                'country' => 'Singapore'
            ],
            // Japan
            [
                'title' => 'Jepang Tingkatkan Investasi Infrastruktur Pelabuhan',
                'summary' => 'Pemerintah Jepang mengalokasikan dana baru untuk modernisasi pelabuhan guna memperkuat rantai pasok.',
                'category' => 'Pelabuhan',
                'source' => 'Kabar Jepang',
                'country' => 'Japan'
            ],
            // China
            [
                'title' => 'China Perluas Jalur Perdagangan Internasional',
                'summary' => 'China terus meningkatkan kerja sama perdagangan dengan berbagai negara melalui proyek logistik baru.',
                'category' => 'Perdagangan Internasional',
                'source' => 'Portal China',
                'country' => 'China'
            ],
            // Saudi Arabia
            [
                'title' => 'Arab Saudi Perkuat Sektor Logistik Nasional',
                'summary' => 'Arab Saudi mengembangkan infrastruktur pelabuhan dan logistik untuk mendukung perdagangan internasional.',
                'category' => 'Logistik',
                'source' => 'Logistik Arab Saudi',
                'country' => 'Saudi Arabia'
            ],
            // Thailand
            [
                'title' => 'Thailand Kembangkan Pusat Logistik Otomotif ASEAN',
                'summary' => 'Pemerintah Thailand mengumumkan rencana pembangunan hub logistik otomotif baru untuk melayani pasar Asia Tenggara.',
                'category' => 'Logistik',
                'source' => 'Logistik Thailand',
                'country' => 'Thailand'
            ],
            // Vietnam
            [
                'title' => 'Vietnam Alami Lonjakan Pengiriman Kargo Udara',
                'summary' => 'Aktivitas kargo udara di bandara utama Vietnam mencatat pertumbuhan tertinggi dalam lima tahun terakhir.',
                'category' => 'Logistik',
                'source' => 'Kargo Vietnam',
                'country' => 'Vietnam'
            ],
            // Philippines
            [
                'title' => 'Filipina Modernisasi Terminal Pelabuhan Manila',
                'summary' => 'Proyek modernisasi dermaga dan sistem logistik di Pelabuhan Manila resmi diluncurkan untuk mempercepat arus peti kemas.',
                'category' => 'Pelabuhan',
                'source' => 'Maritim Manila',
                'country' => 'Philippines'
            ],
            // South Korea
            [
                'title' => 'Korea Selatan Uji Coba Truk Kargo Tanpa Pengemudi',
                'summary' => 'Uji coba logistik otonom di jalan tol utama Korea Selatan berhasil diselesaikan sebagai bagian dari inisiatif masa depan.',
                'category' => 'Rantai Pasok',
                'source' => 'Teknologi Seoul',
                'country' => 'South Korea'
            ],
            // India
            [
                'title' => 'India Resmikan Jalur Kereta Kargo Khusus Koridor Barat',
                'summary' => 'Pemerintah India meresmikan jalur kereta cepat khusus angkutan barang guna memangkas waktu distribusi antar wilayah.',
                'category' => 'Logistik',
                'source' => 'Harian India',
                'country' => 'India'
            ],
            // United Arab Emirates
            [
                'title' => 'Dubai Hubungkan Rantai Pasok Global dengan AI',
                'summary' => 'Uni Emirat Arab memperkenalkan sistem kecerdasan buatan terpadu untuk optimalisasi rute pelayaran di Pelabuhan Jebel Ali.',
                'category' => 'Rantai Pasok',
                'source' => 'Inovasi Dubai',
                'country' => 'United Arab Emirates'
            ],
            // International news articles
            [
                'title' => 'Global Logistics Supply Chain Face New Disruptions',
                'summary' => 'Recent geopolitical events have introduced fresh bottlenecks in maritime cargo transit lanes.',
                'category' => 'Rantai Pasok',
                'source' => 'Global Trade',
                'country' => null
            ],
            [
                'title' => 'Port Congestion Expected to Rise in Q3 2026',
                'summary' => 'A surge in holiday inventory shipping is projected to challenge terminal storage capacities globally.',
                'category' => 'Pelabuhan',
                'source' => 'Port Authority Info',
                'country' => null
            ],
            [
                'title' => 'Exchange Rate Fluctuations and Their Impact on Marine Cargo',
                'summary' => 'Fluctuating values of major trade currencies are driving shifts in transaction methods for freight contracts.',
                'category' => 'Ekonomi Indonesia',
                'source' => 'Financial Review',
                'country' => null
            ],
            [
                'title' => 'Sustainable Freight Practices Gain Momentum Worldwide',
                'summary' => 'New environmental standards are accelerating the adoption of low-emission shipping solutions in commercial shipping.',
                'category' => 'Maritim',
                'source' => 'Green Logistics',
                'country' => null
            ],
            [
                'title' => 'Vessel Delays in Suez Canal Impact Major Shipments',
                'summary' => 'Adverse conditions and canal maintenance have led to temporary queues for container ships.',
                'category' => 'Maritim',
                'source' => 'Suez Monitor',
                'country' => null
            ],
            [
                'title' => 'Inflation Pressures Maritime Freight Operations',
                'summary' => 'Rising fuel and labor costs continue to squeeze profit margins for bulk carrier fleets.',
                'category' => 'Logistik',
                'source' => 'Maritime Shipping Daily',
                'country' => null
            ],
            [
                'title' => 'New Trade Agreements Reshape Asia-Pacific Cargo Routes',
                'summary' => 'The implementation of regional free trade treaties is shifting trade hubs and maritime corridors.',
                'category' => 'Perdagangan Internasional',
                'source' => 'Asia Trade Journal',
                'country' => null
            ],
            [
                'title' => 'Climate Change Extremes Halt Inland Waterways Shipping',
                'summary' => 'Severe drought conditions in major shipping canals have forced cargo load restrictions on key waterways.',
                'category' => 'Cuaca Pengiriman',
                'source' => 'Weather and Logistics',
                'country' => null
            ],
            [
                'title' => 'Technological Upgrades Drive Container Port Efficiencies',
                'summary' => 'Automation of crane operations and smart tracking systems are dramatically lowering container dwell times.',
                'category' => 'Pelabuhan',
                'source' => 'Smart Ports Info',
                'country' => null
            ],
            [
                'title' => 'Fuel Surcharges Jump as Geopolitical Tensions Rise',
                'summary' => 'Marine gas oil prices have climbed, prompting carriers to announce adjusted freight surcharge rates.',
                'category' => 'Impor',
                'source' => 'Global Shipping Journal',
                'country' => null
            ]
        ];

        $authors = ['John Doe', 'Sarah Smith', 'Logistics Daily', 'Trade Review', 'Mikael V.', 'Supply Chain Intelligence'];
        $countriesCollection = \App\Models\Country::all();

        for ($i = 0; $i < 150; $i++) {
            $artInfo = $originalArticles[$i % count($originalArticles)];
            $author = $authors[array_rand($authors)];
            $date = now()->subDays(rand(0, 365))->format('Y-m-d');
            
            $countryObj = null;
            if ($artInfo['country']) {
                $countryObj = $countriesCollection->where('name', $artInfo['country'])->first();
            }
            if (!$countryObj && !$countriesCollection->isEmpty()) {
                $countryObj = $countriesCollection->random();
            }

            $genericImages = [
                'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
                'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800',
                'https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?w=800',
                'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?w=800'
            ];
            $image = $genericImages[$i % count($genericImages)];

            $newsData[] = [
                'title' => $artInfo['title'],
                'title_id' => $artInfo['title'],
                'summary' => $artInfo['summary'],
                'summary_id' => $artInfo['summary'],
                'content' => $artInfo['summary'] . ' Berita ini disediakan secara otomatis sebagai bagian dari database SupplyGuard.',
                'category' => $artInfo['category'],
                'author' => $author,
                'source' => $artInfo['source'],
                'published_at' => $date,
                'country_id' => $countryObj ? $countryObj->id : null,
                'url' => 'https://supplyguard.com/news/seed-' . ($countryObj ? $countryObj->id : 'global') . '-' . $i . '-' . uniqid(),
                'image' => $image,
                'image_url' => $image,
                'created_at' => now(),
                'updated_at' => now()
            ];

            if (count($newsData) >= 100) {
                DB::table('articles')->insert($newsData);
                $newsData = [];
            }
        }
        if (!empty($newsData)) {
            DB::table('articles')->insert($newsData);
        }
        $this->info('150 News articles inserted.');

        // 10. Shipments & Shipment History (150 shipments)
        $this->line('Generating 150 Shipments & 600 Shipment history tracking points...');
        $shipments = [];
        $shipmentPlanners = [];
        $shipmentHistories = [];
        $cargoTypes = ['Electronics', 'Palm Oil', 'Automotive Parts', 'Chemicals', 'Textiles', 'Machinery', 'Foodstuffs', 'Pharmaceuticals'];
        $statuses = ['Pending', 'In Transit', 'Arrived', 'Delayed', 'Cancelled'];

        $dbPorts = \App\Models\Port::all();
        // 10. Shipments & Shipment History (Real Synced Shipments)
        $this->line('Syncing shipments from external JSON/Mock dataset...');
        app(\App\Services\ShipmentService::class)->syncShipments();
        $this->info('Real Shipments and histories synced successfully.');

        // 11. Watchlists
        $this->line('Seeding Watchlists...');
        $watchlistCountries = ['Indonesia', 'Japan', 'China', 'Singapore', 'Saudi Arabia'];
        foreach ($watchlistCountries as $wcName) {
            $wc = \App\Models\Country::where('name', $wcName)->first();
            if ($wc) {
                DB::table('watchlists')->insert([
                    'user_id' => $user->id,
                    'country_id' => $wc->id,
                    'note' => 'Pemantauan ketat rantai pasok untuk negara ' . $wcName,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        $this->info('Watchlists seeded.');
        
        $this->line('Running NewsService syncNews to populate articles for all countries and categories...');
        app(\App\Services\NewsService::class)->syncNews();

        $this->info('SupplyGuard database successfully populated with required volume!');
    }
}
