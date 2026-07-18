<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\RiskScore;

class CompareController extends Controller
{
    public function index()
    {
        $countries = Country::all();
        return view('compare.index', compact('countries'));
    }

    public function compare(Request $request)
    {
        $request->validate([
            'country1' => 'required|different:country2',
            'country2' => 'required',
        ]);

        $country1 = RiskScore::with('country')
            ->where('country_id', $request->country1)
            ->latest()
            ->first();

        if (!$country1) {
            $country1 = RiskScore::calculateAndSave($request->country1);
            $country1->load('country');
        }

        $country2 = RiskScore::with('country')
            ->where('country_id', $request->country2)
            ->latest()
            ->first();

        if (!$country2) {
            $country2 = RiskScore::calculateAndSave($request->country2);
            $country2->load('country');
        }

        return view('compare.result', compact('country1', 'country2'));
    }
}