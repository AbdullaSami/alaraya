<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_driver_assignment_id',
        'extra_amount',
        'extra_type',
    ];

    public function vehicleDriverAssignment()
    {
        return $this->belongsTo(VehicleDriverAssignment::class);
    }

    public function shipOrderData()
    {
        return $this->hasOneThrough(
            ShipOrderData::class,
            Policy::class,
            'id', // Foreign key on VehicleDriverAssignment table
            'id', // Foreign key on Policy table
            'vehicle_driver_assignment_id', // Local key on DriverExtra table
            'ship_order_data_id' // Local key on Policy table
        );
    }
}
