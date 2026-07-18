<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Country;
use App\Models\Article;

$countries = Country::all();
$categories = [
    'Logistik',
    'Rantai Pasok',
    'Pelabuhan',
    'Ekspor',
    'Impor',
    'Perdagangan Internasional',
    'Maritim',
    'Cuaca Pengiriman',
    'Ekonomi Indonesia'
];

echo "Total countries: " . $countries->count() . "\n";
echo "Total categories: " . count($categories) . "\n";
echo "Expected combinations: " . ($countries->count() * count($categories)) . "\n";

$start = microtime(true);
$articlesToInsert = [];
foreach ($countries as $country) {
    foreach ($categories as $category) {
        $articlesToInsert[] = [
            'url' => 'https://supplyguard.com/news/' . strtolower($country->code) . '-' . strtolower(str_replace(' ', '-', $category)) . '-' . uniqid(),
            'title' => "Berita {$category} untuk {$country->name}",
            'title_id' => "Berita {$category} untuk {$country->name}",
            'summary' => "Ringkasan berita {$category} di {$country->name}.",
            'summary_id' => "Ringkasan berita {$category} di {$country->name}.",
            'content' => "Isi lengkap berita {$category} di {$country->name}.",
            'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
            'image_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
            'source' => 'SupplyGuard Intelijen',
            'category' => $category,
            'author' => 'Tim Analis SupplyGuard',
            'published_at' => date('Y-m-d'),
            'country_id' => $country->id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}

// Bulk insert using chunks to avoid memory limit or statement size limit
$chunks = array_chunk($articlesToInsert, 500);
foreach ($chunks as $chunk) {
    Article::insert($chunk);
}

$elapsed = microtime(true) - $start;
echo "Inserted " . count($articlesToInsert) . " records in " . number_format($elapsed, 4) . " seconds.\n";
echo "Total articles in DB: " . Article::count() . "\n";
