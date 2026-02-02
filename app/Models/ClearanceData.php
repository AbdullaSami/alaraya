<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClearanceData extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'clearance_type',
        'customs_location',
        'redirect_location',
    ];

    public function shipBooking()
    {
        return $this->belongsTo(ShipBooking::class, 'booking_id');
    }
}
