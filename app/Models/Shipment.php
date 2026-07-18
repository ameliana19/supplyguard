<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'container_number',
        'cargo_type',
        'origin_country_id',
        'destination_country_id',
        'origin_port_id',
        'destination_port_id',
        'estimated_departure',
        'estimated_arrival',
        'status',
    ];

    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function destinationCountry()
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function originPort()
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort()
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }

    public function histories()
    {
        return $this->hasMany(ShipmentHistory::class)->orderBy('event_time', 'desc');
    }
}
