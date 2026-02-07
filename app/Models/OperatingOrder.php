<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'is_operating_order',
        'requirement_notes',
    ];

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function vehicles()
    {
        return $this->hasMany(OperatingOrderVehicle::class);
    }

    public function drivers()
    {
        return $this->hasMany(OperatingOrderDriver::class);
    }
}
