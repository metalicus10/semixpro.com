<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseAssignmentLog extends Model
{
    use HasFactory;

    protected $table = 'warehouse_assignments_log';
    protected $fillable = ['manager_id', 'technician_id', 'warehouse_id', 'assigned_at'];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
