<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'global_notifications';
    protected $fillable = ['user_id', 'type', 'message', 'read', 'payload'];

    protected $casts = [
        'payload' => 'array',
        'read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
