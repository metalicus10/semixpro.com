<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'start_time', 'end_time', 'customer_id', 'technician_ids'];

    protected $casts = ['technician_ids' => 'array'];
}
