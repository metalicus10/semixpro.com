<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'price',
        'manager_id',
        'active',
    ];

    // Связь с менеджером
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Пример: связь с заказами (если будет)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'item_id')->where('item_type', 'service');
    }
}
