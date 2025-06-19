<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Technician extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password', 'manager_id', 'user_id', 'is_active'];

    // Связь с менеджером
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Связь с пользователями (User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class);
    }

}
