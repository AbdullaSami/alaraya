<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_id',
        'policy_id',
        'army_scales',
        'roads_and_bridges',
        'road_cards',
        'governorate_voucher',
        'tips',
        'official_receipts',
        'overnight_leave',
        'tarif_receipts',
        'third_party_car_rental',
        'customs_clearance',
        'bill_of_lading_amendment',
        'third_party_vehicle_leave',
        'brokers',
    ];

    /**
     * Relationship: Expense belongs to Ship Order
     */
    public function shipOrder()
    {
        return $this->belongsTo(ShipOrderData::class, 'ship_order_id');
    }

    /**
     * Relationship: Expense belongs to Ship Policy
     */
    public function policy()
    {
        return $this->belongsTo(Policy::class, 'policy_id');
    }
}
