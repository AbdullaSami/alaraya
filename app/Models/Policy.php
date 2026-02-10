<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_data_id',
        'operating_order_id',
        'policy_number',
        'covenant_amount',
        'policy_type',
        'policy_aging_date',
        'policy_loading_date',
    ];

    protected $casts = [
        'policy_type' => 'boolean',
        'policy_aging_date' => 'datetime',
        'policy_loading_date' => 'datetime',
    ];

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function operatingOrder()
    {
        return $this->belongsTo(OperatingOrder::class);
    }

    public function vehicleDriverAssignments()
    {
        return $this->hasMany(VehicleDriverAssignment::class);
    }

    public static function generatePolicyNumber()
    {
        $date = now()->format('Ymd');
        $random = mt_rand(100, 999);
        return "BL-{$date}-{$random}";
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($policy) {
            if (empty($policy->policy_number)) {
                $policy->policy_number = self::generatePolicyNumber();
            }
        });
    }
}
