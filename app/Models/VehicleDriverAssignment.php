<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class VehicleDriverAssignment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'policy_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('vehicle_driver_assignments');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function driverExtras()
    {
        return $this->hasMany(DriverExtra::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Drivers::class);
    }

    public function shipContainers()
    {
        return $this->belongsToMany(ShipContainersDetail::class, 'assignment_container_pivot', 'vehicle_driver_assignment_id', 'ship_container_id')
            ->withTimestamps();
    }

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    public function shipOrderData()
    {
        return $this->policy->shipOrderData;
    }

    public function syncShipContainers(array $containerIds)
    {
        return $this->shipContainers()->sync($containerIds);
    }

    public function attachShipContainers(array $containerIds)
    {
        return $this->shipContainers()->attach($containerIds);
    }

    public function detachShipContainers(array $containerIds)
    {
        return $this->shipContainers()->detach($containerIds);
    }
}
