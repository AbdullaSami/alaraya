<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_line_name',
        'contact_info',
        'notes',
    ];
}
