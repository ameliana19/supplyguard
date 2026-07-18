<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

class WatchlistController extends Controller
{
    /**
     * Display a listing of the user's watchlist.
     */
    public function index()
    {
        try {
            $userId = auth()->id() ?? 1;

            // Optimasi Query: Hanya ambil id dan nama untuk dropdown negara
            $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();

            // Optimasi Query: Menggunakan relasi latestRiskScore untuk menghindari load seluruh history skor risiko
            $watchlists = Watchlist::with(['country.latestRiskScore'])
                ->where('user_id', $userId)
                ->latest()
                ->paginate(15);

            foreach ($watchlists as $item) {
                $country = $item->country;

                // Default value
                $item->latest_risk = null;
                $item->risk_level  = 'No Data';

                if ($country && $country->latestRiskScore) {
                    $item->latest_risk = (float) $country->latestRiskScore->total_score;
                    $item->risk_level  = $country->latestRiskScore->risk_level;
                }
            }

            return view('watchlist.index', compact('watchlists', 'countries'));
        } catch (\Exception $e) {
            Log::error('Error memuat data watchlist: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data pantauan.');
        }
    }

    /**
     * Show the form for creating a new watchlist item.
     */
    public function create()
    {
        // Optimasi Query
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();

        return view('watchlist.create', compact('countries'));
    }

    /**
     * Store a newly created watchlist item in storage.
     */
    public function store(Request $request)
    {
        $userId = auth()->id() ?? 1;

        // Validasi input
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'note'       => 'nullable|string|max:255',
        ]);

        try {
            // Cek duplikasi
            $exists = Watchlist::where('user_id', $userId)
                ->where('country_id', $request->country_id)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Negara sudah ada di daftar pantauan.');
            }

            Watchlist::create([
                'user_id'    => $userId,
                'country_id' => $request->country_id,
                'note'       => $request->note,
            ]);

            return redirect()->route('watchlist.index')
                ->with('success', 'Negara berhasil ditambahkan ke daftar pantauan.');
        } catch (\Exception $e) {
            Log::error('Error menyimpan watchlist: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan negara ke daftar pantauan.');
        }
    }

    /**
     * Display the specified watchlist item.
     */
    public function show($id)
    {
        try {
            $watchlist = Watchlist::with('country.latestRiskScore')->findOrFail($id);

            return view('watchlist.show', compact('watchlist'));
        } catch (\Exception $e) {
            Log::error('Watchlist tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('watchlist.index')
                ->with('error', 'Data pantauan tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified watchlist item.
     */
    public function edit($id)
    {
        try {
            $watchlist = Watchlist::findOrFail($id);
            // Optimasi Query
            $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();

            return view('watchlist.edit', compact('watchlist', 'countries'));
        } catch (\Exception $e) {
            Log::error('Watchlist tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('watchlist.index')
                ->with('error', 'Data pantauan tidak ditemukan.');
        }
    }

    /**
     * Update the specified watchlist item in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'note'       => 'nullable|string|max:255',
        ]);

        try {
            $watchlist = Watchlist::findOrFail($id);

            $watchlist->update([
                'country_id' => $request->country_id,
                'note'       => $request->note,
            ]);

            return redirect()->route('watchlist.index')
                ->with('success', 'Watchlist berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error memperbarui watchlist: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui daftar pantauan.');
        }
    }

    /**
     * Remove the specified watchlist item from storage.
     */
    public function destroy($id)
    {
        try {
            $watchlist = Watchlist::findOrFail($id);
            $watchlist->delete();

            return redirect()->route('watchlist.index')
                ->with('success', 'Watchlist berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error menghapus watchlist: ' . $e->getMessage());
            return redirect()->route('watchlist.index')
                ->with('error', 'Gagal menghapus data pantauan.');
        }
    }
}