<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentPlanner extends Model
{
    use HasFactory;

    protected $table = 'shipment_planners';

    protected $fillable = [
        'shipment_id',
        'title',
        'start_date',
        'end_date',
        'description',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
