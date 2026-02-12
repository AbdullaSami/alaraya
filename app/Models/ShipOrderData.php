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
        return $this->hasMany(ShipLineClient::class, 'ship_order_data_id');
    }

    public function shipPolicies()
    {
        return $this->hasMany(ShipPolicy::class, 'ship_order_data_id');
    }

    public function shipBookings()
    {
        return $this->hasMany(ShipBooking::class, 'ship_order_data_id');
    }

    public function shipContactData()
    {
        return $this->hasOne(ShipContactData::class, 'ship_order_data_id');
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
