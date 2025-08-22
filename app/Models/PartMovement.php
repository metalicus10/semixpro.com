<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartMovement extends Model
{
    use HasFactory;

    protected $fillable = ['part_id', 'technician_id', 'quantity', 'manager_id', 'from_warehouse_id', 'to_warehouse_id'];

    /*public function part()
    {
        return $this->belongsTo(Part::class, 'part_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }*/
}
