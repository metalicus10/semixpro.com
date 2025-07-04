<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;

class Part extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sku',
        'warehouse_id',
        'quantity',
        'price',
        'image',
        'category_id',
        'manager_id',
        'total',
        'url',
        'nomenclature_id',
    ];

    public static function boot()
    {
        parent::boot();

        static::updating(function ($part) {
            if ($part->isDirty('price')) {
                DB::table('part_price_history')->insert([
                    'part_id' => $part->id,
                    'price' => $part->price,
                    'changed_at' => now(),
                ]);
            }
        });
    }

    protected static function booted()
    {
        static::saving(function ($part) {
            $part->total = $part->price * $part->quantity;
        });
    }

    // Получение текущих остатков запчастей
    public static function getStockQuantities()
    {
        return self::select('name', 'sku', 'quantity')
            ->where('quantity', '>', 0)
            ->get();
    }

    public function technicianParts()
    {
        return $this->hasMany(TechnicianPart::class, 'part_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Связь с таблицей перемещений запчастей
    public function transfers()
    {
        return $this->hasMany(PartTransfer::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_part');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function pns()
    {
        return $this->hasMany(Pn::class, 'part_id', 'id');
    }

    public function nomenclatures()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'item_id')->where('item_type', 'part');
    }

}
