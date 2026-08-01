<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class TreasuryTransactions extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'receivable_id',
        'payable_id',
        'amount',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('treasury_transactions');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = class_basename($this) . " {$eventName}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }
}
