<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'start_time', 'end_time', 'customer_id'];

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function technicians()
    {
        return $this->belongsToMany(Technician::class, 'task_technician');
    }

}
