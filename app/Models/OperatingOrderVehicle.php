<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingOrderVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'operating_order_id',
        'vehicle_id',
    ];

    public function operatingOrder()
    {
        return $this->belongsTo(OperatingOrder::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
