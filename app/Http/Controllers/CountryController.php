<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        
        // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
        $countries = Country::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('capital', 'like', "%{$search}%")
                      ->orWhere('region', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('countries.index', compact('countries', 'search'));
    }

    public function create()
    {
        return view('countries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'capital' => 'required',
            'region' => 'required',
            'currency' => 'required',
            'population' => 'required|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Country::create($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Data negara berhasil ditambahkan.');
    }

    public function show(Country $country)
    {
        $latestNews = Article::where('country_id', $country->id)->latest()->take(5)->get();
        return view('countries.show', compact('country', 'latestNews'));
    }

    public function edit(Country $country)
    {
        return view('countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => 'required',
            'capital' => 'required',
            'region' => 'required',
            'currency' => 'required',
            'population' => 'required|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $country->update($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Data negara berhasil diubah.');
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('countries.index')
            ->with('success', 'Data negara berhasil dihapus.');
    }

    // =====================================
    // IMPORT API DARI REST COUNTRIES (FIXED)
    // =====================================
    public function importFromApi()
    {
        Log::info('Import API dari Web Controller dijalankan');

        $result = $this->countryService->importFromAPI();

        if ($result['success']) {
            return redirect()->route('countries.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('countries.index')
            ->with('error', $result['message']);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        fgetcsv($handle, 1000, ',');
        $count = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($row) < 4) continue;
            Country::firstOrCreate(
                ['name' => $row[0]],
                [
                    'capital' => $row[1] ?? '-',
                    'region' => $row[2] ?? '-',
                    'currency' => $row[3] ?? '-',
                    'population' => isset($row[4]) ? (int)$row[4] : 0,
                    'latitude' => isset($row[5]) ? (float)$row[5] : null,
                    'longitude' => isset($row[6]) ? (float)$row[6] : null,
                ]
            );
            $count++;
        }
        fclose($handle);

        return redirect()->route('countries.index')
            ->with('success', "Import CSV berhasil! {$count} data diproses.");
    }

    public function exportExcel()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=countries_export_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $countries = Country::orderBy('name', 'asc')->get();
        $columns = ['Name', 'Capital', 'Region', 'Currency', 'Population', 'Latitude', 'Longitude'];

        $callback = function() use($countries, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($countries as $country) {
                fputcsv($file, [
                    $country->name,
                    $country->capital,
                    $country->region,
                    $country->currency,
                    $country->population,
                    $country->latitude,
                    $country->longitude,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('countries.pdf', compact('countries'));
    }
}