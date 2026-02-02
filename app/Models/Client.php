<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'contact_number',
        'notes'
    ];

    public function factories()
    {
        return $this->hasMany(Factory::class);
    }

    public function shipLineClients()
    {
        return $this->hasMany(ShipLineClient::class);
    }
}
