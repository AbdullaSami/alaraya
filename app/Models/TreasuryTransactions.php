<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receivable_id',
        'payable_id',
        'amount',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }
}
