<?php

namespace App\Http\Controllers;

use App\Models\WeatherData;
use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    /**
     * Service to handle OpenWeather API operations.
     */
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Display a listing of weather data.
     */
    public function index()
    {
        // Optimasi Query: Menggunakan paginate alih-alih get() untuk mencegah load memory berlebihan
        // meskipun view utamanya me-load data melalui AJAX API, variabel ini tetap tersedia jika diperlukan.
        $weather = WeatherData::with('country')
            ->latest('recorded_at')
            ->paginate(20);
            
        return view('weather.index', compact('weather'));
    }

    /**
     * Show the form for creating a new weather record.
     */
    public function create()
    {
        // Mengambil daftar negara untuk dropdown
        $countries = Country::orderBy('name', 'asc')->get();
        return view('weather.create', compact('countries'));
    }

    /**
     * Store a newly created weather record in storage.
     */
    public function store(Request $request)
    {
        // Validasi input untuk memastikan data cuaca yang dimasukkan valid
        $validated = $request->validate([
            'country_id'        => 'required|exists:countries,id',
            'city'              => 'required|string|max:255',
            'temperature'       => 'required|numeric',
            'humidity'          => 'required|integer|min:0|max:100',
            'wind_speed'        => 'required|numeric|min:0',
            'weather_condition' => 'required|string|max:255',
            'pressure'          => 'required|integer|min:0',
        ]);

        try {
            // Merapikan logika penyimpanan data
            $validated['weather_icon'] = '';
            $validated['recorded_at'] = now();

            WeatherData::create($validated);

            return redirect()->route('weather.index')
                ->with('success', 'Data cuaca berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan data cuaca: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan data cuaca.');
        }
    }

    /**
     * Show the form for editing the specified weather record.
     */
    public function edit(WeatherData $weather)
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('weather.edit', compact('weather', 'countries'));
    }

    /**
     * Update the specified weather record in storage.
     */
    public function update(Request $request, WeatherData $weather)
    {
        // Validasi request saat proses update
        $validated = $request->validate([
            'country_id'        => 'required|exists:countries,id',
            'city'              => 'required|string|max:255',
            'temperature'       => 'required|numeric',
            'humidity'          => 'required|integer|min:0|max:100',
            'wind_speed'        => 'required|numeric|min:0',
            'weather_condition' => 'required|string|max:255',
            'pressure'          => 'required|integer|min:0',
        ]);

        try {
            // Proses update data
            $weather->update($validated);

            return redirect()->route('weather.index')
                ->with('success', 'Data cuaca berhasil diubah');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui data cuaca: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mengubah data cuaca.');
        }
    }

    /**
     * Remove the specified weather record from storage.
     */
    public function destroy(WeatherData $weather)
    {
        try {
            $weather->delete();

            return redirect()->route('weather.index')
                ->with('success', 'Data cuaca berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus data cuaca: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data cuaca.');
        }
    }

    // ============================================
    // UPDATE DATA CUACA DARI OPENWEATHER (FIXED)
    // ============================================
    
    /**
     * Sinkronisasi data cuaca dari OpenWeather API.
     */
    public function updateWeather()
    {
        Log::info('Update cuaca via Web Controller dijalankan');

        try {
            // Pengecekan apakah data negara tersedia sebelum sync
            $countryCount = Country::count();
            if ($countryCount === 0) {
                return redirect()->route('weather.index')
                    ->with('warning', 'Data negara belum tersedia. Silakan tambahkan negara terlebih dahulu sebelum sinkronisasi cuaca.');
            }

            // Memperbaiki proses sinkronisasi dengan service
            $result = $this->weatherService->syncAll();

            if (isset($result['success']) && $result['success']) {
                return redirect()->route('weather.index')
                    ->with('success', $result['message'] ?? 'Cuaca berhasil disinkronkan.');
            }

            // Menangani error ketika API mengembalikan status gagal
            return redirect()->route('weather.index')
                ->with('error', $result['message'] ?? 'Gagal menyinkronkan data cuaca.');
                
        } catch (\Exception $e) {
            // Menangani error fatal saat API gagal diakses
            Log::error('Exception saat sinkronisasi cuaca: ' . $e->getMessage());
            return redirect()->route('weather.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghubungi OpenWeather API.');
        }
    }
}