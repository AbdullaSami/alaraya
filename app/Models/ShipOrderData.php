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
        'containers_type',
        'containers_number',
        'loading_way',
        'transfers_count',
    ];

    public function operatingOrder()
    {
        return $this->hasOne(OperatingOrder::class);
    }
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

       public function policies()
    {
        return $this->hasMany(Policy::class);
    }
    public function canCreateMorePolicies()
    {
        return $this->policies()->count() < $this->transfers_count;
    }

    public function remainingPolicySlots()
    {
        return max(0, $this->transfers_count - $this->policies()->count());
    }
}
