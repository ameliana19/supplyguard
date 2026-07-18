<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\RiskScore;
use App\Models\Country;

class RiskScoreController extends Controller
{
    /**
     * Display a listing of the risk scores.
     */
    public function index()
    {
        try {
            // Auto-calculate risk scores for all countries if any are missing
            if (RiskScore::count() < Country::count()) {
                $countries = Country::select('id')->get();
                foreach ($countries as $country) {
                    RiskScore::calculateAndSave($country->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error saat auto-calculate skor risiko: ' . $e->getMessage());
            // Lanjutkan eksekusi meskipun auto-calculate gagal
        }

        // Optimasi: Membatasi data per halaman menggunakan paginate
        $riskScores = RiskScore::with('country')->latest()->paginate(15);
        return view('risk-score.index', compact('riskScores'));
    }

    /**
     * Show the form for creating a new risk score.
     */
    public function create()
    {
        // Optimasi Query: Hanya mengambil id dan nama untuk keperluan form dropdown
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();
        return view('risk-score.create', compact('countries'));
    }

    /**
     * Store a newly created risk score in storage.
     */
    public function store(Request $request)
    {
        // Validasi input data risiko
        $data = $request->validate([
            'country_id'     => 'required|exists:countries,id',
            'weather_score'  => 'required|numeric|min:0|max:100',
            'currency_score' => 'required|numeric|min:0|max:100',
            'economy_score'  => 'required|numeric|min:0|max:100',
            'port_score'     => 'required|numeric|min:0|max:100',
        ]);

        try {
            $total = $this->calculateTotalScore($data);
            $risk = $this->determineRiskLevel($total);

            RiskScore::updateOrCreate(
                ['country_id' => $data['country_id']],
                [
                    'weather_score'  => $data['weather_score'],
                    'currency_score' => $data['currency_score'],
                    'economy_score'  => $data['economy_score'],
                    'port_score'     => $data['port_score'],
                    'total_score'    => $total,
                    'risk_level'     => $risk,
                ]
            );

            return redirect()->route('risk-score.index')
                ->with('success', 'Data tingkat risiko berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan tingkat risiko: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan tingkat risiko.');
        }
    }

    /**
     * Show the form for editing the specified risk score.
     */
    public function edit($id)
    {
        try {
            $riskScore = RiskScore::findOrFail($id);
            // Optimasi Query: Hanya mengambil id dan nama untuk dropdown
            $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();

            return view('risk-score.edit', compact('riskScore', 'countries'));
        } catch (\Exception $e) {
            Log::error('Data tingkat risiko tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('risk-score.index')
                ->with('error', 'Data tingkat risiko tidak ditemukan.');
        }
    }

    /**
     * Update the specified risk score in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi input pembaruan data risiko
        $data = $request->validate([
            'country_id'     => 'required|exists:countries,id',
            'weather_score'  => 'required|numeric|min:0|max:100',
            'currency_score' => 'required|numeric|min:0|max:100',
            'economy_score'  => 'required|numeric|min:0|max:100',
            'port_score'     => 'required|numeric|min:0|max:100',
        ]);

        try {
            $riskScore = RiskScore::findOrFail($id);

            $total = $this->calculateTotalScore($data);
            $risk = $this->determineRiskLevel($total);

            $riskScore->update([
                'country_id'     => $data['country_id'],
                'weather_score'  => $data['weather_score'],
                'currency_score' => $data['currency_score'],
                'economy_score'  => $data['economy_score'],
                'port_score'     => $data['port_score'],
                'total_score'    => $total,
                'risk_level'     => $risk,
            ]);

            return redirect()->route('risk-score.index')
                ->with('success', 'Data tingkat risiko berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui tingkat risiko: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data tingkat risiko.');
        }
    }

    /**
     * Remove the specified risk score from storage.
     */
    public function destroy($id)
    {
        try {
            $riskScore = RiskScore::findOrFail($id);
            $riskScore->delete();

            return redirect()->route('risk-score.index')
                ->with('success', 'Data tingkat risiko berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus tingkat risiko: ' . $e->getMessage());
            return redirect()->route('risk-score.index')
                ->with('error', 'Gagal menghapus tingkat risiko.');
        }
    }

    // =========================================
    // HITUNG RISIKO OTOMATIS BERDASARKAN DATA
    // =========================================
    
    /**
     * Calculate risk scores automatically for all countries based on raw data.
     */
    public function calculate()
    {
        Log::info('Hitung risiko otomatis dijalankan');

        try {
            // Memeriksa ketersediaan data negara sebelum kalkulasi
            if (Country::count() === 0) {
                return redirect()->route('risk-score.index')
                    ->with('warning', 'Data negara belum tersedia. Tambahkan negara terlebih dahulu.');
            }

            $countries = Country::select('id', 'name')->get();
            $count = 0;
            $updated = 0;
            $created = 0;
            $failed = 0;

            foreach ($countries as $country) {
                try {
                    // Gunakan method calculateAndSave dari model
                    $riskScore = RiskScore::calculateAndSave($country->id);

                    if ($riskScore && $riskScore->wasRecentlyCreated) {
                        $created++;
                    } else if ($riskScore) {
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
                ->with('success', "Berhasil menghitung risiko untuk {$count} negara. ({$created} baru, {$updated} diperbarui, {$failed} gagal)");

        } catch (\Exception $e) {
            Log::error('Exception saat hitung risiko: ' . $e->getMessage());
            return redirect()->route('risk-score.index')
                ->with('error', 'Gagal menghitung risiko sistem secara masal.');
        }
    }

    /**
     * Helper: Calculate the average of all individual risk scores.
     */
    private function calculateTotalScore(array $data)
    {
        return (
            $data['weather_score'] +
            $data['currency_score'] +
            $data['economy_score'] +
            $data['port_score']
        ) / 4;
    }

    /**
     * Helper: Determine the string category of risk based on total numerical score.
     */
    private function determineRiskLevel($totalScore)
    {
        if ($totalScore >= 42.00) {
            return "High";
        } elseif ($totalScore >= 30.00) {
            return "Medium";
        } else {
            return "Low";
        }
    }
}