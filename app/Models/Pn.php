<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pn extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'part_id'];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
