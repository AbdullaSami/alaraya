<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'treasury_id',
        'amount',
        'reason',
        'type',
    ];

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

}
