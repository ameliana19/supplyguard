<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;
use App\Models\Country;

class WatchlistController extends Controller
{
    public function index()
    {
        $userId = auth()->id() ?? 1;

        // Ambil semua country untuk dropdown
        $countries = Country::orderBy('name', 'asc')->get();

        // Ambil watchlist + relasi country + riskScores
        $watchlists = Watchlist::with(['country.riskScores'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        foreach ($watchlists as $item) {

            $country = $item->country;

            // default value
            $item->latest_risk = null;
            $item->risk_level  = 'No Data';

            if ($country) {

                // ambil risk terbaru (paling aman)
                $latestRisk = $country->riskScores()
                    ->orderByDesc('created_at')
                    ->first();

                if ($latestRisk) {
                    $item->latest_risk = (float) $latestRisk->total_score;
                    $item->risk_level  = $latestRisk->risk_level;
                }
            }
        }

        return view('watchlist.index', compact('watchlists', 'countries'));
    }

    public function create()
    {
        $countries = Country::orderBy('name', 'asc')->get();

        return view('watchlist.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $userId = auth()->id() ?? 1;

        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'note'       => 'nullable|string|max:255',
        ]);

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
    }

    public function show($id)
    {
        $watchlist = Watchlist::with('country.riskScores')->findOrFail($id);

        return view('watchlist.show', compact('watchlist'));
    }

    public function edit($id)
    {
        $watchlist = Watchlist::findOrFail($id);
        $countries = Country::orderBy('name', 'asc')->get();

        return view('watchlist.edit', compact('watchlist', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'note'       => 'nullable|string|max:255',
        ]);

        $watchlist = Watchlist::findOrFail($id);

        $watchlist->update([
            'country_id' => $request->country_id,
            'note'       => $request->note,
        ]);

        return redirect()->route('watchlist.index')
            ->with('success', 'Watchlist berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Watchlist::findOrFail($id)->delete();

        return redirect()->route('watchlist.index')
            ->with('success', 'Watchlist berhasil dihapus.');
    }
}