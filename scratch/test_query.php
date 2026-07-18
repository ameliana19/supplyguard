<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Economy;
use Illuminate\Support\Facades\DB;

try {
    $subquery = DB::table('economic_data')
        ->select('country_id', DB::raw('MAX(year) as max_year'))
        ->groupBy('country_id');

    $query = Economy::joinSub($subquery, 'latest_economy', function ($join) {
        $join->on('economic_data.country_id', '=', 'latest_economy.country_id')
             ->on('economic_data.year', '=', 'latest_economy.max_year');
    })
    ->with('country')
    ->select('economic_data.*');

    $economies = $query->orderBy('gdp', 'desc')->paginate(15);
    echo "Count: " . count($economies->items()) . "\n";
    echo "Total: " . $economies->total() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
