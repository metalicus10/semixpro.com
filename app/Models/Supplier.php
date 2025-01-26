<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'manager_id'];

    public function nomenclatures()
    {
        return $this->hasMany(Nomenclature::class, 'supplier_id');
    }
}
