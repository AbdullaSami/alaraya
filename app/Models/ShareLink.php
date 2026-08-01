<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ShareLink extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'serial_number',
        'user_id',
        'type',
        'body',
    ];

    protected $casts = [
        'body' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('share_links');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public static function generateSerialNumber()
    {
        do {
            $serial = strtoupper(Str::random(10));
        } while (self::where('serial_number', $serial)->exists());
        return $serial;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
