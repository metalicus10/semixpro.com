<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianPartUsage extends Model
{
    use HasFactory;

    // Таблица для этой модели
    protected $table = 'technician_part_usages';

    // Заполняемые поля
    protected $fillable = [
        'part_id',
        'technician_id',
        'quantity_used',
    ];

    /**
     * Связь с моделью Part (Запчасть).
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Связь с моделью User (Техник).
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
