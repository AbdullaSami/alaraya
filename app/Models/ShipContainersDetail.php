<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ShipContainersDetail extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'policy_id',
        'booking_id',
        'container_number',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('ship_containers');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

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

    public function assignmentContainerPivots()
    {
        return $this->hasMany(AssignmentContainerPivot::class, 'ship_container_id');
    }
}
