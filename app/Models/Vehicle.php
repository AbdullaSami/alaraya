<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_number',
        'trailer_number',
        'badge_number',
        'notes',
    ];

    public function operatingOrderVehicles()
    {
        return $this->hasMany(OperatingOrderVehicle::class);
    }
}
