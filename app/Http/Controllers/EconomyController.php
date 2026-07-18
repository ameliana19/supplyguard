<?php

namespace App\Http\Controllers;

use App\Models\Economy;
use App\Models\Country;
use App\Services\EconomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EconomyController extends Controller
{
    protected $economyService;

    public function __construct(EconomyService $economyService)
    {
        $this->economyService = $economyService;
    }

    public function index()
    {
        return view('economy.index');
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('economy.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id'   => 'required|exists:countries,id',
            'gdp'          => 'required|numeric',
            'inflation'    => 'required|numeric',
            'unemployment' => 'required|numeric',
            'exports'      => 'required|numeric',
            'imports'      => 'required|numeric',
            'year'         => 'required|integer',
        ]);

        Economy::create($validated);

        return redirect()->route('economy.index')
            ->with('success', 'Data ekonomi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $economy = Economy::findOrFail($id);
        $countries = Country::orderBy('name')->get();
        return view('economy.edit', compact('economy', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'country_id'   => 'required|exists:countries,id',
            'gdp'          => 'required|numeric',
            'inflation'    => 'required|numeric',
            'unemployment' => 'required|numeric',
            'exports'      => 'required|numeric',
            'imports'      => 'required|numeric',
            'year'         => 'required|integer',
        ]);

        $economy = Economy::findOrFail($id);
        $economy->update($validated);

        return redirect()->route('economy.index')
            ->with('success', 'Data ekonomi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Economy::findOrFail($id)->delete();
        return redirect()->route('economy.index')
            ->with('success', 'Data ekonomi berhasil dihapus.');
    }

    // ============================================
    // SYNC DATA EKONOMI DARI WORLD BANK (FIXED)
    // ============================================
    public function sync()
    {
        Log::info('Sync ekonomi via Web Controller dijalankan');

        $result = $this->economyService->syncAll();

        if ($result['success']) {
            return redirect()->route('economy.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('economy.index')
            ->with('error', $result['message']);
    }
}