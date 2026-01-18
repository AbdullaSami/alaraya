<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drivers extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_name',
        'phone_number',
        'identification_number',
        'license_number',
    ];
}
