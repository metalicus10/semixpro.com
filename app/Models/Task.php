<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'day', 'start_time', 'end_time', 'customer_id', 'order_id'];

    protected $casts = [
        'day'        => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time'   => 'datetime:H:i:s',
    ];

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function technicians()
    {
        return $this->belongsToMany(Technician::class, 'task_technician', 'task_id', 'technician_id')
            ->withPivot(['status', 'assigned_at'])
            ->withTimestamps();
    }

}
