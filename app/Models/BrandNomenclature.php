<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandNomenclature extends Model
{
    use HasFactory;

    protected $table = 'brand_nomenclature';
    protected $fillable = ['nomenclature_id', 'brand_id'];


}
