<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Economy extends Model
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

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}