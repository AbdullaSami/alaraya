<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipLineClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'client_id',
        'shipping_line_id',
        'destination_id',
    ];

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function shippingLine()
    {
        return $this->belongsTo(ShippingLine::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function shipLineClientFactories()
    {
        return $this->hasMany(ShipLineClientFactory::class);
    }
}
