<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Country;
use App\Services\PortService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PortController extends Controller
{
    /**
     * Service to handle Port data synchronization.
     */
    protected $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    /**
     * Display a listing of the ports with search and filter functionality.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $countryFilter = $request->country_id;

        // Mengambil data pelabuhan dengan fitur pencarian dan filter negara
        // Optimasi Query: Menggunakan paginate() untuk mencegah load seluruh data sekaligus ke memory
        $ports = Port::mainPorts()->with('country')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('port_name', 'like', "%{$search}%")
                      ->orWhere('port_code', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($countryFilter, function ($query) use ($countryFilter) {
                $query->where('country_id', $countryFilter);
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Optimasi Query: Hanya select id dan nama untuk dropdown filter
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();

        return view('ports.index', compact('ports', 'countries'));
    }

    /**
     * Show the form for creating a new port.
     */
    public function create()
    {
        // Optimasi Query: Hanya select id dan nama untuk dropdown form
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();
        return view('ports.create', compact('countries'));
    }

    /**
     * Store a newly created port in storage.
     */
    public function store(Request $request)
    {
        // Validasi request pembuatan pelabuhan baru
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'port_name'  => 'required|string|max:100',
            'port_code'  => 'required|string|max:20|unique:ports,port_code',
            'city'       => 'required|string|max:100',
            'type'       => 'required|string|max:50',
            'capacity'   => 'required|numeric|min:0',
            'status'     => 'required|in:Open,Busy,Maintenance,Closed',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
        ]);

        try {
            // Proses penyimpanan data ke database
            Port::create($validated);

            return redirect()->route('ports.index')
                ->with('success', 'Data pelabuhan berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan pelabuhan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan data pelabuhan.');
        }
    }

    /**
     * Show the form for editing the specified port.
     */
    public function edit(Port $port)
    {
        // Optimasi Query: Hanya select id dan nama untuk dropdown form
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();
        return view('ports.edit', compact('port', 'countries'));
    }

    /**
     * Update the specified port in storage.
     */
    public function update(Request $request, Port $port)
    {
        // Validasi request pembaruan pelabuhan (dengan pengecualian pengecekan kode unik)
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'port_name'  => 'required|string|max:100',
            'port_code'  => 'required|string|max:20|unique:ports,port_code,' . $port->id,
            'city'       => 'required|string|max:100',
            'type'       => 'required|string|max:50',
            'capacity'   => 'required|numeric|min:0',
            'status'     => 'required|in:Open,Busy,Maintenance,Closed',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
        ]);

        try {
            // Proses pembaruan data
            $port->update($validated);

            return redirect()->route('ports.index')
                ->with('success', 'Data pelabuhan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui pelabuhan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data pelabuhan.');
        }
    }

    /**
     * Remove the specified port from storage.
     */
    public function destroy(Port $port)
    {
        try {
            $port->delete();
            
            return redirect()->route('ports.index')
                ->with('success', 'Data pelabuhan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus pelabuhan: ' . $e->getMessage());
            return redirect()->route('ports.index')
                ->with('error', 'Gagal menghapus data pelabuhan.');
        }
    }

    // ============================================
    // SYNC DATA PELABUHAN DUNIA (FIXED)
    // ============================================
    
    /**
     * Synchronize port data from external sources.
     */
    public function sync()
    {
        Log::info('Sync pelabuhan via Web Controller dijalankan');

        try {
            // Memeriksa ketersediaan data negara sebelum sinkronisasi
            if (Country::count() === 0) {
                return redirect()->route('ports.index')
                    ->with('warning', 'Data negara belum tersedia. Tambahkan negara terlebih dahulu.');
            }

            $result = $this->portService->syncPorts();

            if (isset($result['success']) && $result['success']) {
                return redirect()->route('ports.index')
                    ->with('success', $result['message'] ?? 'Data pelabuhan berhasil disinkronkan.');
            }

            return redirect()->route('ports.index')
                ->with('error', $result['message'] ?? 'Gagal menyinkronkan data pelabuhan.');

        } catch (\Exception $e) {
            Log::error('Exception saat sinkronisasi pelabuhan: ' . $e->getMessage());
            return redirect()->route('ports.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghubungi sumber data.');
        }
    }
}