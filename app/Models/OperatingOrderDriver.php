<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingOrderDriver extends Model
{
    use HasFactory;

        protected $fillable = [
        'operating_order_id',
        'driver_id',
    ];

        public function operatingOrder()
    {
        return $this->belongsTo(OperatingOrder::class);
    }

    public function driver()
    {
        return $this->belongsTo(Drivers::class);
    }
}
