<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDriverAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'ship_container_id',
        'policy_id',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Drivers::class);
    }

    public function shipContainer()
    {
        return $this->belongsTo(ShipContainersDetail::class, 'ship_container_id');
    }

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    public function shipOrderData()
    {
        return $this->policy->shipOrderData;
    }
}
