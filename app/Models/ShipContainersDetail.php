<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipContainersDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'booking_id',
        'container_number',
    ];

    public function shipPolicy()
    {
        return $this->belongsTo(ShipPolicy::class, 'policy_id');
    }

    public function shipBooking()
    {
        return $this->belongsTo(ShipBooking::class, 'booking_id');
    }

    public function torrentContainers()
    {
        return $this->hasMany(TorrentContainer::class, 'container_id');
    }
}
