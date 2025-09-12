<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'item_id')->where('item_type', 'part');
    }

    public static function availableForDay(int $partId, string|\DateTimeInterface $day, int $managerId, ?int $excludeOrderId = null): int
    {
        $day = Carbon::parse($day)->toDateString();

        $stock = (int) static::query()
            ->where('id', $partId)
            ->where('manager_id', $managerId)
            ->value('quantity');

        $reservedQ = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('tasks  as t', 't.order_id', '=', 'o.id')
            ->where('oi.item_type', 'material')
            ->where('oi.part_id', $partId)
            ->whereDate('t.day', '>=', $day);

        if ($excludeOrderId) {
            $reservedQ->where('oi.order_id', '!=', $excludeOrderId);
        }

        $reserved = (int) $reservedQ->sum('oi.quantity');

        /*Log::debug('availableForDay', [
            'partId'    => $partId,
            'managerId' => $managerId,
            'day'       => $day,
            'stock'     => $stock,
            'reserved'  => $reserved,
            'exclude'   => $excludeOrderId,
        ]);*/

        // quantity – общее количество, reserved – сумма qty по order_items (material) в будущих тасках
        /*$p = static::query()
            ->where('id', $partId)
            ->where('manager_id', $managerId)
            ->selectRaw('quantity,
            (select coalesce(sum(oi.quantity),0)
             from order_items oi
             join orders o on o.id=oi.order_id
             join tasks t on t.order_id=o.id
            where oi.part_id = parts.id
              and oi.item_type = "material"
              and oi.part_id is not null
              and date(t.day) >= ?
              and (o.status is null or o.status <> "canceled")
            ) as reserved', [$day])
            ->first();

        $reserved  = (int)($p->reserved ?? 0);
        $quantity  = (int)($p->quantity ?? 0);*/
        //return max(0, $quantity - $reserved);
        return max(0, $stock - $reserved);
    }

}
