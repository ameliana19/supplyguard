<?php

namespace App\Http\Controllers;

use App\Models\Economy;
use App\Models\Country;
use App\Services\EconomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EconomyController extends Controller
{
    /**
     * Service to handle World Bank API operations.
     */
    protected $economyService;

    public function __construct(EconomyService $economyService)
    {
        $this->economyService = $economyService;
    }

    /**
     * Display a listing of the economy data.
     */
    public function index()
    {
        // View utama menampilkan UI, data aktual akan diambil secara asinkron via API/AJAX
        return view('economy.index');
    }

    /**
     * Show the form for creating a new economy record.
     */
    public function create()
    {
        // Optimasi Query: Hanya mengambil kolom yang dibutuhkan untuk dropdown
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();
        return view('economy.create', compact('countries'));
    }

    /**
     * Store a newly created economy record in storage.
     */
    public function store(Request $request)
    {
        // Validasi input data ekonomi dengan ketat
        $validated = $request->validate([
            'country_id'   => 'required|exists:countries,id',
            'gdp'          => 'required|numeric|min:0',
            'inflation'    => 'required|numeric',
            'unemployment' => 'required|numeric|min:0|max:100',
            'exports'      => 'required|numeric|min:0',
            'imports'      => 'required|numeric|min:0',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        try {
            Economy::create($validated);

            return redirect()->route('economy.index')
                ->with('success', 'Data ekonomi berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan data ekonomi: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan data ekonomi.');
        }
    }

    /**
     * Show the form for editing the specified economy record.
     */
    public function edit($id)
    {
        try {
            $economy = Economy::findOrFail($id);
            // Optimasi Query: Hanya mengambil kolom yang dibutuhkan
            $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();
            
            return view('economy.edit', compact('economy', 'countries'));
        } catch (\Exception $e) {
            Log::error('Data ekonomi tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('economy.index')
                ->with('error', 'Data ekonomi tidak ditemukan.');
        }
    }

    /**
     * Update the specified economy record in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi input pembaruan data
        $validated = $request->validate([
            'country_id'   => 'required|exists:countries,id',
            'gdp'          => 'required|numeric|min:0',
            'inflation'    => 'required|numeric',
            'unemployment' => 'required|numeric|min:0|max:100',
            'exports'      => 'required|numeric|min:0',
            'imports'      => 'required|numeric|min:0',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        try {
            $economy = Economy::findOrFail($id);
            $economy->update($validated);

            return redirect()->route('economy.index')
                ->with('success', 'Data ekonomi berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui data ekonomi: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data ekonomi.');
        }
    }

    /**
     * Remove the specified economy record from storage.
     */
    public function destroy($id)
    {
        try {
            $economy = Economy::findOrFail($id);
            $economy->delete();
            
            return redirect()->route('economy.index')
                ->with('success', 'Data ekonomi berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus data ekonomi: ' . $e->getMessage());
            return redirect()->route('economy.index')
                ->with('error', 'Gagal menghapus data ekonomi.');
        }
    }

    // ============================================
    // SYNC DATA EKONOMI DARI WORLD BANK (FIXED)
    // ============================================
    
    /**
     * Synchronize economy data from external API (World Bank).
     */
    public function sync()
    {
        Log::info('Sync ekonomi via Web Controller dijalankan');

        try {
            // Memeriksa ketersediaan data negara sebelum sinkronisasi
            if (Country::count() === 0) {
                return redirect()->route('economy.index')
                    ->with('warning', 'Data negara belum tersedia. Tambahkan negara terlebih dahulu.');
            }

            $result = $this->economyService->syncAll();

            if (isset($result['success']) && $result['success']) {
                return redirect()->route('economy.index')
                    ->with('success', $result['message'] ?? 'Data ekonomi berhasil disinkronkan.');
            }

            return redirect()->route('economy.index')
                ->with('error', $result['message'] ?? 'Gagal menyinkronkan data ekonomi.');
                
        } catch (\Exception $e) {
            Log::error('Exception saat sinkronisasi data ekonomi: ' . $e->getMessage());
            return redirect()->route('economy.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghubungi server API.');
        }
    }
}