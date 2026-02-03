<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'policy_number',
    ];

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function shipContainersDetails()
    {
        return $this->hasMany(ShipContainersDetail::class, 'policy_id');
    }
}
