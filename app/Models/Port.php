<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $table = 'ports';

    protected $fillable = [
        'country_id',
        'port_name',
        'port_code',
        'city',
        'type',
        'capacity',
        'status',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Country
     * Satu Port dimiliki oleh satu Country
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Scope to retrieve only the main port (highest capacity) for each country
     */
    public function scopeMainPorts($query)
    {
        $mainPortIdsQuery = \Illuminate\Support\Facades\DB::table('ports as p2')
            ->join(
                \Illuminate\Support\Facades\DB::raw('(SELECT country_id, MAX(capacity) as max_cap FROM ports GROUP BY country_id) as mc'),
                function($join) {
                    $join->on('p2.country_id', '=', 'mc.country_id')
                         ->on('p2.capacity', '=', 'mc.max_cap');
                }
            )
            ->select(\Illuminate\Support\Facades\DB::raw('MAX(p2.id)'))
            ->groupBy('p2.country_id');

        return $query->whereIn('ports.id', $mainPortIdsQuery);
    }
}