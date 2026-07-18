<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherData extends Model
{
    protected $table = 'weather_data';

    protected $fillable = [
        'country_id',
        'city',
        'temperature',
        'humidity',
        'wind_speed',
        'weather_condition',
        'weather_icon',
        'pressure',
        'recorded_at'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}