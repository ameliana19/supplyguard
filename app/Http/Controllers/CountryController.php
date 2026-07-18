<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    /**
     * Service to handle REST Countries API operations.
     */
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display a listing of countries with search and pagination.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        
        // Mengambil data negara dari database dengan fitur pencarian (search)
        // Data diurutkan berdasarkan nama secara alfabetis agar mudah dicari
        $countries = Country::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('capital', 'like', "%{$search}%")
                      ->orWhere('region', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->withQueryString(); // Mempertahankan query string saat pagination

        return view('countries.index', compact('countries', 'search'));
    }

    /**
     * Show the form for creating a new country.
     */
    public function create()
    {
        return view('countries.create');
    }

    /**
     * Store a newly created country in storage.
     */
    public function store(Request $request)
    {
        // Validasi input untuk form create
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:countries,name',
            'capital'    => 'required|string|max:255',
            'region'     => 'required|string|max:255',
            'currency'   => 'required|string|max:100',
            'population' => 'required|integer|min:0',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
        ]);

        try {
            // Menyimpan data negara ke database
            Country::create($validated);
            
            return redirect()->route('countries.index')
                ->with('success', 'Data negara berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan negara baru: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan negara. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified country and its latest news.
     */
    public function show(Country $country)
    {
        // Mengambil 5 berita terbaru yang berkaitan dengan negara ini untuk ditampilkan di halaman detail
        $latestNews = Article::where('country_id', $country->id)->latest()->take(5)->get();
        return view('countries.show', compact('country', 'latestNews'));
    }

    /**
     * Show the form for editing the specified country.
     */
    public function edit(Country $country)
    {
        return view('countries.edit', compact('country'));
    }

    /**
     * Update the specified country in storage.
     */
    public function update(Request $request, Country $country)
    {
        // Validasi input untuk form edit (pengecualian unique rule untuk ID saat ini)
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:countries,name,' . $country->id,
            'capital'    => 'required|string|max:255',
            'region'     => 'required|string|max:255',
            'currency'   => 'required|string|max:100',
            'population' => 'required|integer|min:0',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
        ]);

        try {
            // Memperbarui data negara di database
            $country->update($validated);
            
            return redirect()->route('countries.index')
                ->with('success', 'Data negara berhasil diubah.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui data negara: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mengubah negara. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified country from storage.
     */
    public function destroy(Country $country)
    {
        try {
            // Menghapus data negara dari database
            $country->delete();
            
            return redirect()->route('countries.index')
                ->with('success', 'Data negara berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus data negara: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus negara. Terdapat data terkait.');
        }
    }

    // =====================================
    // IMPORT API DARI REST COUNTRIES (FIXED)
    // =====================================
    
    /**
     * Import country data from REST Countries API using CountryService.
     */
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

    /**
     * Import country data from a CSV file.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // Melewati baris header
            fgetcsv($handle, 1000, ',');
            $count = 0;

            // Membaca isi CSV baris demi baris dan menyimpannya ke database
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 4) continue;
                Country::firstOrCreate(
                    ['name' => $row[0]],
                    [
                        'capital'    => $row[1] ?? '-',
                        'region'     => $row[2] ?? '-',
                        'currency'   => $row[3] ?? '-',
                        'population' => isset($row[4]) ? (int)$row[4] : 0,
                        'latitude'   => isset($row[5]) ? (float)$row[5] : null,
                        'longitude'  => isset($row[6]) ? (float)$row[6] : null,
                    ]
                );
                $count++;
            }
            fclose($handle);

            return redirect()->route('countries.index')
                ->with('success', "Import CSV berhasil! {$count} data diproses.");
        } catch (\Exception $e) {
            Log::error('Error saat import CSV: ' . $e->getMessage());
            return redirect()->route('countries.index')
                ->with('error', 'Terjadi kesalahan saat mengimpor CSV.');
        }
    }

    /**
     * Export all country data to a CSV file.
     */
    public function exportExcel()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=countries_export_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Mengambil semua data untuk di-export (tanpa pagination)
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

    /**
     * Export country data to a PDF view.
     */
    public function exportPdf()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('countries.pdf', compact('countries'));
    }
}