<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Factory extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'factory_name',
        'client_id',
        'location',
        'contact_person',
        'contact_number',
        'loading_person',
        'loading_contact',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('factories');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function shipLineClientFactories()
    {
        return $this->hasMany(ShipLineClientFactory::class);
    }
}
