<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Vehicle extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_number',
        'trailer_number',
        'badge_number',
        'notes',
        'type',
        'office_name'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('vehicles');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function operatingOrderVehicles()
    {
        return $this->hasMany(OperatingOrderVehicle::class);
    }
}
