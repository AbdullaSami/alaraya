<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ShipBooking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ship_order_data_id',
        'booking_number',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('ship_bookings');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function clearanceData()
    {
        return $this->hasOne(ClearanceData::class, 'booking_id');
    }

    public function shipContainersDetails()
    {
        return $this->hasMany(ShipContainersDetail::class, 'booking_id');
    }
}
