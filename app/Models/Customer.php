<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address', 'address_formatted', 'address_place_id', 'address_lat', 'address_lng'];

    protected $casts = [
        'address_lat' => 'float',
        'address_lng' => 'float',
    ];

}
