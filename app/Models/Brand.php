<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'manager_id'];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    public function parts()
    {
        return $this->belongsToMany(Part::class, 'brand_part');
    }

    public function nomenclatures()
    {
        return $this->belongsToMany(Nomenclature::class, 'brand_nomenclature', 'brand_id', 'nomenclature_id');
    }

}
