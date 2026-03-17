<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treasury extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_main',
        'balance',
    ];

    public function users(){
        return $this->belongsToMany(User::class, 'treasury_user');
    }
    public function orderTreasury()
    {
        return $this->hasOne(ShipOrderData::class, 'order_treasury');
    }
    public function receivableTransactions()
    {
        return $this->hasMany(TreasuryTransactions::class, 'receivable_id');
    }

    public function payableTransactions()
    {
        return $this->hasMany(TreasuryTransactions::class, 'payable_id');
    }

    public function deductions()
    {
        return $this->hasMany(TreasuryDeduction::class);
    }

    public function shiftHandles()
    {
        return $this->hasMany(TreasuryShiftHandle::class);
    }

}
