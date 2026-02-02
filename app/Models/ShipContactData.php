<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipContactData extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'contact_loading_name',
        'contact_loading_number',
        'contact_customs_officer_name',
        'contact_customs_officer_number',
    ];

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }
}
