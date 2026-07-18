<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'code',
        'flag',
        'capital',
        'region',
        'currency',
        'population',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'population' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // 🌦 WEATHER RELATION
    public function weather()
    {
        return $this->hasMany(WeatherData::class, 'country_id');
    }

    // ⭐ WATCHLIST RELATION
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class, 'country_id');
    }

    // ⚠️ RISK SCORES (semua history)
    public function riskScores()
    {
        return $this->hasMany(RiskScore::class, 'country_id');
    }

    // 🔥 RISK TERBARU (FIXED & CLEAN)
    public function latestRiskScore()
    {
        return $this->hasOne(RiskScore::class, 'country_id')
            ->latestOfMany('created_at');
    }

    // 📈 ECONOMY RELATION
    public function economies()
    {
        return $this->hasMany(Economy::class, 'country_id');
    }

    // 🚢 PORTS RELATION
    public function ports()
    {
        return $this->hasMany(Port::class, 'country_id');
    }

    // 📦 SHIPMENTS RELATION (Origin)
    public function originShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_country_id');
    }

    // 📦 SHIPMENTS RELATION (Destination)
    public function destinationShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_country_id');
    }
}