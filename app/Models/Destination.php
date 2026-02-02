<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_name',
        'noloan_code',
        'notes',
    ];

    public function shipLineClients()
    {
        return $this->hasMany(ShipLineClient::class);
    }
}
