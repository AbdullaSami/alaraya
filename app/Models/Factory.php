<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    use HasFactory;
    protected $fillable = [
        'factory_name',
        'client_id',
        'location',
        'contact_person',
        'contact_number',
        'loading_person',
        'loading_contact',
        'notes',
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function shipLineClientFactories()
    {
        return $this->hasMany(ShipLineClientFactory::class);
    }
}
