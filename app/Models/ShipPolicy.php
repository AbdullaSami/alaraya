<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class ShipPolicy extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ship_order_data_id',
        'policy_number',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('ship_policies');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function shipOrderData()
    {
        return $this->belongsTo(ShipOrderData::class);
    }

    public function shipContainersDetails()
    {
        return $this->hasMany(ShipContainersDetail::class, 'policy_id');
    }
}
