<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $search = $request->search;

        // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
        $currencies = Currency::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        })
        ->orderBy('rate', 'desc')
        ->get();

        $totalCurrency = $currencies->count();
        $baseCurrency = Currency::where('code', 'USD')->first();
        $highestRate = Currency::max('rate');
        $lowestRate = Currency::min('rate');
        $lastUpdate = Currency::latest()->first();

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

    public function create()
    {
        return view('currency.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|max:100',
            'code'   => 'required|max:10|unique:currency_data,code',
            'symbol' => 'required|max:10',
            'rate'   => 'required|numeric|min:0'
        ]);

        Currency::create([
            'name'   => $request->name,
            'code'   => strtoupper($request->code),
            'symbol' => $request->symbol,
            'rate'   => $request->rate,
        ]);

        return redirect()
            ->route('currency.index')
            ->with('success', 'Currency berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('currency.edit', compact('currency'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|max:100',
            'code'   => 'required|max:10|unique:currency_data,code,' . $id,
            'symbol' => 'required|max:10',
            'rate'   => 'required|numeric|min:0'
        ]);

        $currency = Currency::findOrFail($id);

        $currency->update([
            'name'   => $request->name,
            'code'   => strtoupper($request->code),
            'symbol' => $request->symbol,
            'rate'   => $request->rate,
        ]);

        return redirect()
            ->route('currency.index')
            ->with('success', 'Currency berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();

        return redirect()
            ->route('currency.index')
            ->with('success', 'Currency berhasil dihapus.');
    }

    // ===============================================
    // SYNC KURS MATA UANG DARI EXCHANGERATE (FIXED)
    // ===============================================
    public function sync()
    {
        Log::info('Sync mata uang via Web Controller dijalankan');

        $result = $this->currencyService->syncRates();

        if ($result['success']) {
            return redirect()->route('currency.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('currency.index')
            ->with('error', $result['message']);
    }
}