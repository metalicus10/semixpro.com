<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomenclatureVersion extends Model
{
    protected $fillable = [
        'nomenclature_id',
        'changes',
        'user_id',
    ];
}
