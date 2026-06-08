<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signatory extends Model
{
    protected $fillable = ['document_id', 'user_id', 'sign_order', 'status', 'signature_data', 'signed_at', 'x_pos', 'y_pos', 'page_num'];

    // ADD THIS SECTION:
    protected $casts = [
        'signed_at' => 'datetime', // This tells Laravel to turn the string into a Carbon object
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}