<?php

namespace App\Http\Controllers;

use App\Models\WeatherData;
use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index()
    {
        // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
        $weather = WeatherData::with('country')->latest()->get();
        return view('weather.index', compact('weather'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('weather.create', compact('countries'));
    }

    public function store(Request $request)
    {
        WeatherData::create([
            'country_id' => $request->country_id,
            'city' => $request->city,
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'wind_speed' => $request->wind_speed,
            'weather_condition' => $request->weather_condition,
            'pressure' => $request->pressure,
            'weather_icon' => '',
            'recorded_at' => now(),
        ]);

        return redirect()->route('weather.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(WeatherData $weather)
    {
        $countries = Country::all();
        return view('weather.edit', compact('weather', 'countries'));
    }

    public function update(Request $request, WeatherData $weather)
    {
        $weather->update($request->all());

        return redirect()->route('weather.index')
            ->with('success', 'Data berhasil diubah');
    }

    public function destroy(WeatherData $weather)
    {
        $weather->delete();

        return redirect()->route('weather.index')
            ->with('success', 'Data berhasil dihapus');
    }

    // ============================================
    // UPDATE DATA CUACA DARI OPENWEATHER (FIXED)
    // ============================================
    public function updateWeather()
    {
        Log::info('Update cuaca via Web Controller dijalankan');

        $result = $this->weatherService->syncAll();

        if ($result['success']) {
            return redirect()->route('weather.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('weather.index')
            ->with('error', $result['message']);
    }
}