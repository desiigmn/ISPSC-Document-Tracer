<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // These MUST match the columns you just added in phpMyAdmin
    protected $fillable = ['user_id', 'type', 'message', 'link', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}