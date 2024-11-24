<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manager_id',
        'position',
    ];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
}
