<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'day', 'start_time', 'end_time', 'customer_id', 'order_id', 'message'];

    protected $casts = [
        'day' => 'date',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
