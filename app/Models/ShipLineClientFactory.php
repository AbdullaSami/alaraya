<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipLineClientFactory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_line_client_id',
        'factory_id',
    ];

    public function shipLineClient()
    {
        return $this->belongsTo(ShipLineClient::class);
    }

    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }
}
