<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $fillable = [
        'document_id',
        'office_id',
        'user_id',
        'sign_order',
        'status',
        'x_pos',    // <--- ADD THIS
        'y_pos',    // <--- ADD THIS
        'page_num', // <--- ADD THIS
        'signature_data',
        'signed_at'
    ];
        // ADD THIS BLOCK
        protected $casts = [
            'signed_at' => 'datetime',
        ];

    public function office() {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}