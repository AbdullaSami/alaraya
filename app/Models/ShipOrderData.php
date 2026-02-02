<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipOrderData extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'order_type',
        'client_requirements',
        'noloans',
        'shipping_date',
        'aging_date',
        'notes',
    ];

    public function shipLineClients()
    {
        return $this->hasMany(ShipLineClient::class);
    }

    public function shipPolicies()
    {
        return $this->hasMany(ShipPolicy::class);
    }

    public function shipBookings()
    {
        return $this->hasMany(ShipBooking::class);
    }

    public function shipContactData()
    {
        return $this->hasOne(ShipContactData::class);
    }
}
