<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = ['part_id', 'quantity', 'user_id'];

    // Связь с пользователем (менеджером)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
