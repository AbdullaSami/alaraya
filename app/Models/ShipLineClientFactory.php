<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ShipLineClientFactory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ship_line_client_id',
        'factory_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('ship_line_client_factories');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function shipLineClient()
    {
        return $this->belongsTo(ShipLineClient::class);
    }

    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }
}
