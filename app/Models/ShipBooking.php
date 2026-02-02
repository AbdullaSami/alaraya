<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'booking_number',
    ];

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function clearanceData()
    {
        return $this->hasOne(ClearanceData::class);
    }

    public function shipContainersDetails()
    {
        return $this->hasMany(ShipContainersDetail::class);
    }
}
