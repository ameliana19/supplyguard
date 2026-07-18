<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\RiskScore;
use App\Models\Country;

class RiskScoreController extends Controller
{
    public function index()
    {
        // Auto-calculate risk scores for all countries if any are missing
        if (RiskScore::count() < Country::count()) {
            $countries = Country::all();
            foreach ($countries as $country) {
                RiskScore::calculateAndSave($country->id);
            }
        }

        $riskScores = RiskScore::with('country')->latest()->paginate(15);
        return view('risk-score.index', compact('riskScores'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('risk-score.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'weather_score' => 'required|numeric|min:0|max:100',
            'currency_score' => 'required|numeric|min:0|max:100',
            'economy_score' => 'required|numeric|min:0|max:100',
            'port_score' => 'required|numeric|min:0|max:100',
        ]);

        // ✅ RATA-RATA (biar 0–100)
        $total = (
            $data['weather_score'] +
            $data['currency_score'] +
            $data['economy_score'] +
            $data['port_score']
        ) / 4;

        // REDESIGN THRESHOLDS
        if ($total >= 42.00) {
            $risk = "High";
        } elseif ($total >= 30.00) {
            $risk = "Medium";
        } else {
            $risk = "Low";
        }

        RiskScore::updateOrCreate(
            ['country_id' => $data['country_id']],
            [
                'weather_score' => $data['weather_score'],
                'currency_score' => $data['currency_score'],
                'economy_score' => $data['economy_score'],
                'port_score' => $data['port_score'],
                'total_score' => $total,
                'risk_level' => $risk,
            ]
        );

        return redirect()->route('risk-score.index')
            ->with('success', 'Data Risk Score berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $riskScore = RiskScore::findOrFail($id);
        $countries = Country::all();

        return view('risk-score.edit', compact('riskScore', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'weather_score' => 'required|numeric|min:0|max:100',
            'currency_score' => 'required|numeric|min:0|max:100',
            'economy_score' => 'required|numeric|min:0|max:100',
            'port_score' => 'required|numeric|min:0|max:100',
        ]);

        $riskScore = RiskScore::findOrFail($id);

        // ✅ RATA-RATA
        $total = (
            $data['weather_score'] +
            $data['currency_score'] +
            $data['economy_score'] +
            $data['port_score']
        ) / 4;

        // REDESIGN THRESHOLDS
        if ($total >= 42.00) {
            $risk = "High";
        } elseif ($total >= 30.00) {
            $risk = "Medium";
        } else {
            $risk = "Low";
        }

        $riskScore->update([
            'country_id' => $data['country_id'],
            'weather_score' => $data['weather_score'],
            'currency_score' => $data['currency_score'],
            'economy_score' => $data['economy_score'],
            'port_score' => $data['port_score'],
            'total_score' => $total,
            'risk_level' => $risk,
        ]);

        return redirect()->route('risk-score.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        RiskScore::destroy($id);

        return redirect()->route('risk-score.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    // =========================================
    // HITUNG RISIKO OTOMATIS BERDASARKAN DATA
    // =========================================
    public function calculate()
    {
        Log::info('Hitung risiko otomatis dijalankan');

        try {
            $countries = Country::all();
            $count = 0;
            $updated = 0;
            $created = 0;
            $failed = 0;

            foreach ($countries as $country) {
                try {
                    // Gunakan method calculateAndSave dari model
                    $riskScore = RiskScore::calculateAndSave($country->id);

                    if ($riskScore->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    $count++;

                } catch (\Exception $e) {
                    Log::warning("Gagal menghitung risiko untuk {$country->name}: " . $e->getMessage());
                    $failed++;
                }
            }

            Log::info("Hitung risiko selesai: {$count} diproses, {$created} baru, {$updated} diupdate, {$failed} gagal");

            return redirect()->route('risk-score.index')
                ->with('success', "Berhasil menghitung risiko untuk {$count} negara. ({$created} baru, {$updated} diupdate, {$failed} gagal)");

        } catch (\Exception $e) {
            Log::error('Exception saat hitung risiko: ' . $e->getMessage());
            return redirect()->route('risk-score.index')
                ->with('error', 'Gagal menghitung risiko. Error: ' . $e->getMessage());
        }
    }
}