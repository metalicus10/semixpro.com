<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_type',
        'target_type',
        'target_id',
        'description',
        'user_id',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
