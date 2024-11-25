<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nomenclature extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'pn',
        'name',
        'category',
        'supplier',
        'brand',
        'url',
        'manager_id',
    ];

    protected $casts = [
        'pn' => 'array',
        'brand' => 'array',
        'url' => 'array',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function pns()
    {
        return $this->hasMany(Pn::class, 'nomenclature_id', 'id');
    }
}
