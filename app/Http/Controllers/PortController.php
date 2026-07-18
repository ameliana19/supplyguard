<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Country;
use App\Services\PortService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PortController extends Controller
{
    protected $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $countryFilter = $request->country_id;

        // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
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
            ->get();

        $countries = Country::orderBy('name')->get();

        return view('ports.index', compact('ports', 'countries'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('ports.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'port_name'  => 'required|string|max:100',
            'port_code'  => 'required|string|max:20|unique:ports,port_code',
            'city'       => 'required|string|max:100',
            'type'       => 'required|string|max:50',
            'capacity'   => 'required|numeric|min:0',
            'status'     => 'required|in:Open,Busy,Maintenance,Closed',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ]);

        Port::create($validated);

        return redirect()->route('ports.index')
            ->with('success', 'Data port berhasil ditambahkan.');
    }

    public function edit(Port $port)
    {
        $countries = Country::orderBy('name')->get();
        return view('ports.edit', compact('port', 'countries'));
    }

    public function update(Request $request, Port $port)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'port_name'  => 'required|string|max:100',
            'port_code'  => 'required|string|max:20|unique:ports,port_code,' . $port->id,
            'city'       => 'required|string|max:100',
            'type'       => 'required|string|max:50',
            'capacity'   => 'required|numeric|min:0',
            'status'     => 'required|in:Open,Busy,Maintenance,Closed',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ]);

        $port->update($validated);

        return redirect()->route('ports.index')
            ->with('success', 'Data port berhasil diperbarui.');
    }

    public function destroy(Port $port)
    {
        $port->delete();
        return redirect()->route('ports.index')
            ->with('success', 'Data port berhasil dihapus.');
    }

    // ============================================
    // SYNC DATA PELABUHAN DUNIA (FIXED)
    // ============================================
    public function sync()
    {
        Log::info('Sync pelabuhan via Web Controller dijalankan');

        $result = $this->portService->syncPorts();

        if ($result['success']) {
            return redirect()->route('ports.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('ports.index')
            ->with('error', $result['message']);
    }
}