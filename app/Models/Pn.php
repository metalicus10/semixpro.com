<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pn extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'part_id', 'manager_id', 'nomenclature_id'];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id');
    }
}
