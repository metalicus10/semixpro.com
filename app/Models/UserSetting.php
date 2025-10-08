<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'delete_after_days',
        'scheduler_settings',
    ];

    protected $casts = [
        'scheduler_settings' => 'array',
    ];
}
