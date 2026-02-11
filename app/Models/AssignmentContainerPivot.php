<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentContainerPivot extends Model
{
    use HasFactory;

    protected $table = 'assignment_container_pivot';

    protected $fillable = [
        'vehicle_driver_assignment_id',
        'ship_container_id',
    ];

    public $timestamps = true;

    public function vehicleDriverAssignment()
    {
        return $this->belongsTo(VehicleDriverAssignment::class);
    }

    public function shipContainer()
    {
        return $this->belongsTo(ShipContainersDetail::class, 'ship_container_id');
    }
}
