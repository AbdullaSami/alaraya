<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'is_operating_order',
        'cause_note',
        'operating_order_image',
        'operating_order_location',
        'operating_order_mail_image',
        'is_torrents',
        'torrents_cause_note',
        'torrents_image',
        'pull_torrents_date',
        'load_torrents_date',
        'release_and_assignment_image',
        'release_and_assignment_requirements',
    ];

    public function torrentContainers()
    {
        return $this->hasMany(TorrentContainer::class);
    }
    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function vehicles()
    {
        return $this->hasMany(OperatingOrderVehicle::class);
    }

    public function drivers()
    {
        return $this->hasMany(OperatingOrderDriver::class);
    }
}
