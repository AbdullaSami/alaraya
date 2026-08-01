<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class TreasuryShiftHandle extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'treasury_id',
        'user_id',
        'amount',
        'action',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('treasury_shift_handles');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
