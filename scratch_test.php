<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shipment;
use App\Models\Article;
use App\Models\Country;

echo "=== Total countries count ===\n";
echo Country::count() . "\n\n";

echo "=== List of countries ===\n";
foreach (Country::orderBy('id')->get() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | Code: {$c->code}\n";
}
echo "\n";

$mismatchedCount = 0;
$shipments = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort'])
    ->take(5000)
    ->get();

foreach ($shipments as $s) {
    $originCountryId = $s->origin_country_id;
    $destCountryId = $s->destination_country_id;
    
    $portOriginCountryId = $s->originPort ? $s->originPort->country_id : null;
    $portDestCountryId = $s->destinationPort ? $s->destinationPort->country_id : null;
    
    if ($originCountryId !== $portOriginCountryId || $destCountryId !== $portDestCountryId) {
        $mismatchedCount++;
    }
}

echo "Total Mismatched Shipments: {$mismatchedCount}\n";
echo "Total Articles: " . Article::count() . "\n";
if (Article::count() > 0) {
    $art = Article::with('country')->first();
    echo "Article Title: {$art->title}\n";
    echo "Article Summary: {$art->summary}\n";
    echo "Article Image: {$art->image}\n";
    echo "Article Country: " . ($art->country ? $art->country->name : 'None') . "\n";
}
