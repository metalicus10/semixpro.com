<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'manager_id'
    ];

    public function parts()
    {
        return $this->hasMany(Part::class, 'category_id');
    }

    public function nomenclatures()
    {
        return $this->hasMany(Nomenclature::class, 'category_id');
    }
}
