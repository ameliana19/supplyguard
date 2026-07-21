<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Article;
use App\Models\Port;
use App\Models\Shipment;
use App\Models\Watchlist;
use App\Models\RiskScore;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard with statistics and recent data.
     */
    public function index()
    {
        try {
            // Mengambil jumlah agregat secara efisien
            $totalCountries  = Country::count();
            $totalNews       = Article::count();
            $totalPorts      = Port::mainPorts()->count();
            $totalShipments  = Shipment::count();
            $totalWatchlist  = Watchlist::count();
            $totalRiskScores = RiskScore::count();

            // Memuat sebagian kecil data terbaru untuk UI tanpa me-load seluruh field
            $latestCountries = Country::select('id', 'name', 'code', 'region')->latest()->take(5)->get();
            $latestNews      = Article::select('id', 'title', 'published_at', 'url')->latest()->take(5)->get();
            $latestShipments = Shipment::with([
                'originCountry:id,name', 
                'destinationCountry:id,name'
            ])->select('id', 'tracking_number', 'status', 'origin_country_id', 'destination_country_id', 'estimated_departure', 'estimated_arrival')
              ->latest()
              ->take(5)
              ->get();

            // Mengelompokkan level risiko
            $highRiskCount   = RiskScore::where('risk_level', 'High')->count();
            $mediumRiskCount = RiskScore::where('risk_level', 'Medium')->count();
            $lowRiskCount    = RiskScore::where('risk_level', 'Low')->count();

            return view('dashboard', compact(
                'totalCountries',
                'totalNews',
                'totalPorts',
                'totalShipments',
                'totalWatchlist',
                'totalRiskScores',
                'latestCountries',
                'latestNews',
                'latestShipments',
                'highRiskCount',
                'mediumRiskCount',
                'lowRiskCount'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard Load Error: ' . $e->getMessage());
            // Tetap memuat tampilan dasar jika database bermasalah
            return view('dashboard', [
                'totalCountries' => 0,
                'totalNews' => 0,
                'totalPorts' => 0,
                'totalShipments' => 0,
                'totalWatchlist' => 0,
                'totalRiskScores' => 0,
                'latestCountries' => collect(),
                'latestNews' => collect(),
                'latestShipments' => collect(),
                'highRiskCount' => 0,
                'mediumRiskCount' => 0,
                'lowRiskCount' => 0,
            ])->with('error', 'Gagal memuat beberapa data dasbor.');
        }
    }
}