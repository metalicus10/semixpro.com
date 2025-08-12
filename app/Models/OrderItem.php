<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'item_title',
        'item_description',
        'order_id',
        'item_type',
        'item_id',
        'part_id',
        'quantity',
        'price',
        'total',
        'is_custom',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    /*public function part()
    {
        return $this->morphTo(__FUNCTION__, 'part_type', 'part_id');
    }*/

}
