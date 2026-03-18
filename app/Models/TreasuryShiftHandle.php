<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryShiftHandle extends Model
{
    use HasFactory;

    protected $fillable = [
        'treasury_id',
        'user_id',
        'amount',
        'action',
    ];

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
