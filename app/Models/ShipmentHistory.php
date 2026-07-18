<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentHistory extends Model
{
    use HasFactory;

    protected $table = 'shipment_histories';

    protected $fillable = [
        'shipment_id',
        'status',
        'location',
        'notes',
        'event_time',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
