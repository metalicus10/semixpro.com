<?php

namespace App\Models;

use Orchid\Access\UserAccess;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'         => Where::class,
        'name'       => Like::class,
        'email'      => Like::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    // Добавляем проверку на блокировку
    public function isBlocked()
    {
        return $this->is_blocked;
    }

    public function parts()
    {
        return $this->hasManyThrough(Part::class, Category::class, 'manager_id', 'category_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'manager_id', 'id');
    }

    public function brands()
    {
        return $this->hasMany(Brand::class, 'manager_id', 'id');
    }

    public function managedWarehouses()
    {
        return $this->hasMany(Warehouse::class, 'manager_id', 'id');
    }

    public function assignedWarehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'technician_warehouse', 'technician_id', 'warehouse_id')
            ->withPivot('technician_id', 'warehouse_id')
            ->select('warehouses.id', 'warehouses.name');
    }

    // Универсальный метод для получения складов в зависимости от роли
    public function warehouses()
    {
        return $this->inRole('manager') ? $this->managedWarehouses() : $this->assignedWarehouses();
    }

    public function pns()
    {
        return $this->hasMany(Pn::class, 'manager_id', 'id');
    }

    public function assignedParts()
    {
        return Part::whereIn('warehouse_id', function ($query) {
            $query->select('warehouse_id')
                ->from('technician_warehouse')
                ->where('technician_id', auth()->id());
        })->get();
    }

}
