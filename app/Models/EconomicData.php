<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicData extends Model
{
    protected $table = 'economic_data';

    protected $fillable = [
        'country_id',
        'gdp',
        'inflation',
        'unemployment',
        'exports',
        'imports',
        'year',
    ];

    protected $casts = [
        'gdp' => 'float',
        'inflation' => 'float',
        'unemployment' => 'float',
        'exports' => 'float',
        'imports' => 'float',
        'year' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
