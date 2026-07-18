<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\Port;
use App\Models\Watchlist;
use App\Models\RiskScore;
use App\Models\WeatherData;
use App\Models\Article;
use App\Models\Currency;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseApiController
{
    /**
     * Get summary metrics and latest data for dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id() ?? $request->query('user_id', 1);

            $totalCountries = Country::count();
            $totalPorts = Port::mainPorts()->count();
            $totalShipments = Shipment::count();

            // Safe column check for watchlists
            $totalWatchlist = 0;
            if (Schema::hasTable('watchlists')) {
                $totalWatchlist = Watchlist::where('user_id', $userId)->count();
            }
            
            // Risk levels count based on latest risk scores
            $highRisk = 0;
            $mediumRisk = 0;
            $lowRisk = 0;
            $avgRiskScore = 0;

            if (Schema::hasTable('risk_scores')) {
                $latestRiskScores = Country::with('latestRiskScore')->get()->pluck('latestRiskScore');
                $highRisk = $latestRiskScores->where('risk_level', 'High')->count();
                $mediumRisk = $latestRiskScores->where('risk_level', 'Medium')->count();
                $lowRisk = $latestRiskScores->where('risk_level', 'Low')->count();
                
                // Fallback check: if no latest risk scores, count from table directly
                if ($highRisk === 0 && $mediumRisk === 0 && $lowRisk === 0) {
                    $riskCounts = RiskScore::selectRaw('risk_level, count(*) as total')
                        ->groupBy('risk_level')
                        ->pluck('total', 'risk_level');
                    $highRisk = $riskCounts['High'] ?? 0;
                    $mediumRisk = $riskCounts['Medium'] ?? 0;
                    $lowRisk = $riskCounts['Low'] ?? 0;
                }

                $avgRiskScore = RiskScore::avg('total_score') ?? 0;
            }

            // Latest weather updates
            $latestWeather = collect();
            if (Schema::hasTable('weather_data')) {
                $latestWeather = WeatherData::with('country')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'country' => $item->country->name ?? null,
                            'city' => $item->city,
                            'temperature' => (float) $item->temperature,
                            'weather_condition' => $item->weather_condition,
                            'humidity' => $item->humidity,
                            'wind_speed' => $item->wind_speed,
                            'recorded_at' => $item->recorded_at,
                        ];
                    });
            }

            // Latest news articles
            $latestNews = collect();
            if (Schema::hasTable('articles')) {
                $latestNews = Article::latest()
                    ->take(5)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'category' => $item->category,
                            'author' => $item->author,
                            'date' => $item->published_at ? $item->published_at->format('Y-m-d') : null,
                        ];
                    });
            }

            // Currency metrics
            $usdCurrency = null;
            $highestRate = null;
            if (Schema::hasTable('currency_data')) {
                $usdCurrency = Currency::where('code', 'USD')->first();
                $highestRate = Currency::orderBy('rate', 'desc')->first();
            }

            // Economy trends (average GDP and Inflation by year)
            $economyTrends = collect();
            if (Schema::hasTable('economic_data')) {
                $economyTrends = DB::table('economic_data')
                    ->select('year', DB::raw('round(avg(gdp), 2) as avg_gdp'), DB::raw('round(avg(inflation), 2) as avg_inflation'))
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->limit(6)
                    ->get()
                    ->reverse()
                    ->values();
            }

            // Risk trends (average risk score by date)
            $riskTrends = collect();
            if (Schema::hasTable('risk_scores')) {
                $riskTrends = DB::table('risk_scores')
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'), DB::raw('round(avg(total_score), 2) as avg_score'))
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->limit(7)
                    ->get()
                    ->reverse()
                    ->values();
            }

            // Shipment trends (shipment volume by month)
            $shipmentTrends = collect();
            if (Schema::hasTable('shipments')) {
                $shipmentTrends = DB::table('shipments')
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as total'))
                    ->groupBy('month')
                    ->orderBy('month', 'desc')
                    ->limit(6)
                    ->get()
                    ->reverse()
                    ->values();
            }
            
            $summary = [
                'statistics' => [
                    'total_countries' => $totalCountries,
                    'total_ports' => $totalPorts,
                    'total_shipments' => $totalShipments,
                    'watchlist_count' => $totalWatchlist,
                    'average_risk_score' => round((float) $avgRiskScore, 2),
                    'high_risk' => $highRisk,
                    'medium_risk' => $mediumRisk,
                    'low_risk' => $lowRisk,
                ],
                'currency_overview' => [
                    'base_currency' => $usdCurrency ? [
                        'code' => $usdCurrency->code,
                        'name' => $usdCurrency->name,
                        'symbol' => $usdCurrency->symbol,
                    ] : null,
                    'highest_rate' => $highestRate ? [
                        'code' => $highestRate->code,
                        'rate' => (float) $highestRate->rate,
                        'name' => $highestRate->name,
                    ] : null,
                ],
                'latest_weather' => $latestWeather,
                'latest_news' => $latestNews,
                'trends' => [
                    'economy' => $economyTrends,
                    'risk' => $riskTrends,
                    'shipment' => $shipmentTrends
                ]
            ];

            return $this->sendResponse($summary, 'Data dashboard berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data dashboard.', [$e->getMessage()], 500);
        }
    }
}
