<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShareLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'user_id',
        'type',
        'body',
    ];

    protected $casts = [
        'body' => 'array',
    ];

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
