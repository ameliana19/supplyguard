<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Article;
use App\Models\Port;
use App\Models\Shipment;
use App\Models\Watchlist;
use App\Models\RiskScore;

class DashboardController extends Controller
{
    public function index()
    {
        // Real-time statistics
        $totalCountries = Country::count();
        $totalNews = Article::count();
        $totalPorts = Port::mainPorts()->count();
        $totalShipments = Shipment::count();
        $totalWatchlist = Watchlist::count();
        $totalRiskScores = RiskScore::count();

        // Latest data
        $latestCountries = Country::latest()->take(5)->get();
        $latestNews = Article::latest()->take(5)->get();
        $latestShipments = Shipment::with(['originCountry', 'destinationCountry'])->latest()->take(5)->get();

        // Risk statistics
        $highRiskCount = RiskScore::where('risk_level', 'High')->count();
        $mediumRiskCount = RiskScore::where('risk_level', 'Medium')->count();
        $lowRiskCount = RiskScore::where('risk_level', 'Low')->count();

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
    }
}