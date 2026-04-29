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
        'vgm',
        'x_ray',
        'data_entry',
        'yard_receipts',
        'port_authority_receipts',
        'port_weight_fees',
        'agency_receipts',
        'explosives_receipt',
        'bascule_scale_receipt',
        'cashier_receipt',
        'reweighing_receipt',
        'sina_marine_receipts',
        'tunnel_ferry_receipts',
        'container_repair_receipt',
        'port_receipts',
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
