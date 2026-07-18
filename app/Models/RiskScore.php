<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    use HasFactory;

    protected $table = 'risk_scores';

    protected $fillable = [
        'country_id',
        'weather_score',
        'currency_score',
        'economy_score',
        'port_score',
        'total_score',
        'risk_level',
    ];

    protected $casts = [
        'weather_score' => 'float',
        'currency_score' => 'float',
        'economy_score' => 'float',
        'port_score' => 'float',
        'total_score' => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public static function calculateAndSave($countryId)
    {
        // 1. Weather Score (25%)
        $weather = WeatherData::where('country_id', $countryId)->latest()->first();
        $weatherScore = 50.0;
        if ($weather) {
            $cond = strtolower($weather->weather_condition);
            if (str_contains($cond, 'thunder') || str_contains($cond, 'tornado') || str_contains($cond, 'storm')) {
                $weatherScore = 95.0;
            } elseif (str_contains($cond, 'rain') || str_contains($cond, 'drizzle') || str_contains($cond, 'snow')) {
                $weatherScore = 60.0;
            } elseif (str_contains($cond, 'cloud')) {
                $weatherScore = 30.0;
            } else {
                $weatherScore = 15.0;
            }
            if ($weather->wind_speed > 15) {
                $weatherScore = min(100.0, $weatherScore + 15);
            }
        }

        // 2. Economy Score (25%)
        $economy = Economy::where('country_id', $countryId)->latest()->first();
        $economyScore = 50.0;
        if ($economy) {
            $inflationRisk = min(100.0, abs($economy->inflation - 2.5) * 8 + 15);
            $unemploymentRisk = min(100.0, $economy->unemployment * 8 + 10);
            $economyScore = ($inflationRisk + $unemploymentRisk) / 2;
        }

        // 3. Currency Score (25%)
        $country = Country::find($countryId);
        $currencyScore = 50.0;
        if ($country && $country->currency) {
            $currency = Currency::where('code', $country->currency)->first();
            if ($currency) {
                $code = strtoupper($currency->code);
                if (in_array($code, ['USD', 'EUR', 'GBP', 'SGD', 'CHF'])) {
                    $currencyScore = 15.0;
                } elseif (in_array($code, ['JPY', 'CNY', 'AUD', 'CAD'])) {
                    $currencyScore = 25.0;
                } else {
                    $currencyScore = min(90.0, 30.0 + ($currency->rate > 1000 ? 25.0 : 10.0));
                }
            }
        }

        // 4. Port Score (25%)
        $ports = Port::where('country_id', $countryId)->get();
        $portScore = 50.0;
        if ($ports->count() > 0) {
            $totalPortRisk = 0;
            foreach ($ports as $port) {
                switch ($port->status) {
                    case 'Closed':
                        $totalPortRisk += 100;
                        break;
                    case 'Maintenance':
                        $totalPortRisk += 65;
                        break;
                    case 'Busy':
                        $totalPortRisk += 40;
                        break;
                    case 'Open':
                    default:
                        $totalPortRisk += 15;
                        break;
                }
            }
            $portScore = $totalPortRisk / $ports->count();
        }

        $totalScore = ($weatherScore + $economyScore + $currencyScore + $portScore) / 4;

        // REDESIGN THRESHOLDS: Adjusted for realistic distribution based on actual score ranges
        if ($totalScore >= 42.00) {
            $riskLevel = 'High';
        } elseif ($totalScore >= 30.00) {
            $riskLevel = 'Medium';
        } else {
            $riskLevel = 'Low';
        }

        return self::updateOrCreate(
            ['country_id' => $countryId],
            [
                'weather_score' => $weatherScore,
                'currency_score' => $currencyScore,
                'economy_score' => $economyScore,
                'port_score' => $portScore,
                'total_score' => $totalScore,
                'risk_level' => $riskLevel,
            ]
        );
    }
}