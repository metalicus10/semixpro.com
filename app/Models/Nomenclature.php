<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nomenclature extends Model
{
    use HasFactory;

    protected $fillable = [
        'nn',
        'name',
        'category_id',
        'supplier_id',
        'manager_id',
        'is_archived',
        'archived_at',
        'image',
    ];

    /*protected $casts = [
        'url' => 'array',
    ];*/

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function archive()
    {
        $this->is_archived = true;
        $this->archived_at = now();
        $this->save();
    }

}
