<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianPart extends Model
{
    protected $fillable = ['technician_id', 'part_id', 'nomenclature_id', 'warehouse_id', 'quantity', 'manager_id', 'total_transferred', 'daily_transfers', 'daily_returns'];

    protected $casts = [
        'daily_transfers' => 'array',
        'daily_returns' => 'array',
    ];

    // Получение: $part->getWeeklyTransfersForWeek($weekKey)
    public function getWeeklyTransfersForWeek($weekKey)
    {
        $arr = $this->daily_transfers ?? [];
        return $arr[$weekKey] ?? 0;
    }

    // Запись: $part->setWeeklyTransfersForWeek($weekKey, $value);
    public function setWeeklyTransfersForWeek($weekKey, $value)
    {
        $arr = $this->daily_transfers ?? [];
        $arr[$weekKey] = $value;
        $this->daily_transfers = $arr;
        return $this;
    }

    // Аналогично для returns
    public function getWeeklyReturnsForWeek($weekKey)
    {
        $arr = $this->daily_returns ?? [];
        return $arr[$weekKey] ?? 0;
    }

    public function setWeeklyReturnsForWeek($weekKey, $value)
    {
        $arr = $this->daily_returns ?? [];
        $arr[$weekKey] = $value;
        $this->daily_returns = $arr;
        return $this;
    }

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
