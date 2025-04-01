<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'contact_name', 'email', 'phone',
        'receivables', 'used_credits', 'address', 'is_active', 'manager_id'
    ];

    public function nomenclatures()
    {
        return $this->hasMany(Nomenclature::class, 'supplier_id');
    }
}
