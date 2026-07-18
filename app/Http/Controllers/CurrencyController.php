<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    /**
     * Service to handle Exchange Rate API operations.
     */
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of currencies and statistics.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        // Mengambil data mata uang dengan fitur pencarian (search)
        $currencies = Currency::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        })
        ->orderBy('rate', 'desc')
        ->get();

        // Mengoptimalkan agregasi data
        $totalCurrency = $currencies->count();
        $baseCurrency = Currency::where('code', 'USD')->first();
        
        // Statistik global (tanpa terpengaruh filter)
        $highestRate = Currency::max('rate');
        $lowestRate = Currency::min('rate');
        $lastUpdate = Currency::latest('updated_at')->first();

        // Data untuk Chart.js di frontend
        $chartLabels = $currencies->pluck('code');
        $chartData = $currencies->pluck('rate');

        return view('currency.index', compact(
            'currencies',
            'totalCurrency',
            'baseCurrency',
            'highestRate',
            'lowestRate',
            'lastUpdate',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('currency.create');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(Request $request)
    {
        // Validasi input data mata uang
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'code'   => 'required|string|max:10|unique:currency_data,code',
            'symbol' => 'required|string|max:10',
            'rate'   => 'required|numeric|min:0'
        ]);

        try {
            // Memastikan kode mata uang diset ke huruf besar (Uppercase)
            $validated['code'] = strtoupper($validated['code']);

            Currency::create($validated);

            return redirect()
                ->route('currency.index')
                ->with('success', 'Mata uang berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan mata uang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan mata uang.');
        }
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('currency.edit', compact('currency'));
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi request dengan pengecualian unique untuk ID saat ini
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'code'   => 'required|string|max:10|unique:currency_data,code,' . $id,
            'symbol' => 'required|string|max:10',
            'rate'   => 'required|numeric|min:0'
        ]);

        try {
            $currency = Currency::findOrFail($id);
            $validated['code'] = strtoupper($validated['code']);

            $currency->update($validated);

            return redirect()
                ->route('currency.index')
                ->with('success', 'Mata uang berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui mata uang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data mata uang.');
        }
    }

    /**
     * Remove the specified currency from storage.
     */
    public function destroy($id)
    {
        try {
            $currency = Currency::findOrFail($id);
            $currency->delete();

            return redirect()
                ->route('currency.index')
                ->with('success', 'Mata uang berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus mata uang: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus mata uang.');
        }
    }

    // ===============================================
    // SYNC KURS MATA UANG DARI EXCHANGERATE (FIXED)
    // ===============================================
    
    /**
     * Synchronize currency rates from external Exchange Rate API.
     */
    public function sync()
    {
        Log::info('Sync mata uang via Web Controller dijalankan');

        try {
            // Memanggil layanan sinkronisasi API
            $result = $this->currencyService->syncRates();

            if (isset($result['success']) && $result['success']) {
                return redirect()->route('currency.index')
                    ->with('success', $result['message'] ?? 'Kurs mata uang berhasil disinkronkan.');
            }

            // Menangani respon API jika berstatus gagal
            return redirect()->route('currency.index')
                ->with('error', $result['message'] ?? 'Gagal menyinkronkan kurs mata uang.');

        } catch (\Exception $e) {
            // Menangani kegagalan fatal seperti server down atau timeout
            Log::error('Exception saat sinkronisasi mata uang: ' . $e->getMessage());
            return redirect()->route('currency.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghubungi Exchange Rate API.');
        }
    }
}