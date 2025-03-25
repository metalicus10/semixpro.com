<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianPart extends Model
{
    protected $fillable = ['technician_id', 'part_id', 'quantity', 'manager_id', 'total_transferred'];

    // Связь с техником
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Связь с запчастями
    public function parts()
    {
        return $this->belongsTo(Part::class, 'part_id');
    }

    // Связь с менеджером
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function nomenclatures()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function technicianDetails()
    {
        return $this->belongsTo(Technician::class, 'technician_id', 'user_id');
    }

}
