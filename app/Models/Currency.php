<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    /**
     * Nama tabel
     */
    protected $table = 'currency_data';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'rate',
        'status',
        'change_percent',
        'country_id',
    ];

    protected $casts = [
        'rate' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getFormattedRateAttribute()
    {
        return number_format($this->rate, 0, ',', '.');
    }

    public function getIsBaseAttribute()
    {
        return strtoupper($this->code) === 'USD';
    }

    public function getBadgeColorAttribute()
    {
        return $this->is_base ? 'success' : 'primary';
    }
}