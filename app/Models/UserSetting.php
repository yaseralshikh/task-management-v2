<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'notifications',
        'preferences',
        'privacy',
    ];

    protected $casts = [
        'notifications' => 'array',
        'preferences' => 'array',
        'privacy' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
