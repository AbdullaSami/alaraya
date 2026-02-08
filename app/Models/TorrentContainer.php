<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'operating_order_id',
        'container_id',
        'torrent_number',
    ];

    public function operatingOrder()
    {
        return $this->belongsTo(OperatingOrder::class);
    }

    public function container()
    {
        return $this->belongsTo(ShipContainersDetail::class);
    }
}
