<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class TorrentContainer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'operating_order_id',
        'container_id',
        'torrent_number',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('torrent_containers');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function operatingOrder()
    {
        return $this->belongsTo(OperatingOrder::class);
    }

    public function container()
    {
        return $this->belongsTo(ShipContainersDetail::class);
    }
}
