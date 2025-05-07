<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkLog extends Model
{
    protected $fillable = ['action_type', 'target_type', 'user_id', 'items', 'summary'];

    protected $casts = [
        'items' => 'array',
    ];
}
